# USDT Multichain Scanner

Production-ready, **read-only** USDT incoming payment scanner for multiple blockchains.

This library normalizes USDT transfers across chains and returns a single DTO format.
You decide how to match customers and credit balances (idempotent by `txid`).

---

## Supported Networks

- EVM (type: `evm`)
  - Ethereum
  - BNB Smart Chain (BSC)
  - Polygon
  - Arbitrum
  - Optimism
- Tron (type: `tron`)

---

## Install

```bash
composer require oktayaydogan/usdt-multichain-scanner
```

---

## Configuration (Unified Model)

All networks use the same config shape:

```php
return [
  'networks' => [
    '<network>' => [
      'enabled' => true|false,
      'type' => 'evm'|'tron',
      'address' => '...',
      // optional:
      'api_key' => '...',
      'endpoint' => '...',
      'usdt_contract' => '...',
      'timeout' => 10,
    ],
  ],
];
```

### Defaults (if you omit fields)

**EVM defaults**
- `endpoint` is selected by network key:
  - `bsc` → BscScan
  - `polygon` → PolygonScan
  - `arbitrum` → ArbiScan
  - `optimism` → Optimism Etherscan
  - anything else → Etherscan
- `usdt_contract` defaults:
  - `bsc` → BSC USDT contract
  - others → ERC20 USDT contract

**Tron defaults**
- `endpoint` defaults to TronScan API base: `https://apilist.tronscanapi.com`
- TRC20 USDT contract is built-in.
- TronScan expects an API Key in headers as `TRON-PRO-API-KEY` (recommended / may be required depending on endpoint and policy).

---

## Minimal Config Examples

### BSC + Tron

```php
return [
  'networks' => [
    'bsc' => [
      'enabled' => true,
      'type' => 'evm',
      'address' => '0xYourBscAddress',
      'api_key' => 'BSCSCAN_API_KEY',
    ],
    'tron' => [
      'enabled' => true,
      'type' => 'tron',
      'address' => 'TYourTronAddress',
      'api_key' => 'TRONSCAN_API_KEY', // sent as TRON-PRO-API-KEY
    ],
  ],
];
```

### Ethereum only

```php
return [
  'networks' => [
    'ethereum' => [
      'enabled' => true,
      'type' => 'evm',
      'address' => '0xYourEthAddress',
      'api_key' => 'ETHERSCAN_API_KEY',
    ],
  ],
];
```

---

## Usage

```php
use OktayAydogan\UsdtMultichainScanner\Registry\ScannerRegistry;

$config = require __DIR__ . '/config/usdt.php';

$registry = new ScannerRegistry($config);
$transfers = $registry->fetchAll();

foreach ($transfers as $tx) {
  // 1) idempotent check: UNIQUE(network, txid)
  // 2) customer match (your whitelist)
  // 3) credit balance
}
```

---

## Security Model

- Read-only API usage
- No private keys
- No signing / sweeping
- Safe for cron & workers

---

## License
MIT

---

## Fallback Providers

The scanner automatically falls back if the primary provider fails.

### EVM
- Primary: network-specific Scan API (Etherscan, BscScan, etc.)
- Fallback: optional secondary endpoint via `fallback_endpoint`

### Tron
- Primary: TronScan API
- Fallback: TronGrid public API

Example:

```php
'tron' => [
  'enabled' => true,
  'type' => 'tron',
  'address' => 'T...',
  'api_key' => 'TRONSCAN_KEY',
  // optional overrides
  'endpoint' => 'https://apilist.tronscanapi.com',
  'fallback_endpoint' => 'https://api.trongrid.io/v1',
],
```
