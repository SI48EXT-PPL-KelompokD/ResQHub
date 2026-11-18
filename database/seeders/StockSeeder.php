<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $stocks = [
            // Posko 1 (Baleendah)
            ['id' => 1, 'shelter_id' => 1, 'item_id' => 1, 'quantity' => 250, 'min_threshold' => 100, 'created_at' => $now, 'updated_at' => $now], // Beras
            ['id' => 2, 'shelter_id' => 1, 'item_id' => 2, 'quantity' => 45, 'min_threshold' => 20, 'created_at' => $now, 'updated_at' => $now], // Mie Instan
            ['id' => 3, 'shelter_id' => 1, 'item_id' => 3, 'quantity' => 28, 'min_threshold' => 30, 'created_at' => $now, 'updated_at' => $now], // Air Mineral (Menipis < 30)
            ['id' => 4, 'shelter_id' => 1, 'item_id' => 5, 'quantity' => 60, 'min_threshold' => 30, 'created_at' => $now, 'updated_at' => $now], // Minyak Goreng
            ['id' => 5, 'shelter_id' => 1, 'item_id' => 6, 'quantity' => 30, 'min_threshold' => 40, 'created_at' => $now, 'updated_at' => $now], // Selimut (Menipis < 40)
            ['id' => 6, 'shelter_id' => 1, 'item_id' => 15, 'quantity' => 5, 'min_threshold' => 20, 'created_at' => $now, 'updated_at' => $now], // Popok (Menipis)
            ['id' => 7, 'shelter_id' => 1, 'item_id' => 20, 'quantity' => 80, 'min_threshold' => 50, 'created_at' => $now, 'updated_at' => $now], // Matras

            // Posko 2 (Dayeuhkolot)
            ['id' => 8, 'shelter_id' => 2, 'item_id' => 1, 'quantity' => 200, 'min_threshold' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'shelter_id' => 2, 'item_id' => 2, 'quantity' => 30, 'min_threshold' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'shelter_id' => 2, 'item_id' => 3, 'quantity' => 15, 'min_threshold' => 25, 'created_at' => $now, 'updated_at' => $now], // Air Mineral Menipis
        ];

        DB::table('stocks')->upsert($stocks, ['id'], ['shelter_id', 'item_id', 'quantity', 'min_threshold', 'updated_at']);
    }
}
