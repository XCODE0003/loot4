<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('event')->badge()->placeholder('—'),
                        TextEntry::make('causer.name')->label('Who')->placeholder('System'),
                        TextEntry::make('created_at')->label('Time')->dateTime(),
                        TextEntry::make('subject_type')
                            ->label('Subject')
                            ->formatStateUsing(fn (?string $state, Activity $record): string => $state
                                ? class_basename($state).' #'.$record->subject_id
                                : '—'),
                        TextEntry::make('log_name')->placeholder('—'),
                        TextEntry::make('description')->columnSpanFull(),
                    ]),

                Section::make('Changes')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('old')
                            ->label('Before')
                            ->state(fn (Activity $record): string => self::json($record->properties['old'] ?? null)),
                        TextEntry::make('attributes')
                            ->label('After')
                            ->state(fn (Activity $record): string => self::json($record->properties['attributes'] ?? null)),
                    ]),
            ]);
    }

    private static function json(mixed $value): string
    {
        if (blank($value)) {
            return '—';
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '—';
    }
}
