<?php

namespace App\Livewire\Admin;

use App\Models\Transaction;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        // Admin hanya diperbolehkan mengakses agregat COUNT (K-14, FR-ADM-04, FR-ADM-05)
        $totalUsers = User::where('role', 'user')->count();
        $totalTransactions = Transaction::count();
        $newUsersThisMonth = User::where('role', 'user')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Pertumbuhan user 6 bulan terakhir (COUNT per bulan)
        $userGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $targetMonth = now()->subMonths($i);
            $count = User::where('role', 'user')
                ->whereYear('created_at', $targetMonth->year)
                ->whereMonth('created_at', $targetMonth->month)
                ->count();

            $userGrowth[] = [
                'label' => $targetMonth->translatedFormat('M Y'),
                'count' => $count,
            ];
        }

        $maxGrowth = max(1, ...array_column($userGrowth, 'count'));

        return view('livewire.admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalTransactions' => $totalTransactions,
            'newUsersThisMonth' => $newUsersThisMonth,
            'userGrowth' => $userGrowth,
            'maxGrowth' => $maxGrowth,
        ]);
    }
}
