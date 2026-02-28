<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'memberships' => fn ($q) => $q->latest()]))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('first_name')
                    ->label('Prenume')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('last_name')
                    ->label('Nume')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.barcode')
                    ->label('Cod de Bare')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Cod copiat!')
                    ->copyMessageDuration(1500)
                    ->fontFamily('mono')
                    ->badge()
                    ->color('info')
                    ->default('-'),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'warning',
                        'EXPIRED' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('active_membership_start')
                    ->label('Valabil de la')
                    ->getStateUsing(fn ($record) => $record->memberships->first()?->starts_at?->format('d.m.Y') ?? '-')
                    ->color('gray'),
                \Filament\Tables\Columns\TextColumn::make('active_membership_end')
                    ->label('Valabil până la')
                    ->getStateUsing(fn ($record) => $record->memberships->first()?->expires_at?->format('d.m.Y') ?? '-')
                    ->weight('bold')
                    ->color(fn ($record) => ($record->memberships->first()?->expires_at && $record->memberships->first()->expires_at->isPast()) ? 'danger' : 'success'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive',
                        'EXPIRED' => 'Expired',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'default' => 'Default',
                        'student' => 'Student',
                        'senior' => 'Senior',
                        'vip' => 'VIP',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                \Filament\Actions\Action::make('regenerateBarcode')
                    ->label('Regenereză Cod')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerare Cod de Bare')
                    ->modalDescription('Sunteți sigur că doriți să generați un nou cod de bare pentru acest membru? Vechiul cod (dacă era printat pe o cartelă) nu va mai fi valabil.')
                    ->modalSubmitActionLabel('Da, regenerează')
                    ->action(function (\App\Models\Member $record) {
                        $user = $record->user;
                        if ($user) {
                            $user->barcode = \App\Models\User::generateUniqueBarcode();
                            $user->save();
                            \Filament\Notifications\Notification::make()
                                ->title('Cod de bare regenerat cu succes!')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Eroare: Nu s-a găsit contul de utilizator atașat acestui membru.')
                                ->danger()
                                ->send();
                        }
                    }),
                \Filament\Tables\Actions\Action::make('renew')
                    ->label('Reînnoire')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->form([
                        Select::make('plan_id')
                            ->label('Abonament')
                            ->options(Plan::where('active', true)->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $set('amount', Plan::find($state)?->price ?? 0)),
                        TextInput::make('amount')
                            ->label('Sumă Incasată')
                            ->numeric()
                            ->required(),
                        Select::make('method')
                            ->label('Metodă Plată')
                            ->options([
                                'cash' => 'Numerar',
                                'card' => 'Card',
                                'online' => 'Online',
                            ])
                            ->default('cash')
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Models\Member $record) {
                        $plan = Plan::find($data['plan_id']);
                        $lastMembership = $record->memberships()->latest()->first();
                        
                        // Start after current expiration or now
                        $startsAt = ($lastMembership && $lastMembership->expires_at->isFuture()) 
                            ? $lastMembership->expires_at 
                            : now();
                        
                        $expiresAt = $startsAt->copy()->addDays($plan->duration_days);

                        DB::beginTransaction();
                        try {
                            $membership = Membership::create([
                                'member_id' => $record->id,
                                'plan_id' => $plan->id,
                                'starts_at' => $startsAt,
                                'expires_at' => $expiresAt,
                                'status' => 'ACTIVE',
                            ]);

                            Payment::create([
                                'member_id' => $record->id,
                                'membership_id' => $membership->id,
                                'amount' => $data['amount'],
                                'status' => 'PAID',
                                'paid_at' => now(),
                                'method' => $data['method'],
                            ]);

                            $record->update(['status' => 'ACTIVE']);
                            
                            DB::commit();

                            Notification::make()
                                ->title('Abonament reînnoit cu succes!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Eroare la reînnoire: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
