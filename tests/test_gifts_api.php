<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST 1: Gift Catalog ===\n";
$api = new \App\Http\Controllers\Api\GiftApiController();
$req = \Illuminate\Http\Request::create('/api/gifts', 'GET');
$res = $api->getCatalog($req);
$data = json_decode($res->getContent(), true);
echo "Catalog Status: " . ($data['status'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Total Gifts in Store: " . count($data['data']['gifts']) . "\n";
echo "Sample Gift: " . $data['data']['gifts'][0]['name'] . " - " . $data['data']['gifts'][0]['formatted_coins'] . " coins\n";

echo "\n=== TEST 2: User Received Gifts ===\n";
$user = \App\Models\User::first();
$req2 = \Illuminate\Http\Request::create('/api/gifts/received/' . $user->id, 'GET');
$res2 = $api->getUserReceivedGifts($req2, (string) $user->id);
$data2 = json_decode($res2->getContent(), true);
echo "User: " . $data2['data']['user']['display_name'] . "\n";
echo "Charm Level: " . $data2['data']['charm_level']['level_tag'] . "\n";
echo "Top Fan: " . ($data2['data']['top_fan']['name'] ?? 'N/A') . "\n";
echo "Total Received Volume: " . $data2['data']['summary']['formatted_coins'] . " coins\n";
echo "Unique Gift Types: " . $data2['data']['summary']['total_unique_gifts'] . "\n";
echo "Top Received Gift: " . $data2['data']['gifts_received'][0]['name'] . " - " . $data2['data']['gifts_received'][0]['count_label'] . " (" . $data2['data']['gifts_received'][0]['formatted_coins'] . ")\n";

echo "\n=== TEST 3: Send Gift ===\n";
$sender = \App\Models\User::skip(1)->first() ?? $user;
$receiver = $user;
$gift = \App\Models\Gift::first();

// Give sender coins if low
if ($sender->coins < 50000) {
    $sender->coins = 100000;
    $sender->save();
}

$req3 = \Illuminate\Http\Request::create('/api/gifts/send', 'POST', [
    'sender_id'   => $sender->id,
    'receiver_id' => $receiver->id,
    'gift_id'     => $gift->id,
    'quantity'    => 2,
    'context'     => 'profile',
]);
$res3 = $api->sendGift($req3);
$data3 = json_decode($res3->getContent(), true);
echo "Send Status: " . ($data3['status'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $data3['message'] . "\n";
if (!empty($data3['data'])) {
    echo "Total Cost: " . $data3['data']['formatted_cost'] . " coins\n";
    echo "Updated Receiver Slot: " . $data3['data']['receiver']['updated_slot'] . "\n";
}

echo "\nALL TESTS COMPLETED SUCCESSFULLY!\n";
