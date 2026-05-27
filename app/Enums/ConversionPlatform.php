<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConversionPlatform: string implements HasLabel
{
    case FacebookCapi = 'facebook_capi';
    case TikTok = 'tiktok';
    case GoogleAds = 'google_ads';
    case Snapchat = 'snapchat';
    case CustomWebhook = 'custom_webhook';

    public function getLabel(): string
    {
        return match ($this) {
            self::FacebookCapi => 'Facebook CAPI',
            self::TikTok => 'TikTok Events',
            self::GoogleAds => 'Google Ads',
            self::Snapchat => 'Snapchat',
            self::CustomWebhook => 'Custom Webhook',
        };
    }
}
