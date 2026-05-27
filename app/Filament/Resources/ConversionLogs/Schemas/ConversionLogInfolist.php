<?php

namespace App\Filament\Resources\ConversionLogs\Schemas;

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

                Section::make('Request payload')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('request_payload')
                            ->hiddenLabel()
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '—')
                            ->copyable()
                            ->placeholder('—'),
                    ]),

                Section::make('Response payload')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('response_payload')
                            ->hiddenLabel()
                            ->formatStateUsing(fn ($state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '—')
                            ->copyable()
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
