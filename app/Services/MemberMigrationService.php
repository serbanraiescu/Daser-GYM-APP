<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MemberMigrationService
{
    public function importFromCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle); // First line header
        // Skip the second line (it seems to be a continuation of the header in the provided file)
        fgetcsv($handle);

        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== false) {
                if (empty($data[1]) || $data[1] == 'Nr. Crt.' || is_numeric($data[1] == false && empty($data[1]))) {
                    // Skip if name is empty or total row
                    if (str_contains(implode(',', $data), '29048') || str_contains(implode(',', $data), '19430')) {
                        continue; // Total rows
                    }
                    if (empty($data[1])) continue;
                }

                $fullName = $data[1];
                $phone = $data[2] ?? '';
                $email = $data[3] ?? null;
                $planName = $data[4] ?? 'Standard';
                $expiryStr = $data[5] ?? '';
                $valueStr = $data[8] ?? '0';

                // Split name
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[1] ?? $fullName;
                $lastName = $nameParts[0] ?? '';

                // Handle date (dd/mm/yy)
                try {
                    $expiresAt = null;
                    if (!empty($expiryStr) && $expiryStr != 'valabilitate') {
                        $expiresAt = Carbon::createFromFormat('d/m/y', $expiryStr);
                    }
                } catch (\Exception $e) {
                    $expiresAt = now();
                }

                // Handle Amount
                $amount = 0;
                if (is_numeric($valueStr)) {
                    $amount = (float) $valueStr;
                }

                // 1. Create/Find Member
                $member = Member::updateOrCreate(
                    ['phone' => $phone],
                    [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'status' => ($expiresAt && $expiresAt->isPast()) ? 'EXPIRED' : 'ACTIVE',
                    ]
                );

                // 2. Find/Create Plan
                $plan = Plan::firstOrCreate(
                    ['name' => $planName],
                    [
                        'price' => $amount,
                        'duration_days' => 30,
                        'active' => true
                    ]
                );

                // 3. Create Membership
                $membership = Membership::create([
                    'member_id' => $member->id,
                    'plan_id' => $plan->id,
                    'starts_at' => $expiresAt ? $expiresAt->copy()->subDays(30) : now()->subDays(30),
                    'expires_at' => $expiresAt,
                    'status' => ($expiresAt && $expiresAt->isPast()) ? 'EXPIRED' : 'ACTIVE',
                ]);

                // 4. Create Payment
                Payment::create([
                    'member_id' => $member->id,
                    'membership_id' => $membership->id,
                    'amount' => $amount,
                    'status' => 'PAID',
                    'paid_at' => $membership->starts_at,
                    'method' => 'cash',
                ]);

                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
