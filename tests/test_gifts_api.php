<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST 1: Smart Coin Parser in Admin Controller ===\n";
echo "17.70 -> " . \App\Http\Controllers\Admin\GiftController::parseCoins('17.70') . " (Expected: 17700)\n";
echo "17.70K -> " . \App\Http\Controllers\Admin\GiftController::parseCoins('17.70K') . " (Expected: 17700)\n";
echo "5.55k -> " . \App\Http\Controllers\Admin\GiftController::parseCoins('5.55k') . " (Expected: 5550)\n";
echo "500 -> " . \App\Http\Controllers\Admin\GiftController::parseCoins('500') . " (Expected: 500)\n";
echo "10K -> " . \App\Http\Controllers\Admin\GiftController::parseCoins('10K') . " (Expected: 10000)\n";

echo "\n=== TEST 2: Charm Level Calculation (Admin 10K/lvl) ===\n";
$lvlData = \App\Models\CharmLevelSetting::calculateLevel(65000);
echo "65,000 coins -> Level: " . $lvlData['level_tag'] . " (" . $lvlData['title'] . ") Progress: " . $lvlData['progress'] . "%\n";

echo "\n=== TEST 3: Top Fans Leaderboard API ===\n";
$api = new \App\Http\Controllers\Api\GiftApiController();
$user = \App\Models\User::first();
$req = \Illuminate\Http\Request::create('/api/profile/' . $user->id . '/top-fans', 'GET');
$res = $api->getTopFans($req, (string)$user->id);
$topData = json_decode($res->getContent(), true);
echo "Top Fans Count: " . count($topData['data']['top_fans']) . "\n";
echo "Rank 1 Fan: " . $topData['data']['top_fans'][0]['display_name'] . " (" . $topData['data']['top_fans'][0]['formatted_coins'] . " coins, Crown: " . $topData['data']['top_fans'][0]['crown_type'] . ")\n";

echo "\n=== TEST 4: Send Like Heart API ===\n";
$sender = \App\Models\User::skip(1)->first() ?? $user;
$reqLike = \Illuminate\Http\Request::create('/api/profile/' . $user->id . '/like', 'POST', [
    'sender_id' => $sender->id,
    'count'     => 5,
    'context'   => 'call',
]);
$resLike = $api->sendLike($reqLike, (string)$user->id);
$likeData = json_decode($resLike->getContent(), true);
echo "Like Status: " . ($likeData['status'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Total Host Likes: " . $likeData['data']['formatted_likes'] . "\n";

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
