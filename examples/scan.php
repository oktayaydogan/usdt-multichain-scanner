<?php
require __DIR__ . '/../vendor/autoload.php';

use OktayAydogan\UsdtMultichainScanner\Registry\ScannerRegistry;

$config = [
    'bep20' => [
        'enabled' => true,
        'endpoint' => 'https://api.bscscan.com/api',
        'address' => '0xYourAddress',
        'api_key' => 'API_KEY',
        'usdt_contract' => '0x55d398326f99059fF775485246999027B3197955'
    ]
];

$registry = new ScannerRegistry($config);
$txs = $registry->fetchAll();

foreach ($txs as $tx) {
    echo "{$tx->network} {$tx->amount} USDT {$tx->txid}
";
}