<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();

        if ($data['activate_plan'] ?? false) {
            $plan = \App\Models\Plan::find($data['initial_plan_id']);
            
            $membership = \App\Models\Membership::create([
                'member_id' => $this->record->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'status' => 'ACTIVE',
            ]);

            \App\Models\Payment::create([
                'member_id' => $this->record->id,
                'membership_id' => $membership->id,
                'amount' => $data['initial_amount'],
                'status' => 'PAID',
                'paid_at' => now(),
                'method' => $data['initial_payment_method'],
            ]);

            $this->record->update(['status' => 'ACTIVE']);
        }
    }
}
