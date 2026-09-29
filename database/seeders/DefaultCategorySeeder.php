<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            // Pemasukan
            ['name' => 'Gaji', 'type' => CategoryType::Income, 'color' => '#0B7A55', 'icon' => 'banknotes'],
            ['name' => 'Bonus & THR', 'type' => CategoryType::Income, 'color' => '#2B3A8C', 'icon' => 'gift'],
            ['name' => 'Investasi & Dividen', 'type' => CategoryType::Income, 'color' => '#0E7C86', 'icon' => 'chart'],
            ['name' => 'Penjualan / Usaha', 'type' => CategoryType::Income, 'color' => '#B7791F', 'icon' => 'shopping-bag'],
            ['name' => 'Pemasukan Lainnya', 'type' => CategoryType::Income, 'color' => '#44515C', 'icon' => 'ellipsis-horizontal'],

            // Pengeluaran
            ['name' => 'Makan & Minum', 'type' => CategoryType::Expense, 'color' => '#B93A2B', 'icon' => 'cake'],
            ['name' => 'Transportasi', 'type' => CategoryType::Expense, 'color' => '#2B3A8C', 'icon' => 'truck'],
            ['name' => 'Belanja Harian', 'type' => CategoryType::Expense, 'color' => '#B7791F', 'icon' => 'shopping-cart'],
            ['name' => 'Tagihan & Utilitas', 'type' => CategoryType::Expense, 'color' => '#6B3FA0', 'icon' => 'bolt'],
            ['name' => 'Hiburan & Hobi', 'type' => CategoryType::Expense, 'color' => '#C2547A', 'icon' => 'sparkles'],
            ['name' => 'Kesehatan', 'type' => CategoryType::Expense, 'color' => '#0E7C86', 'icon' => 'heart'],
            ['name' => 'Pendidikan', 'type' => CategoryType::Expense, 'color' => '#4F6B2D', 'icon' => 'academic-cap'],
            ['name' => 'Keluarga & Donasi', 'type' => CategoryType::Expense, 'color' => '#7A5230', 'icon' => 'user-group'],
            ['name' => 'Pengeluaran Lainnya', 'type' => CategoryType::Expense, 'color' => '#44515C', 'icon' => 'ellipsis-horizontal'],
        ];

        foreach ($defaults as $item) {
            Category::firstOrCreate(
                [
                    'user_id' => null,
                    'name' => $item['name'],
                    'type' => $item['type'],
                ],
                [
                    'color' => $item['color'],
                    'icon' => $item['icon'],
                    'archived_at' => null,
                ]
            );
        }
    }
}
