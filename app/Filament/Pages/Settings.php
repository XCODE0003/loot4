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
        'facebook_pixel_id', 'tiktok_pixel_id', 'ga_measurement_id',
        'google_ads_conversion_id', 'google_ads_conversion_label', 'discord_webhook_url',
        'telegram_bot_token', 'telegram_chat_id', 'telegram_failed_bot_token', 'telegram_failed_chat_id',
        'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption',
        'mail_from_address', 'mail_from_name', 'order_notify_email',
        'delivery_standard_time', 'delivery_express_time', 'delivery_express_fee',
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
                        TextInput::make('ga_measurement_id')->label('Google Analytics ID (G-XXXXXXX)')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('google_ads_conversion_id')
                            ->label('Google Ads Conversion ID')
                            ->placeholder('AW-123456789')
                            ->helperText('From the Google Ads conversion tag: gtag send_to = AW-XXXXXXXXX/label.'),
                        TextInput::make('google_ads_conversion_label')
                            ->label('Google Ads Conversion Label')
                            ->placeholder('AbC-D_efG-h12_34-567'),
                        TextInput::make('discord_webhook_url')->label('Discord webhook URL')->url(),
                    ]),

                Section::make('Telegram notifications')
                    ->description('New paid orders are sent to the orders bot. Failed/dropped payments are sent to the failed-orders bot (falls back to the orders bot when left empty).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('telegram_bot_token')->label('Orders bot token')->password()->revealable(),
                        TextInput::make('telegram_chat_id')->label('Orders chat ID'),
                        TextInput::make('telegram_failed_bot_token')->label('Failed-orders bot token')->password()->revealable(),
                        TextInput::make('telegram_failed_chat_id')->label('Failed-orders chat ID'),
                    ]),

                Section::make('Email (SMTP)')
                    ->description('Used for the customer order confirmation and the staff new-order email.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('mail_host')->label('SMTP host'),
                        TextInput::make('mail_port')->label('SMTP port')->numeric(),
                        TextInput::make('mail_username')->label('SMTP username'),
                        TextInput::make('mail_password')->label('SMTP password')->password()->revealable(),
                        TextInput::make('mail_encryption')->label('Encryption (tls / ssl)'),
                        TextInput::make('mail_from_address')->label('From address')->email(),
                        TextInput::make('mail_from_name')->label('From name'),
                        TextInput::make('order_notify_email')->label('Send new-order emails to')->email(),
                    ]),

                Section::make('Delivery')
                    ->description('Shown on checkout. Express appears only for products with "Express delivery" enabled.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('delivery_standard_time')
                            ->label('Standard delivery time')
                            ->placeholder('1–24 hours'),
                        TextInput::make('delivery_express_time')
                            ->label('Express delivery time')
                            ->placeholder('1–12 hours'),
                        TextInput::make('delivery_express_fee')
                            ->label('Express surcharge')
                            ->numeric()
                            ->prefix('$')
                            ->placeholder('20.99'),
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
