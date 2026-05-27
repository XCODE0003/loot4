<?php

namespace App\Filament\Resources\StorageUnits\Tables;

use App\Enums\StorageUnitStatus;
use App\Models\StorageUnit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StorageUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('stock')
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder('—'),
                TextColumn::make('expires_at')
                    ->dateTime('M j, Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(StorageUnitStatus::class),
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('reserve')
                    ->icon('heroicon-m-lock-closed')
                    ->color('warning')
                    ->visible(fn (StorageUnit $record): bool => $record->status === StorageUnitStatus::Available)
                    ->action(fn (StorageUnit $record) => $record->update([
                        'status' => StorageUnitStatus::Reserved,
                        'reserved_at' => now(),
                    ])),
                Action::make('release')
                    ->icon('heroicon-m-lock-open')
                    ->color('gray')
                    ->visible(fn (StorageUnit $record): bool => $record->status === StorageUnitStatus::Reserved)
                    ->action(fn (StorageUnit $record) => $record->update([
                        'status' => StorageUnitStatus::Available,
                        'reserved_at' => null,
                    ])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
