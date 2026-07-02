<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\OptionsLayout;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Main')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->options(ProductType::class)
                            ->default(ProductType::Items->value)
                            ->required(),
                        Select::make('status')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Draft->value)
                            ->required(),
                        Textarea::make('short_description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('html_description')
                            ->label('Description (HTML)')
                            ->helperText('Raw HTML is allowed and rendered as-is on the product page.')
                            ->rows(8)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->helperText('Base price. If the product has a "Price selector" option group, the shown price becomes the cheapest option and this value is the fallback base.'),
                        TextInput::make('compare_price')
                            ->label('Compare-at price')
                            ->numeric()
                            ->prefix('$'),
                        Select::make('currency_id')
                            ->relationship('currency', 'code')
                            ->preload()
                            ->label('Currency'),
                    ]),

                Section::make('Media')
                    ->columns(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('main')
                            ->collection('main')
                            ->image()
                            ->imageEditor()
                            ->label('Main image'),
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->label('Gallery'),
                    ]),

                Section::make('Associations')
                    ->columns(3)
                    ->schema([
                        Select::make('game_id')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Game'),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload(),
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload(),
                        TagsInput::make('filter_values')
                            ->label('Filter values')
                            ->helperText('Values used for game-page filters. Must match the filter values configured on the game.')
                            ->placeholder('EU')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        Toggle::make('auto_delivery')->inline(false),
                        Repeater::make('delivery_options')
                            ->label('Delivery options')
                            ->helperText('Delivery choices shown on this product at checkout — add your own (e.g. "Standard (1–24h)" free, "Express (1–12h)" paid). Set price to 0 for free. Leave empty for no delivery choice.')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Name / time')
                                    ->placeholder('Express (1–12 hours)')
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpan(2),
                                TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('$')
                                    ->helperText('0 = free'),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Add delivery option')
                            ->columnSpanFull(),
                        Toggle::make('featured')->inline(false),
                        Toggle::make('visibility')->default(true)->inline(false),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Select::make('options_layout')
                            ->label('Options layout')
                            ->options(OptionsLayout::class)
                            ->default(OptionsLayout::Single->value)
                            ->helperText('How option groups appear on the product page: all on one page, or as a step-by-step wizard.'),
                        Textarea::make('delivery_instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                        CheckboxList::make('allowed_payment_methods')
                            ->options([
                                'stripe' => 'Stripe',
                                'paypal' => 'PayPal',
                                'crypto' => 'Crypto',
                                'apple_pay' => 'Apple Pay',
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->collapsible()
                    ->collapsed()
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')->maxLength(255),
                        Textarea::make('meta_description')->rows(2),
                        TextInput::make('og_image')->label('OpenGraph image URL')->url(),
                    ]),
            ]);
    }
}
