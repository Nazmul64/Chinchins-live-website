<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'bKash Personal',
                'code' => 'bkash',
                'account_type' => 'Personal',
                'account_number' => '01700000000',
                'instructions' => "1. Go to your bKash Mobile App or dial *247#\n2. Select 'Send Money' option\n3. Enter the Number: 01700000000\n4. Enter the Amount and your bKash PIN\n5. Copy the Transaction ID (TrxID) and enter it below along with your sender number.",
                'icon' => 'https://raw.githubusercontent.com/Nazmul64/assets/main/bkash.png',
                'min_deposit' => 50,
                'max_deposit' => 25000,
                'rate_per_bdt' => 10, // 1 BDT = 10 Coins
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Nagad Personal',
                'code' => 'nagad',
                'account_type' => 'Personal',
                'account_number' => '01800000000',
                'instructions' => "1. Go to your Nagad App or dial *167#\n2. Select 'Send Money'\n3. Enter Nagad Number: 01800000000\n4. Enter Amount and PIN\n5. Copy the 8-character Transaction ID (TxnID) and paste it below.",
                'icon' => 'https://raw.githubusercontent.com/Nazmul64/assets/main/nagad.png',
                'min_deposit' => 50,
                'max_deposit' => 25000,
                'rate_per_bdt' => 10, // 1 BDT = 10 Coins
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Rocket Personal',
                'code' => 'rocket',
                'account_type' => 'Personal',
                'account_number' => '01900000000-0',
                'instructions' => "1. Open Rocket App or dial *322#\n2. Select 'Send Money'\n3. Enter Rocket Number: 01900000000-0\n4. Complete the transfer and submit your Transaction ID.",
                'icon' => 'https://raw.githubusercontent.com/Nazmul64/assets/main/rocket.png',
                'min_deposit' => 50,
                'max_deposit' => 25000,
                'rate_per_bdt' => 10,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Upay Personal',
                'code' => 'upay',
                'account_type' => 'Personal',
                'account_number' => '01600000000',
                'instructions' => "1. Open Upay App or dial *268#\n2. Select 'Send Money'\n3. Enter Upay Number: 01600000000\n4. Submit the transaction ID.",
                'icon' => 'https://raw.githubusercontent.com/Nazmul64/assets/main/upay.png',
                'min_deposit' => 50,
                'max_deposit' => 25000,
                'rate_per_bdt' => 10,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code'], 'account_number' => $method['account_number']],
                $method
            );
        }
    }
}
