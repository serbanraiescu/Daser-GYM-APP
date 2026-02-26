<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'member_id',
        'barcode',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->role === 'member' && empty($user->barcode)) {
                $user->barcode = self::generateUniqueBarcode();
            }
        });
    }

    /**
     * Generate a unique 8-character alphanumeric barcode.
     */
    public static function generateUniqueBarcode(): string
    {
        do {
            $barcode = strtoupper(\Illuminate\Support\Str::random(8));
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        Log::info('Checking panel access', [
            'user' => $this->email,
            'role' => $this->role,
            'panel' => $panel->getId()
        ]);

        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'app') {
            // Allow members AND admins to see the client portal for testing/management
            return $this->isAdmin() || $this->isMember();
        }

        return false;
    }
}
