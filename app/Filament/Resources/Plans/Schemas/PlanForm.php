<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nume Abonament')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('description')
                    ->label('Descriere / Detalii Publice')
                    ->placeholder('Ex: Acces nelimitat la sala de forță și cardio...')
                    ->rows(2)
                    ->columnSpanFull(),
                \Filament\Schemas\Components\Group::make([
                    \Filament\Forms\Components\TextInput::make('price')
                        ->label('Preț')
                        ->numeric()
                        ->prefix('RON')
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('duration_days')
                        ->label('Durată (Zile)')
                        ->numeric()
                        ->default(30)
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('grace_days_override')
                        ->label('Zile de Grație (Opțional)')
                        ->numeric()
                        ->helperText('Dacă este gol, se folosește setarea globală.'),
                ])->columns(3),
                \Filament\Schemas\Components\Section::make('Facilități & Reguli')
                    ->description('Selectați ce facilități sunt incluse în acest tip de abonament.')
                    ->schema([
                        \Filament\Forms\Components\CheckboxList::make('features')
                            ->label('Facilități Incluse')
                            ->options(fn () => \App\Models\PlanFeature::where('active', true)->pluck('name', 'slug'))
                            ->descriptions(fn () => \App\Models\PlanFeature::where('active', true)->pluck('description', 'slug'))
                            ->columns(2)
                            ->gridDirection('column'),
                        \Filament\Forms\Components\Toggle::make('active')
                            ->label('Abonament Activ')
                            ->default(true),
                    ]),
            ]);
    }
}
