<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\FieldType;
use App\Enums\PricingMode;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormsRelationManager extends RelationManager
{
    protected static string $relationship = 'forms';

    protected static ?string $title = 'Dynamic forms';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Repeater::make('fields')
                    ->relationship('fields')
                    ->label('Fields / steps')
                    ->columnSpanFull()
                    ->reorderable()
                    ->orderColumn('sort_order')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'New field')
                    ->addActionLabel('Add field')
                    ->defaultItems(1)
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (blank($get('key'))) {
                                    $set('key', Str::snake((string) $state));
                                }
                            }),
                        TextInput::make('key')
                            ->required()
                            ->helperText('Used as the field identifier in order data.'),
                        Select::make('type')
                            ->options(FieldType::class)
                            ->default(FieldType::Select->value)
                            ->live()
                            ->required(),
                        Select::make('pricing_mode')
                            ->label('Pricing mode')
                            ->options(PricingMode::class)
                            ->default(PricingMode::Addon->value)
                            ->live()
                            ->required()
                            ->helperText('Add-on: each chosen option adds to the total. Price selector: the customer picks the price and the product\'s shown price becomes the cheapest option.'),
                        TextInput::make('extra_price')
                            ->label('Base extra price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->visible(fn (Get $get): bool => self::stateValue($get('pricing_mode')) !== PricingMode::Absolute->value),
                        Select::make('options_columns')
                            ->label('Options per row')
                            ->options([1 => '1 per row', 2 => '2 per row'])
                            ->default(1)
                            ->helperText('Radio/checkbox layout on the product page.')
                            ->visible(fn (Get $get): bool => in_array(self::stateValue($get('type')), [
                                FieldType::Radio->value,
                                FieldType::Checkbox->value,
                            ], true)),
                        TextInput::make('step')
                            ->label('Step')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('auto')
                            ->helperText('Step-by-step layout only: fields with the SAME number share one step. Leave blank to give this field its own step.'),
                        Toggle::make('required')
                            ->inline(false),
                        TextInput::make('tooltip'),

                        Repeater::make('options')
                            ->label('Options (the choices the customer selects)')
                            ->helperText('Add one row per choice. "Price" = the full price for Price-selector groups, or the amount added for Add-on groups.')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => in_array(self::stateValue($get('type')), [
                                FieldType::Select->value,
                                FieldType::Radio->value,
                                FieldType::Checkbox->value,
                            ], true))
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'New option')
                            ->addActionLabel('Add option')
                            ->defaultItems(1)
                            ->reorderable()
                            ->schema([
                                TextInput::make('label')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                        if (blank($get('value'))) {
                                            $set('value', Str::slug((string) $state));
                                        }
                                    }),
                                TextInput::make('value')
                                    ->required()
                                    ->helperText('Internal identifier (auto-filled from the label).'),
                                TextInput::make('extra_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0),
                                TextInput::make('tooltip')
                                    ->label('Tooltip')
                                    ->helperText('Optional info shown on hover.'),
                                Toggle::make('popular')
                                    ->label('Popular')
                                    ->inline(false),
                                Toggle::make('default')
                                    ->label('Selected by default')
                                    ->helperText('Preselect this option on page load. Leave all off for no preselection.')
                                    ->inline(false),
                            ])
                            ->columns(3),
                    ]),

                Actions::make([
                    Action::make('copyFieldsJson')
                        ->label('Copy fields JSON')
                        ->icon('heroicon-m-clipboard-document')
                        ->color('gray')
                        ->modalHeading('Fields JSON')
                        ->modalDescription('Copy this and paste it into another product via "Paste fields JSON".')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->fillForm(fn (Get $get): array => [
                            'json' => json_encode(
                                collect($get('fields') ?? [])
                                    ->values()
                                    ->map(function (array $field): array {
                                        $field['options'] = array_values($field['options'] ?? []);

                                        return $field;
                                    })
                                    ->all(),
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                            ),
                        ])
                        ->schema([
                            Textarea::make('json')
                                ->hiddenLabel()
                                ->rows(14)
                                ->readOnly(),
                        ]),
                    Action::make('pasteFieldsJson')
                        ->label('Paste fields JSON')
                        ->icon('heroicon-m-clipboard')
                        ->color('gray')
                        ->modalHeading('Paste fields JSON')
                        ->modalDescription('Paste a fields JSON copied from another product. This replaces the fields above — review them, then click Save changes.')
                        ->modalSubmitActionLabel('Load')
                        ->schema([
                            Textarea::make('json')
                                ->label('Fields JSON')
                                ->rows(14)
                                ->required(),
                        ])
                        ->action(function (array $data, Set $set): void {
                            $decoded = json_decode((string) ($data['json'] ?? ''), true);

                            if (! is_array($decoded) || $decoded === []) {
                                Notification::make()->title('Invalid or empty JSON')->danger()->send();

                                return;
                            }

                            $set('fields', array_values($decoded));

                            Notification::make()
                                ->title('Loaded '.count($decoded).' field(s) — review and Save changes')
                                ->success()
                                ->send();
                        }),
                ])->columnSpanFull(),
            ]);
    }

    /**
     * Normalise form state for comparisons. Filament keeps enum-cast attributes
     * as enum instances when editing a saved record, so a raw string comparison
     * would fail; this returns the backing scalar value either way.
     */
    private static function stateValue(mixed $state): ?string
    {
        if ($state instanceof \BackedEnum) {
            return (string) $state->value;
        }

        return is_scalar($state) ? (string) $state : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->weight('bold'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('fields_count')
                    ->counts('fields')
                    ->label('Fields')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->alignCenter(),
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
