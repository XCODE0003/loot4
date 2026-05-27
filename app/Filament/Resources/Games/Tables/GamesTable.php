<?php

namespace App\Filament\Resources\Games\Tables;

use App\Enums\GameStatus;
use App\Models\Game;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->collection('image')
                    ->label('')
                    ->square()
                    ->width(48)
                    ->height(48),
                TextColumn::make('name')
                    ->description(fn (Game $record): string => $record->slug)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tags')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(GameStatus::class),
            ])
            ->recordActions([
                Action::make('toggleStatus')
                    ->label(fn (Game $record): string => $record->status === GameStatus::Active ? 'Disable' : 'Enable')
                    ->icon(fn (Game $record): string => $record->status === GameStatus::Active ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (Game $record): string => $record->status === GameStatus::Active ? 'gray' : 'success')
                    ->action(fn (Game $record) => $record->update([
                        'status' => $record->status === GameStatus::Active ? GameStatus::Inactive : GameStatus::Active,
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
