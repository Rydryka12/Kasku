<?php

namespace Database\Seeders;

use App\Enums\DebtType;
use App\Enums\GoalStatus;
use App\Enums\RecurringFrequency;
use App\Enums\RecurringStatus;
use App\Enums\Role;
use App\Enums\TransactionType;
use App\Models\AccountSource;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\RecurringTransaction;
use App\Models\SavingsEntry;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\CategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'user@kasku.test'],
            [
                'name' => 'Ryan Adryan',
                'password' => Hash::make('password'),
                'role' => Role::User,
                'is_active' => true,
            ]
        );

        app(CategoryService::class)->copyDefaultCategoriesToUser($user);

        // Sources
        $bca = AccountSource::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'BCA'],
            [
                'type' => 'bank',
                'initial_balance' => 10_000_000,
                'current_balance' => 10_000_000,
                'color' => '#2B3A8C',
                'icon' => 'building-library',
            ]
        );

        $gopay = AccountSource::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Gopay'],
            [
                'type' => 'ewallet',
                'initial_balance' => 500_000,
                'current_balance' => 500_000,
                'color' => '#0E7C86',
                'icon' => 'device-phone-mobile',
            ]
        );

        $cash = AccountSource::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Cash'],
            [
                'type' => 'cash',
                'initial_balance' => 1_500_000,
                'current_balance' => 1_500_000,
                'color' => '#0B7A55',
                'icon' => 'banknotes',
            ]
        );

        // Categories
        $catMakan = Category::where('user_id', $user->id)->where('name', 'Makan & Minum')->first()
            ?? Category::where('user_id', $user->id)->where('type', 'expense')->first();
        $catTransport = Category::where('user_id', $user->id)->where('name', 'Transportasi')->first()
            ?? $catMakan;
        $catGaji = Category::where('user_id', $user->id)->where('type', 'income')->first();

        // 1. Transactions
        $t1 = Transaction::firstOrCreate(
            ['user_id' => $user->id, 'description' => 'Makan siang bersama tim'],
            [
                'account_source_id' => $gopay->id,
                'category_id' => $catMakan?->id,
                'type' => TransactionType::Expense,
                'amount' => 45_000,
                'tax_amount' => 4_500,
                'tax_note' => 'PB1 Resto',
                'date' => now()->toDateString(),
            ]
        );
        if ($t1->wasRecentlyCreated) {
            $gopay->decrement('current_balance', 45_000 + 4_500);
        }

        $t2 = Transaction::firstOrCreate(
            ['user_id' => $user->id, 'description' => 'Kopi pagi'],
            [
                'account_source_id' => $cash->id,
                'category_id' => $catMakan?->id,
                'type' => TransactionType::Expense,
                'amount' => 25_000,
                'date' => now()->toDateString(),
            ]
        );
        if ($t2->wasRecentlyCreated) {
            $cash->decrement('current_balance', 25_000);
        }

        // 2. Transfer: BCA ke Gopay 200.000 + admin 2.500
        if (! Transfer::where('user_id', $user->id)->exists()) {
            Transfer::create([
                'user_id' => $user->id,
                'from_source_id' => $bca->id,
                'to_source_id' => $gopay->id,
                'amount' => 200_000,
                'admin_fee' => 2_500,
                'date' => now()->toDateString(),
                'notes' => 'Top up saldo Gopay',
            ]);
            $bca->decrement('current_balance', 200_000 + 2_500);
            $gopay->increment('current_balance', 200_000);
        }

        // 3. Tabungan
        $darurat = SavingsGoal::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Dana Darurat'],
            [
                'target_amount' => 6_000_000,
                'current_amount' => 2_000_000,
                'deadline' => now()->addMonths(6)->toDateString(),
                'icon' => 'shield-check',
                'status' => GoalStatus::Active,
            ]
        );

        if (! SavingsEntry::where('goal_id', $darurat->id)->exists()) {
            SavingsEntry::create([
                'user_id' => $user->id,
                'goal_id' => $darurat->id,
                'source_id' => $bca->id,
                'type' => 'deposit',
                'amount' => 2_000_000,
                'date' => now()->subDays(5)->toDateString(),
                'notes' => 'Setoran awal dana darurat',
            ]);
            $bca->decrement('current_balance', 2_000_000);
        }

        // 4. Budget
        if ($catMakan) {
            Budget::firstOrCreate(
                ['user_id' => $user->id, 'category_id' => $catMakan->id, 'month' => now()->format('Y-m')],
                ['limit_amount' => 2_000_000]
            );
        }
        if ($catTransport) {
            Budget::firstOrCreate(
                ['user_id' => $user->id, 'category_id' => $catTransport->id, 'month' => now()->format('Y-m')],
                ['limit_amount' => 800_000]
            );
        }

        // 5. Transaksi Berulang
        RecurringTransaction::firstOrCreate(
            ['user_id' => $user->id, 'description' => 'Langganan Netflix'],
            [
                'account_source_id' => $bca->id,
                'category_id' => $catMakan?->id,
                'type' => TransactionType::Expense,
                'amount' => 186_000,
                'frequency' => RecurringFrequency::Monthly,
                'start_date' => now()->startOfMonth()->toDateString(),
                'next_run' => now()->addDays(3)->toDateString(),
                'status' => RecurringStatus::Active,
            ]
        );

        // 6. Hutang & Cicilan
        $debt = Debt::firstOrCreate(
            ['user_id' => $user->id, 'person' => 'Andi Wijaya'],
            [
                'type' => DebtType::Debt,
                'amount' => 1_000_000,
                'paid_amount' => 500_000,
                'due_date' => now()->addDays(2)->toDateString(),
                'status' => 'partial',
                'note' => 'Pinjaman pembelian perlengkapan kerja',
            ]
        );

        if (! DebtPayment::where('debt_id', $debt->id)->exists()) {
            DebtPayment::create([
                'user_id' => $user->id,
                'debt_id' => $debt->id,
                'account_source_id' => $bca->id,
                'amount' => 500_000,
                'date' => now()->subDays(3)->toDateString(),
                'note' => 'Cicilan pertama',
            ]);
            $bca->decrement('current_balance', 500_000);
        }

        // Sinkronisasi saldo akun demo agar 100% konsisten terhadap mutasi
        $balanceService = app(BalanceService::class);
        $userSources = AccountSource::where('user_id', $user->id)->get();
        foreach ($userSources as $src) {
            $diff = $balanceService->verify($src);
            if ($diff !== 0) {
                $src->current_balance -= $diff;
                $src->save();
            }
        }
    }
}
