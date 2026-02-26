<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Membership;
use BackedEnum;

class MemberDashboard extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Contul Meu';
    protected static ?string $navigationLabel = 'Acasă';
    protected static ?string $slug = ''; // Makes this the default page

    protected string $view = 'filament.app.pages.member-dashboard';

    public $activeMembership;

    public function mount()
    {
        $user = auth()->user();
        
        if ($user && $user->member_id) {
            $this->activeMembership = Membership::with('plan')
                ->where('member_id', $user->member_id)
                ->where('status', 'ACTIVE')
                ->latest()
                ->first();
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Cardul Meu de Membru';
    }
}
