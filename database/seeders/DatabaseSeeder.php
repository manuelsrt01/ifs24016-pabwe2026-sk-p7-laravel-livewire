<?php

namespace Database\Seeders;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'password' => 'admin', 'role' => User::ROLE_ADMIN]
        );

        $member = User::firstOrCreate(
            ['username' => 'member'],
            ['name' => 'Member Demo', 'email' => 'member@example.com', 'password' => 'member', 'role' => User::ROLE_MEMBER]
        );

        if ($member->stocks()->exists()) {
            return;
        }

        $samples = [
            ['name' => 'Kertas A4 80gsm', 'sku' => 'KRT-A4-80', 'description' => "Kertas **A4** untuk printer.\n\n- 500 lembar per rim", 'quantity' => 120, 'unit' => 'rim', 'price' => 52000, 'min_stock' => 20],
            ['name' => 'Pulpen Hitam', 'sku' => 'PLP-BLK-01', 'description' => 'Pulpen tinta hitam 0.5mm.', 'quantity' => 8, 'unit' => 'pcs', 'price' => 3500, 'min_stock' => 10],
            ['name' => 'Spidol Whiteboard', 'sku' => 'SPD-WB-02', 'description' => 'Spidol papan tulis warna biru.', 'quantity' => 0, 'unit' => 'pcs', 'price' => 9000, 'min_stock' => 5],
            ['name' => 'Map Folder', 'sku' => 'MAP-F-03', 'description' => null, 'quantity' => 60, 'unit' => 'pcs', 'price' => 2500, 'min_stock' => 15],
            ['name' => 'Stapler Besar', 'sku' => 'STP-L-04', 'description' => 'Stapler kapasitas 50 lembar.', 'quantity' => 5, 'unit' => 'unit', 'price' => 45000, 'min_stock' => 5],
        ];

        foreach ($samples as $sample) {
            Stock::create($sample + ['user_id' => $member->id]);
        }
    }
}
