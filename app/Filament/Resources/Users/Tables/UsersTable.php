<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->state(fn (User $record): float => $record->revenue())
                    ->money('USD')
                    ->alignEnd(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->dateTime('M j, Y H:i')
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(UserStatus::class),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('toggleBan')
                    ->label(fn (User $record): string => $record->status === UserStatus::Banned ? 'Unban' : 'Ban')
                    ->icon(fn (User $record): string => $record->status === UserStatus::Banned ? 'heroicon-m-lock-open' : 'heroicon-m-no-symbol')
                    ->color(fn (User $record): string => $record->status === UserStatus::Banned ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update([
                        'status' => $record->status === UserStatus::Banned ? UserStatus::Active : UserStatus::Banned,
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
