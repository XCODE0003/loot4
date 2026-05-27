<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->default(fn (): string => strtoupper(Str::random(8)))
                            ->suffixAction(
                                \Filament\Actions\Action::make('generate')
                                    ->icon('heroicon-m-sparkles')
                                    ->tooltip('Generate code')
                                    ->action(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('code', strtoupper(Str::random(8)))),
                            ),
                        Toggle::make('is_active')
                            ->default(true),
                        Select::make('type')
                            ->options(CouponType::class)
                            ->default(CouponType::Percentage->value)
                            ->required(),
                        TextInput::make('value')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('Percentage (%) or fixed amount, depending on type.'),
                    ]),

                Section::make('Restrictions')
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_order_amount')
                            ->numeric()
                            ->prefix('$')
                            ->label('Minimum order amount'),
                        TextInput::make('usage_limit')
                            ->numeric()
                            ->helperText('Total redemptions allowed (leave empty for unlimited).'),
                        TextInput::make('per_user_limit')
                            ->numeric()
                            ->label('Per-user limit'),
                        TextInput::make('used_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('product_ids')
                            ->label('Restrict to products')
                            ->multiple()
                            ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                        Select::make('user_ids')
                            ->label('Restrict to users')
                            ->multiple()
                            ->options(fn (): array => User::query()->orderBy('email')->pluck('email', 'id')->all())
                            ->searchable(),
                        DateTimePicker::make('starts_at'),
                        DateTimePicker::make('expires_at'),
                    ]),
            ]);
    }
}
