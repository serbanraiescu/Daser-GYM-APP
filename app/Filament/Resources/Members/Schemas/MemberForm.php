<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Forms\Form;

class MemberForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->components([
                \Filament\Schemas\Components\Section::make('Informații Personale')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('first_name')
                            ->label('Prenume')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('last_name')
                            ->label('Nume')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                        \Filament\Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->required(),
                        \Filament\Forms\Components\Placeholder::make('barcode')
                            ->label('Cod de bare (8 Caractere)')
                            ->content(fn ($record) => $record?->user?->barcode ?? 'Se generează automat la activarea contului (dacă are acces).'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Statut și Categorie')
                    ->schema([
                        \Filament\Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'ACTIVE' => 'Activ',
                                'INACTIVE' => 'Inactiv',
                                'EXPIRED' => 'Expirat',
                            ])
                            ->default('INACTIVE')
                            ->required(),
                        \Filament\Forms\Components\Select::make('category')
                            ->label('Categorie')
                            ->options([
                                'default' => 'Standard',
                                'student' => 'Student',
                                'senior' => 'Pensionar',
                                'vip' => 'VIP',
                            ])
                            ->default('default')
                            ->required(),
                        \Filament\Forms\Components\FileUpload::make('photo_url')
                            ->label('Fotografie Profil')
                            ->image()
                            ->directory('members'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Detalii Suplimentare')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Note / Observații')
                            ->rows(3),
                    ]),
                \Filament\Schemas\Components\Section::make('Abonamente (Înscrieri)')
                    ->description('Adaugă sau gestionează abonamentele acestui membru.')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('memberships')
                            ->relationship()
                            ->label('Lista Abonamente')
                            ->addActionLabel('Adaugă Abonament Nou')
                            ->schema([
                                \Filament\Forms\Components\Select::make('plan_id')
                                    ->relationship('plan', 'name')
                                    ->label('Abonament (Plan)')
                                    ->required()
                                    ->native(false),
                                \Filament\Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Data de Început')
                                    ->required()
                                    ->default(now()),
                                \Filament\Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('Data Expirării')
                                    ->required(),
                                \Filament\Forms\Components\Select::make('status')
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
