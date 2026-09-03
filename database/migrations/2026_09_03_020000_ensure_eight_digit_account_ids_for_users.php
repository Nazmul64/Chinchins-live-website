<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = User::all();
        foreach ($users as $user) {
            // If empty, too short (< 8 digits), or phone number instead of account id
            if (empty($user->account_id) || strlen((string)$user->account_id) < 8 || strlen((string)$user->account_id) > 10 || str_starts_with((string)$user->account_id, '8801')) {
                $user->account_id = User::generateUniqueAccountId();
                $user->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
