<?php

namespace Database\Seeders;

use App\Models\ProfileBase;
use Illuminate\Database\Seeder;

class ProfileBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProfileBase::seedDefaultBases();
    }
}
