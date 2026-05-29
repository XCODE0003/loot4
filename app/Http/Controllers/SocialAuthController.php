<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lightweight OAuth2 social login for Google and Discord.
 *
 * Self-contained (no Socialite dependency). Each provider is enabled simply by
 * setting its client id/secret in config/services.php (.env). Until then the
 * redirect endpoint bounces back to /login with a friendly error.
 */
class SocialAuthController extends Controller
{
    private const PROVIDERS = [
        'google' => [
            'authorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token' => 'https://oauth2.googleapis.com/token',
            'user' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'scope' => 'openid email profile',
        ],
        'discord' => [
            'authorize' => 'https://discord.com/oauth2/authorize',
            'token' => 'https://discord.com/api/oauth2/token',
            'user' => 'https://discord.com/api/users/@me',
            'scope' => 'identify email',
        ],
    ];

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $config = $this->providerConfig($provider);

        if ($config === null || blank($config['client_id'])) {
            return redirect()->route('login')->withErrors(['oauth' => ucfirst($provider).' login is not configured yet.']);
        }

        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $this->callbackUrl($provider),
            'response_type' => 'code',
            'scope' => $config['scope'],
            'state' => $state,
            'prompt' => 'consent',
        ]);

        return redirect()->away($config['authorize'].'?'.$query);
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $config = $this->providerConfig($provider);

        if ($config === null || blank($config['client_id'])) {
            return redirect()->route('login')->withErrors(['oauth' => ucfirst($provider).' login is not configured yet.']);
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->withErrors(['oauth' => 'Social login was cancelled.']);
        }

        if (! hash_equals((string) $request->session()->pull('oauth_state'), (string) $request->input('state'))) {
            return redirect()->route('login')->withErrors(['oauth' => 'Invalid login state. Please try again.']);
        }

        try {
            $token = Http::asForm()->post($config['token'], [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'code' => $request->input('code'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->callbackUrl($provider),
            ])->throw()->json('access_token');

            $profile = Http::withToken($token)->get($config['user'])->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('OAuth callback failed', ['provider' => $provider, 'message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['oauth' => 'Could not sign you in with '.ucfirst($provider).'. Please try again.']);
        }

        $email = $profile['email'] ?? null;
        $name = $profile['name'] ?? $profile['username'] ?? ($email ? Str::before($email, '@') : 'Player');

        if (blank($email)) {
            return redirect()->route('login')->withErrors(['oauth' => 'Your '.ucfirst($provider).' account has no email address.']);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt(Str::random(40)), 'email_verified_at' => now()],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('account.index'));
    }

    /**
     * @return array{authorize: string, token: string, user: string, scope: string, client_id: ?string, client_secret: ?string}|null
     */
    private function providerConfig(string $provider): ?array
    {
        if (! isset(self::PROVIDERS[$provider])) {
            return null;
        }

        return [
            ...self::PROVIDERS[$provider],
            'client_id' => config("services.{$provider}.client_id"),
            'client_secret' => config("services.{$provider}.client_secret"),
        ];
    }

    private function callbackUrl(string $provider): string
    {
        return url("/auth/{$provider}/callback");
    }
}
