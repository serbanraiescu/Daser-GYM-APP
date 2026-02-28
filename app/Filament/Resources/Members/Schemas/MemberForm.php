<?php

namespace App\Filament\Resources\Members\Schemas;

use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make(2)
                    ->schema([
                        \Filament\Schemas\Components\Section::make('Informații Membru')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('first_name')
                                    ->label('Prenume')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('last_name')
                                    ->label('Nume')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
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
                                \Filament\Forms\Components\Select::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'ACTIVE' => 'Activ',
                                        'INACTIVE' => 'Inactiv',
                                        'EXPIRED' => 'Expirat',
                                    ])
                                    ->default('ACTIVE')
                                    ->required(),
                            ])->columns(2),
                        
                        \Filament\Schemas\Components\Group::make([
                            \Filament\Schemas\Components\Section::make('Abonament Inițial & Plată')
                                ->description('Activează abonamentul acum.')
                                ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Members\Pages\CreateMember)
                                ->schema([
                                    \Filament\Forms\Components\Toggle::make('activate_plan')
                                        ->label('Activează Abonament')
                                        ->reactive()
                                        ->default(true),
                                    \Filament\Forms\Components\Select::make('initial_plan_id')
                                        ->label('Plan')
                                        ->options(\App\Models\Plan::where('active', true)->pluck('name', 'id'))
                                        ->required(fn ($get) => $get('activate_plan'))
                                        ->visible(fn ($get) => $get('activate_plan'))
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, $set) => $set('initial_amount', \App\Models\Plan::find($state)?->price ?? 0)),
                                    \Filament\Forms\Components\TextInput::make('initial_amount')
                                        ->label('Preț')
                                        ->numeric()
                                        ->required(fn ($get) => $get('activate_plan'))
                                        ->visible(fn ($get) => $get('activate_plan')),
                                    \Filament\Forms\Components\Select::make('initial_payment_method')
                                        ->label('Metodă Plată')
                                        ->options([
                                            'cash' => 'Numerar',
                                            'card' => 'Card',
                                            'online' => 'Online',
                                        ])
                                        ->default('cash')
                                        ->required(fn ($get) => $get('activate_plan'))
                                        ->visible(fn ($get) => $get('activate_plan')),
                                ]),
                                
                            \Filament\Schemas\Components\Section::make('Foto Profil')
                                ->schema([
                                    \Filament\Forms\Components\FileUpload::make('photo_url')
                                        ->label(false)
                                        ->image()
                                        ->directory('members'),
                                ])->collapsible(),
                        ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Note')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label(false)
                            ->rows(2),
                    ])->collapsible()->collapsed(),

                \Filament\Schemas\Components\Section::make('Lista Abonamente')
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\Members\Pages\EditMember)
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('memberships')
                            ->relationship()
                            ->label(false)
                            ->addActionLabel('Adaugă Abonament Nou')
                            ->schema([
                                \Filament\Forms\Components\Select::make('plan_id')
                                    ->relationship('plan', 'name')
                                    ->label('Plan')
                                    ->required(),
                                \Filament\Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('Început')
                                    ->required()
                                    ->default(now()),
                                \Filament\Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('Expirare')
                                    ->required(),
                                \Filament\Forms\Components\Select::make('status')
                                    ->label('Status')
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
