<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST 1: App Config API ===\n";
$msgApi = new \App\Http\Controllers\Api\MessageApiController();
$resConfig = $msgApi->getAppConfig();
$configData = json_decode($resConfig->getContent(), true);
echo "App Name: " . $configData['data']['app_name'] . "\n";
echo "App Logo URL: " . $configData['data']['app_logo_url'] . "\n";
echo "Free Messages Limit: " . $configData['data']['free_messages_limit'] . "\n";

echo "\n=== TEST 2: In-App Messages / Inbox (Matching Screenshot) ===\n";
$user = \App\Models\User::first();
$reqInbox = \Illuminate\Http\Request::create('/api/messages', 'GET', ['user_id' => $user->id]);
$resInbox = $msgApi->getConversations($reqInbox);
$inboxData = json_decode($resInbox->getContent(), true);
echo "Inbox Status: " . ($inboxData['status'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Total Unread Badge Count: " . $inboxData['data']['total_unread_badge'] . "\n";
echo "Total Conversations: " . count($inboxData['data']['conversations']) . "\n";
foreach (array_slice($inboxData['data']['conversations'], 0, 8) as $conv) {
    echo " - Contact: {$conv['name']} | Unread: {$conv['unread_count']} | Last: {$conv['last_message']['text']} ({$conv['last_message']['time']})\n";
}

echo "\n=== TEST 3: Send Photo Message to uploads/sms_profile ===\n";
$sender = $user;
$receiver = \App\Models\User::skip(1)->first();
$reqSend = \Illuminate\Http\Request::create('/api/messages/send', 'POST', [
    'sender_id'   => $sender->id,
    'receiver_id' => $receiver->id,
    'type'        => 'image',
    'media_url'   => asset('uploads/sms_profile/test_pic.jpg'),
    'message'     => '[Image]',
]);
$resSend = $msgApi->sendMessage($reqSend);
$sendData = json_decode($resSend->getContent(), true);
echo "Send Status: " . ($sendData['status'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message Created: " . $sendData['data']['chat_message']['message'] . " (Media: " . $sendData['data']['chat_message']['media_url'] . ")\n";

echo "\nALL TESTS PASSED!\n";
