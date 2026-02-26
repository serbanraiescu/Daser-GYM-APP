<?php

namespace App\Filament\Resources\Members\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;

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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
