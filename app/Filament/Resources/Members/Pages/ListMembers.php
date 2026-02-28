<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adaugă membru')
                ->modalWidth('3xl')
                ->after(function (array $data, \App\Models\Member $record) {
                    if ($data['activate_plan'] ?? false) {
                        $plan = \App\Models\Plan::find($data['initial_plan_id']);
                        
                        $membership = \App\Models\Membership::create([
                            'member_id' => $record->id,
                            'plan_id' => $plan->id,
                            'starts_at' => now(),
                            'expires_at' => now()->addDays($plan->duration_days),
                            'status' => 'ACTIVE',
                        ]);

                        \App\Models\Payment::create([
                            'member_id' => $record->id,
                            'membership_id' => $membership->id,
                            'amount' => $data['initial_amount'],
                            'status' => 'PAID',
                            'paid_at' => now(),
                            'method' => $data['initial_payment_method'],
                        ]);

                        $record->update(['status' => 'ACTIVE']);
                    }
                }),
            \Filament\Actions\Action::make('importJan')
                ->label('Importă Ianuarie')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $service = app(\App\Services\MemberMigrationService::class);
                    $path = base_path('import/ian.csv');
                    if (file_exists($path)) {
                        $result = $service->importFromCsv($path);
                        \Filament\Notifications\Notification::make()
                            ->title('Import Ianuarie finalizat!')
                            ->body("Au fost importați {$result['imported']} membri.")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fișierul import/ian.csv nu a fost găsit!')
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation(),
            \Filament\Actions\Action::make('importFeb')
                ->label('Importă Februarie')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    $service = app(\App\Services\MemberMigrationService::class);
                    $path = base_path('import/feb.csv');
                    if (file_exists($path)) {
                        $result = $service->importFromCsv($path);
                        \Filament\Notifications\Notification::make()
                            ->title('Import Februarie finalizat!')
                            ->body("Au fost importați {$result['imported']} membri.")
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Fișierul import/feb.csv nu a fost găsit!')
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation(),
        ];
    }
}
