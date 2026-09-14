<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds for strictly the 30 strong-motion animated gifts.
     */
    public function run(): void
    {
        $this->call(StrongMotionGiftsSeeder::class);
    }
}
