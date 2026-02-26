<?php

namespace App\Filament\Resources;

use App\Models\PlanFeature;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Icons\Heroicon;

class PlanFeatureResource extends Resource
{
    protected static ?string $model = PlanFeature::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    
    protected static ?string $navigationGroup = 'Configurare';
    
    protected static ?string $label = 'Facilitate Abonament';
    protected static ?string $pluralLabel = 'Facilități Abonament';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->components([
                \Filament\Forms\Components\Section::make('Detalii Facilitate')
                    ->description('Definiți o facilitate care poate fi atașată unui abonament.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nume Facilitate')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, \Filament\Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null
                            ),
                        TextInput::make('slug')
                            ->label('Slug (Identificator)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Descriere / Explicație')
                            ->helperText('Această explicație va ajuta clienții să înțeleagă ce include abonamentul.')
                            ->columnSpanFull(),
                        Toggle::make('active')
                            ->label('Activă')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identificator'),
                IconColumn::make('active')
                    ->label('Activă')
                    ->boolean(),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PlanFeatureResource\Pages\ListPlanFeatures::route('/'),
            'create' => PlanFeatureResource\Pages\CreatePlanFeature::route('/create'),
            'edit' => PlanFeatureResource\Pages\EditPlanFeature::route('/{record}/edit'),
        ];
    }
}
