<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Storefront display position. Drag the ≡ handle to reorder (top = first),
            // or type the "Order" number (lower = first). Filter by game first to
            // reorder within one game.
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginationPageOptions([10, 25, 50, 100])
            ->columns([
                SpatieMediaLibraryImageColumn::make('main')
                    ->collection('main')
                    ->label('')
                    ->square()
                    ->width(48)
                    ->height(48),
                TextColumn::make('name')
                    ->description(fn (Product $record): ?string => $record->game?->name)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('compare_price')
                    ->money('USD')
                    ->placeholder('—')
                    ->toggleable()
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('auto_delivery')
                    ->label('Auto')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('featured')
                    ->boolean()
                    ->alignCenter(),
                TextInputColumn::make('sort_order')
                    ->label('Order')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->tooltip('Lower number shows first on the site. You can also drag the ≡ handle to reorder.')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProductStatus::class),
                SelectFilter::make('type')
                    ->options(ProductType::class),
                SelectFilter::make('game')
                    ->relationship('game', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('featured'),
                TernaryFilter::make('auto_delivery')->label('Auto delivery'),
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
