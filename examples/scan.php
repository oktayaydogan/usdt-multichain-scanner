<?php
require __DIR__ . '/../vendor/autoload.php';

use OktayAydogan\UsdtMultichainScanner\Registry\ScannerRegistry;

$config = [
  'networks' => [
    'bsc' => [
      'enabled' => true,
      'type' => 'evm',
      'address' => '0xYourBscAddress',
      'api_key' => 'BSCSCAN_API_KEY',
    ],
    'tron' => [
      'enabled' => false,
      'type' => 'tron',
      'address' => 'TYourTronAddress',
      'api_key' => 'TRONSCAN_API_KEY',
    ],
  ],
];

$registry = new ScannerRegistry($config);
$txs = $registry->fetchAll();

foreach ($txs as $tx) {
  echo "{$tx->network} {$tx->amount} USDT {$tx->txid}\n";
}