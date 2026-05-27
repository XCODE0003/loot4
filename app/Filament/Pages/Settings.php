<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 9;

    /**
     * Settings keys managed by this page.
     *
     * @var list<string>
     */
    private const KEYS = [
        'site_name', 'site_logo', 'favicon', 'seo_default_title', 'seo_default_description',
        'payment_stripe', 'stripe_key', 'payment_paypal', 'paypal_client_id', 'payment_crypto', 'payment_apple_pay',
        'facebook_pixel_id', 'tiktok_pixel_id', 'discord_webhook_url', 'telegram_bot_token',
        'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_from_address',
    ];

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = Setting::get($key);
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('site_name'),
                        TextInput::make('site_logo')->label('Logo URL')->url(),
                        TextInput::make('favicon')->label('Favicon URL')->url(),
                        TextInput::make('seo_default_title')->label('Default SEO title'),
                        TextInput::make('seo_default_description')->label('Default SEO description')->columnSpanFull(),
                    ]),

                Section::make('Payments')
                    ->columns(2)
                    ->schema([
                        Toggle::make('payment_stripe')->label('Stripe enabled'),
                        TextInput::make('stripe_key')->label('Stripe key')->password()->revealable(),
                        Toggle::make('payment_paypal')->label('PayPal enabled'),
                        TextInput::make('paypal_client_id')->label('PayPal client ID'),
                        Toggle::make('payment_crypto')->label('Crypto enabled'),
                        Toggle::make('payment_apple_pay')->label('Apple Pay enabled'),
                    ]),

                Section::make('Integrations')
                    ->columns(2)
                    ->schema([
                        TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID'),
                        TextInput::make('tiktok_pixel_id')->label('TikTok Pixel ID'),
                        TextInput::make('discord_webhook_url')->label('Discord webhook URL')->url(),
                        TextInput::make('telegram_bot_token')->label('Telegram bot token')->password()->revealable(),
                    ]),

                Section::make('Email (SMTP)')
                    ->columns(2)
                    ->schema([
                        TextInput::make('mail_host'),
                        TextInput::make('mail_port')->numeric(),
                        TextInput::make('mail_username'),
                        TextInput::make('mail_password')->password()->revealable(),
                        TextInput::make('mail_from_address')->label('From address')->email(),
                    ]),
            ]);
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
