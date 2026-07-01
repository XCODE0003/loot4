<?php

namespace App\Filament\Resources\ConversionLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConversionLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('platform')->badge(),
                        TextEntry::make('event')->badge()->color('gray'),
                        TextEntry::make('status')->label('Sent status')->badge(),
                        TextEntry::make('order.order_number')->label('Order')->placeholder('—'),
                        TextEntry::make('value')->money(fn ($record): string => $record->currency ?? 'USD')->placeholder('—'),
                        TextEntry::make('currency'),
                        TextEntry::make('created_at')->label('Time')->dateTime(),
                        TextEntry::make('sent_at')->dateTime()->placeholder('Not sent'),
                        TextEntry::make('url')->url(fn ($record): ?string => $record->url)->openUrlInNewTab()->placeholder('—'),
                        TextEntry::make('reason')->placeholder('—')->columnSpanFull(),
                    ]),

                // Human-readable view of what was reported for this conversion
                // (e.g. the Google Ads / Facebook / TikTok fire), as a key→value
                // table instead of a raw JSON blob.
                Section::make('Conversion payload')
                    ->schema([
                        KeyValueEntry::make('conversion_payload')
                            ->hiddenLabel()
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->state(fn ($record): array => self::readablePayload($record))
                            ->placeholder('—'),
                    ]),

                Section::make('Raw request payload (debug)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('request_payload')
                            ->hiddenLabel()
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '—')
                            ->copyable()
                            ->placeholder('—'),
                    ]),

                Section::make('Raw response payload (debug)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('response_payload')
                            ->hiddenLabel()
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '—')
                            ->copyable()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    /**
     * Flatten the stored payload into a readable [field => value] map. Browser
     * pixels wrap the real fields under `client`; server-side sends store them
     * at the top level. Booleans/arrays are rendered as friendly strings.
     *
     * @return array<string, string>
     */
    private static function readablePayload(object $record): array
    {
        $payload = is_array($record->request_payload ?? null) ? $record->request_payload : [];
        $fields = is_array($payload['client'] ?? null) ? $payload['client'] : $payload;

        $readable = [];
        foreach ($fields as $key => $value) {
            $readable[(string) $key] = match (true) {
                is_bool($value) => $value ? 'yes' : 'no',
                is_scalar($value) => (string) $value,
                $value === null => '—',
                default => (string) json_encode($value, JSON_UNESCAPED_SLASHES),
            };
        }

        return $readable;
    }
}
