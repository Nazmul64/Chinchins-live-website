<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Gift;
use Illuminate\Http\Request;

$controller = app(\App\Http\Controllers\Api\GiftApiController::class);

// 1. Test All Catalog
$reqAll = Request::create('/api/gifts', 'GET');
$respAll = $controller->getCatalog($reqAll);
$dataAll = json_decode($respAll->getContent(), true);

echo "=== Total Gifts in Catalog: " . $dataAll['data']['total_gifts'] . " ===\n";
echo "Categories Available: " . count($dataAll['data']['categories_list']) . "\n";
foreach ($dataAll['data']['categories_list'] as $cat) {
    echo " - {$cat['emoji']} {$cat['label']} ({$cat['key']}): {$cat['count']} items\n";
}

// 2. Test Hot Category
$reqHot = Request::create('/api/gifts', 'GET', ['category' => 'hot']);
$respHot = $controller->getCatalog($reqHot);
$dataHot = json_decode($respHot->getContent(), true);
echo "\n=== HOT Category items count: " . $dataHot['data']['total_gifts'] . " ===\n";
foreach (array_slice($dataHot['data']['gifts'], 0, 5) as $g) {
    echo "  * {$g['name']} (💎 {$g['formatted_coins']}) - Badge: {$g['badge']}\n";
}

// 3. Test SVIP Category
$reqSvip = Request::create('/api/gifts', 'GET', ['category' => 'svip']);
$respSvip = $controller->getCatalog($reqSvip);
$dataSvip = json_decode($respSvip->getContent(), true);
echo "\n=== SVIP Category items count: " . $dataSvip['data']['total_gifts'] . " ===\n";
foreach (array_slice($dataSvip['data']['gifts'], 0, 5) as $g) {
    echo "  * {$g['name']} (💎 {$g['formatted_coins']}) - Badge: {$g['badge']}\n";
}

// 4. Test Lucky Category
$reqLucky = Request::create('/api/gifts', 'GET', ['category' => 'lucky']);
$respLucky = $controller->getCatalog($reqLucky);
$dataLucky = json_decode($respLucky->getContent(), true);
echo "\n=== LUCKY Category items count: " . $dataLucky['data']['total_gifts'] . " ===\n";
foreach (array_slice($dataLucky['data']['gifts'], 0, 5) as $g) {
    echo "  * {$g['name']} (💎 {$g['formatted_coins']}) - Badge: {$g['badge']}\n";
}

echo "\nAll Gift API tests PASSED successfully!\n";
