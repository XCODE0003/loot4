<?php

namespace App\Filament\Resources\Currencies\Tables;

use App\Models\Currency;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->badge()
                    ->searchable(),
                TextColumn::make('symbol')
                    ->alignCenter(),
                TextColumn::make('exchange_rate')
                    ->numeric(decimalPlaces: 4)
                    ->sortable()
                    ->alignEnd(),
                IconColumn::make('auto_update')
                    ->label('Auto')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('last_updated_at')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('updateRates')
                    ->label('Update rates')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Refresh exchange rates for currencies with auto-update enabled. (External API integration is stubbed.)')
                    ->action(function (): void {
                        Currency::query()->where('auto_update', true)->update(['last_updated_at' => now()]);
                        Notification::make()->title('Exchange rates refreshed')->success()->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
