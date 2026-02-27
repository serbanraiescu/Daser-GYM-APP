<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detalii Plată')
                    ->schema([
                        \Filament\Forms\Components\Select::make('member_id')
                            ->label('Membru')
                            ->relationship('member', 'last_name')
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('membership_id')
                            ->relationship('membership', 'id')
                            ->label('ID Înscriere'),
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->label('Sumă')
                            ->numeric()
                            ->money('RON')
                            ->required(),
                        \Filament\Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Data și Ora Plății')
                            ->default(now()),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Procesare')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'PENDING' => 'În așteptare',
                                'PAID' => 'Plătit',
                                'FAILED' => 'Eșuat',
                                'REFUNDED' => 'Rambursat',
                            ])
                            ->default('PAID')
                            ->required(),
                        \Filament\Forms\Components\Select::make('method')
                            ->label('Metodă de Plată')
                            ->options([
                                'cash' => 'Numerar (Cash)',
                                'card' => 'Card',
                                'online' => 'Online',
                            ])
                            ->default('cash')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
