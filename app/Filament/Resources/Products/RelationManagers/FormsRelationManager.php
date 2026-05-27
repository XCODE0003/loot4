<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\FieldType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
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
                        TextInput::make('extra_price')
                            ->label('Base extra price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Toggle::make('required')
                            ->inline(false),
                        TextInput::make('tooltip'),

                        Repeater::make('options')
                            ->label('Options')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                FieldType::Select->value,
                                FieldType::Radio->value,
                                FieldType::Checkbox->value,
                            ], true))
                            ->addActionLabel('Add option')
                            ->schema([
                                TextInput::make('label')->required(),
                                TextInput::make('value')->required(),
                                TextInput::make('extra_price')
                                    ->label('Extra price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0),
                            ])
                            ->columns(3),

                        KeyValue::make('conditional_logic')
                            ->label('Conditional logic')
                            ->keyLabel('Depends on field')
                            ->valueLabel('Equals value')
                            ->columnSpanFull(),
                    ]),
            ]);
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
