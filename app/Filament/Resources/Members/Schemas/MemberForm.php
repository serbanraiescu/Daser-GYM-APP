<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informații Personale')
                    ->schema([
                        \Filament\Schemas\Components\TextInput::make('first_name')
                            ->label('Prenume')
                            ->required(),
                        \Filament\Schemas\Components\TextInput::make('last_name')
                            ->label('Nume')
                            ->required(),
                        \Filament\Schemas\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        \Filament\Schemas\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->required(),
                        \Filament\Schemas\Components\Placeholder::make('barcode')
                            ->label('Cod de bare (8 Caractere)')
                            ->content(fn ($record) => $record?->user?->barcode ?? 'Se generează automat la activarea contului (dacă are acces).'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Statut și Categorie')
                    ->schema([
                        \Filament\Schemas\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'ACTIVE' => 'Activ',
                                'INACTIVE' => 'Inactiv',
                                'EXPIRED' => 'Expirat',
                            ])
                            ->default('INACTIVE')
                            ->required(),
                        \Filament\Schemas\Components\Select::make('category')
                            ->label('Categorie')
                            ->options([
                                'default' => 'Standard',
                                'student' => 'Student',
                                'senior' => 'Pensionar',
                                'vip' => 'VIP',
                            ])
                            ->default('default')
                            ->required(),
                        \Filament\Schemas\Components\FileUpload::make('photo_url')
                            ->label('Fotografie Profil')
                            ->image()
                            ->directory('members'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Detalii Suplimentare')
                    ->schema([
                        \Filament\Schemas\Components\Textarea::make('notes')
                            ->label('Note / Observații')
                            ->rows(3),
                    ]),
                \Filament\Schemas\Components\Section::make('Abonamente (Înscrieri)')
                    ->description('Adaugă sau gestionează abonamentele acestui membru.')
                    ->schema([
                        \Filament\Schemas\Components\Repeater::make('memberships')
                            ->relationship()
                            ->label('Lista Abonamente')
                            ->addActionLabel('Adaugă Abonament Nou')
                            ->schema([
                                \Filament\Schemas\Components\Select::make('plan_id')
                                    ->relationship('plan', 'name')
                                    ->label('Abonament (Plan)')
                                    ->required()
                                    ->native(false),
                                \Filament\Schemas\Components\DateTimePicker::make('starts_at')
                                    ->label('Data de Început')
                                    ->required()
                                    ->default(now()),
                                \Filament\Schemas\Components\DateTimePicker::make('expires_at')
                                    ->label('Data Expirării')
                                    ->required(),
                                \Filament\Schemas\Components\Select::make('status')
                                    ->label('Status Abonament')
                                    ->options([
                                        'ACTIVE' => 'Activ',
                                        'EXPIRED' => 'Expirat',
                                        'CANCELLED' => 'Anulat',
                                    ])
                                    ->default('ACTIVE')
                                    ->required(),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['plan_id']) ? \App\Models\Plan::find($state['plan_id'])?->name : null),
                    ]),
            ]);
    }
}
