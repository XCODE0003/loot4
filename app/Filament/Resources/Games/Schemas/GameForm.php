<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Enums\GameStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Game')
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
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('image')
                            ->label('Main Image')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('discover_image')
                            ->collection('discover_image')
                            ->label('Discover Slider Image')
                            ->helperText('Image shown in the homepage game slider. Recommended: 400×560px portrait.')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                        TagsInput::make('tags')
                            ->placeholder('action, rpg, ...')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options(GameStatus::class)
                            ->default(GameStatus::Active->value)
                            ->required(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->collapsible()
                    ->collapsed()
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')->maxLength(255),
                        Textarea::make('meta_description')->rows(2),
                    ]),

                Section::make('Landing page settings')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        KeyValue::make('landing_settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value'),
                    ]),
            ]);
    }
}
