<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\PaymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('method')
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                        'crypto' => 'Crypto',
                        'apple_pay' => 'Apple Pay',
                    ])
                    ->required(),
                TextInput::make('transaction_id')
                    ->label('Transaction ID')
                    ->maxLength(255),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->required(),
                TextInput::make('currency')
                    ->default('USD')
                    ->maxLength(3)
                    ->required(),
                Select::make('status')
                    ->options(PaymentStatus::class)
                    ->default(PaymentStatus::Pending->value)
                    ->required(),
                TextInput::make('discount_code')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('method')
            ->columns([
                TextColumn::make('method')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->money(fn ($record): string => $record->currency ?? 'USD')
                    ->alignEnd(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
