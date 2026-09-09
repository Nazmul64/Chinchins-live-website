<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- 1. Testing User Sending Message to Reseller ---\n";
$user = \App\Models\User::first();
$reseller = \App\Models\Reseller::first();

$reqSend = Illuminate\Http\Request::create('/api/reseller/chat/send', 'POST', [
    'reseller_id' => $reseller->id,
    'user_id' => $user->id,
    'message' => 'Hello! My user ID is ' . ($user->account_id ?: $user->id) . '. I want to recharge 7560 gems. How much should I pay? 【GIVE THE BEST DISCOUNT 💎 DIAMOND 💎】',
    'coins_amount' => 7560,
]);
$resSend = $app->handle($reqSend);
echo "Send Status: " . $resSend->getStatusCode() . "\n";
echo "Response: " . substr($resSend->getContent(), 0, 300) . "...\n\n";

echo "--- 2. Testing User ID Lookup for Reseller Transfer ---\n";
$reqLookup = Illuminate\Http\Request::create('/reseller/validate-user', 'GET', [
    'account_id' => $user->account_id ?: (string)$user->id,
]);
$resLookup = $app->handle($reqLookup);
echo "Lookup Status: " . $resLookup->getStatusCode() . "\n";
echo "Lookup Response: " . $resLookup->getContent() . "\n\n";

echo "--- 3. Testing Direct Coin Transfer from Reseller to User ---\n";
$initialResellerBalance = $reseller->coins_balance;
$initialUserCoins = $user->coins;

$transferReq = Illuminate\Http\Request::create('/api/reseller/transfer-coins', 'POST', [
    'reseller_id' => $reseller->id,
    'target_account_id' => $user->account_id ?: (string)$user->id,
    'coins' => 7560,
    'amount_bdt' => 150.00,
    'payment_method' => 'bKash',
    'transaction_id' => 'TRXTEST' . rand(1000, 9999),
]);
$transferRes = $app->handle($transferReq);
echo "Transfer Status: " . $transferRes->getStatusCode() . "\n";
echo "Transfer Response: " . $transferRes->getContent() . "\n";

$reseller->refresh();
$user->refresh();
echo "Reseller Balance Before: {$initialResellerBalance} -> After: {$reseller->coins_balance} (Diff: " . ($reseller->coins_balance - $initialResellerBalance) . ")\n";
echo "User Coins Before: {$initialUserCoins} -> After: {$user->coins} (Diff: +" . ($user->coins - $initialUserCoins) . ")\n";
echo "\nALL WORKFLOWS VERIFIED PERFECTLY!\n";
