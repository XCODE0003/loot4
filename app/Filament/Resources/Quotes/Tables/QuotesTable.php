<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('Customer')
                    ->description(fn (Quote $record): ?string => $record->user?->name)
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('quoted_price')
                    ->money(fn (Quote $record): string => $record->currency ?? 'USD')
                    ->placeholder('—')
                    ->alignEnd(),
                TextColumn::make('assignedTo.name')
                    ->label('Assigned')
                    ->placeholder('Unassigned'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(QuoteStatus::class),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Quote $record): bool => $record->status !== QuoteStatus::Approved)
                    ->action(fn (Quote $record) => $record->update(['status' => QuoteStatus::Approved])),
                Action::make('reject')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Quote $record): bool => $record->status !== QuoteStatus::Rejected)
                    ->action(fn (Quote $record) => $record->update(['status' => QuoteStatus::Rejected])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
