<?php
// Simple worker example (cron/queue compatible)

require __DIR__ . '/../vendor/autoload.php';

use OktayAydogan\UsdtMultichainScanner\Registry\ScannerRegistry;

$config = require __DIR__ . '/../config/usdt.php';

$registry = new ScannerRegistry($config);
$transfers = $registry->fetchAll();

foreach ($transfers as $tx) {
    // enqueue job or process async
    // e.g. push to Redis / SQS / DB table
    echo json_encode([
        'network' => $tx->network,
        'txid' => $tx->txid,
        'amount' => $tx->amount,
    ]) . PHP_EOL;
}