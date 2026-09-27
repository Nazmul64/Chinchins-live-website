<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== 1. TESTING GET /api/ranks/leaderboard ===" . PHP_EOL;
$req = \Illuminate\Http\Request::create('/api/ranks/leaderboard', 'GET', ['category' => 'rich', 'period' => 'daily']);
$ctrl = new \App\Http\Controllers\Api\LeaderboardApiController();
$res = $ctrl->getLeaderboard($req);
$data = json_decode($res->getContent(), true);

echo "Status: " . ($data['status'] ? 'SUCCESS' : 'FAILED') . PHP_EOL;
echo "Theme BG Image: " . ($data['theme']['background_image_url'] ?? 'none') . PHP_EOL;
echo "Total Ranked Users: " . count($data['rankings'] ?? []) . PHP_EOL;
foreach ($data['rankings'] ?? [] as $r) {
    echo " -> Rank #{$r['rank']}: {$r['name']} ({$r['country_flag']}) | Consume: {$r['consume_formatted']} | Badge: " . ($r['badge_icon_url'] ?: 'No Badge') . PHP_EOL;
}

echo PHP_EOL . "=== 2. TESTING GET /api/app/rank-badges-config ===" . PHP_EOL;
$configResp = $app->handle(\Illuminate\Http\Request::create('/api/app/rank-badges-config', 'GET'));
$configData = json_decode($configResp->getContent(), true);
echo "Config Status: " . ($configData['status'] ? 'SUCCESS' : 'FAILED') . PHP_EOL;
echo "Config Theme BG: " . ($configData['theme']['background_image_url'] ?? 'none') . PHP_EOL;
echo "Daily Badges Count: " . count($configData['data']['daily'] ?? []) . PHP_EOL;
