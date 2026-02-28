<?php

namespace App\Filament\Resources\Members\Schemas;
// sync

use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                ->schema([
                    // Left Side (1/2)
                    \Filament\Schemas\Components\Group::make([
                        \Filament\Schemas\Components\Section::make('Informații Membru')
                            ->icon('heroicon-o-user')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('first_name')
                                    ->label('Prenume')
                                    ->required()
                                    ->prefixIcon('heroicon-m-user-circle'),
                                \Filament\Forms\Components\TextInput::make('last_name')
                                    ->label('Nume')
                                    ->required()
                                    ->prefixIcon('heroicon-m-user-circle'),
                                \Filament\Forms\Components\TextInput::make('phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->required()
                                    ->prefixIcon('heroicon-m-phone'),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->prefixIcon('heroicon-m-envelope'),
                                \Filament\Forms\Components\Select::make('category')
                                    ->label('Categorie')
                                    ->options([
                                        'default' => 'Standard',
                                        'student' => 'Student',
                                        'senior' => 'Pensionar',
                                        'vip' => 'VIP',
                                    ])
                                    ->default('default')
                                    ->prefixIcon('heroicon-m-tag')
                                    ->required(),
                                \Filament\Forms\Components\Select::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'ACTIVE' => 'Activ',
                                        'INACTIVE' => 'Inactiv',
                                        'EXPIRED' => 'Expirat',
                                    ])
                                    ->default('ACTIVE')
                                    ->prefixIcon('heroicon-m-check-badge')
                                    ->required(),
                                \Filament\Forms\Components\Placeholder::make('barcode')
                                    ->label('Cod de bare')
                                    ->content(fn ($record) => $record?->user?->barcode ?? 'Se generează automat la salvare.'),
                            ])
                            ->columns(2),

                        \Filament\Schemas\Components\Section::make('Note / Observații')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                \Filament\Forms\Components\Textarea::make('notes')
                                    ->label(false)
                                    ->placeholder('Observații despre membru...')
                                    ->rows(4),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(1),
                    
                    // Right Side (1/2)
                    \Filament\Schemas\Components\Group::make([
                        \Filament\Schemas\Components\Section::make('Abonament & Plată')
                            ->icon('heroicon-o-credit-card')
                            ->description('Activează imediat.')
                            ->visible(fn ($record) => $record === null)
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
                                    ->prefix('RON')
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
                            ->icon('heroicon-o-camera')
                            ->schema([
                                \Filament\Forms\Components\FileUpload::make('photo_url')
                                    ->label(false)
                                    ->image()
                                    ->directory('members'),
                            ])->collapsible(),
                    ])
                    ->columnSpan(1),
                ]),

                \Filament\Schemas\Components\Section::make('Lista Abonamente')
                    ->visible(fn ($record) => $record !== null)
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
