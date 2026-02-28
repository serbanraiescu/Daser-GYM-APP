<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Member extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($member) {
            if ($member->email && !$member->user) {
                User::create([
                    'name' => $member->full_name,
                    'email' => $member->email,
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role' => 'member',
                    'member_id' => $member->id,
                ]);
            } elseif ($member->email && $member->user) {
                $member->user->update([
                    'name' => $member->full_name,
                    'email' => $member->email,
                ]);
            }
        });
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'category',
        'status',
        'notes',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
