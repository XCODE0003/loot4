<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConversionPlatform: string implements HasLabel
{
    case FacebookCapi = 'facebook_capi';
    // Browser-side Meta Pixel (fbq) — distinct from the server-side CAPI.
    case FacebookPixel = 'facebook_pixel';
    case TikTok = 'tiktok';
    case GoogleAds = 'google_ads';
    case Snapchat = 'snapchat';
    case CustomWebhook = 'custom_webhook';

    public function getLabel(): string
    {
        return match ($this) {
            self::FacebookCapi => 'Facebook CAPI',
            self::FacebookPixel => 'Facebook Pixel',
            self::TikTok => 'TikTok Events',
            self::GoogleAds => 'Google Ads',
            self::Snapchat => 'Snapchat',
            self::CustomWebhook => 'Custom Webhook',
        };
    }
}
