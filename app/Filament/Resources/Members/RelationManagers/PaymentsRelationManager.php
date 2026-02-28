<?php

namespace App\Filament\Resources\Members\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    
    protected static ?string $title = 'Istoric Plăți';
    protected static ?string $label = 'Plată';
    protected static ?string $pluralLabel = 'Plăți';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Sumă')
                    ->required()
                    ->numeric()
                    ->money('RON'),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Data Plății')
                    ->required()
                    ->default(now()),
                Forms\Components\Select::make('method')
                    ->label('Metodă')
                    ->options([
                        'cash' => 'Numerar',
                        'card' => 'Card',
                        'online' => 'Online',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'PAID' => 'Plătit',
                        'PENDING' => 'În așteptare',
                        'FAILED' => 'Eșuat',
                    ])
                    ->default('PAID')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Sumă')
                    ->money('RON')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Data Plății')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Metodă')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAID' => 'success',
                        'PENDING' => 'warning',
                        'FAILED' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // We typically use the "Renew" action on the main list or "Add" here if needed
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
