<?php

namespace App\Features\Stock\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Services\StockSummaryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(StockSummaryService $summary, AdminUserService $admin)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return view('livewire.stock.dashboard', [
                'user' => $user,
                'isAdmin' => true,
                'summary' => $summary->forAll(),
                'overview' => $admin->overview(),
            ]);
        }

        return view('livewire.stock.dashboard', [
            'user' => $user,
            'isAdmin' => false,
            'summary' => $summary->forUser($user),
            'overview' => null,
        ]);
    }
}
