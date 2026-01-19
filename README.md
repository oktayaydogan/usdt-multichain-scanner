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


---

## Cursor / Since Support

You can limit scans to **new transactions only** using `since`.

- EVM: `since` = unix timestamp (seconds)
- Tron: `since` = unix timestamp (milliseconds)

Example:

```php
'bsc' => [
  'enabled' => true,
  'type' => 'evm',
  'address' => '0x...',
  'api_key' => 'BSCSCAN_KEY',
  'since' => 1710000000,
],
```

Store the latest processed timestamp and pass it back on the next run.

---

## Retry & Backoff

HTTP requests automatically retry on failure.

Defaults:
- retries: 2
- backoff: exponential (300ms * attempt)

Safe for rate-limited APIs.

---

## Async / Worker Usage

See `examples/worker.php` for a cron/queue-friendly pattern.

---


---

## Advanced Retry & Backoff Configuration

You can fine-tune retry behavior per network.

```php
'bsc' => [
  'enabled' => true,
  'type' => 'evm',
  'address' => '0x...',
  'api_key' => 'BSCSCAN_KEY',
  'retry' => [
    'retries' => 3,
    'base_backoff_ms' => 500,
  ],
],
```

Features:
- Exponential backoff
- Jitter (prevents thundering herd)
- Retries on 429 / 5xx
- Separate config per network

---


---

## Observability (Logging & Metrics)

The scanner supports pluggable observability via an `Observer` interface.

You can hook:
- Logs (PSR-3 compatible adapters)
- Metrics (Prometheus / Datadog / Cloud Monitoring)

### Available Signals
- `http.request`
- `http.error`
- `http.latency_ms`

### Custom Observer Example

```php
use OktayAydogan\UsdtMultichainScanner\Support\Observer;

class MyObserver implements Observer {
  public function info(string $message, array $context = []): void {
    error_log($message . json_encode($context));
  }
  public function error(string $message, array $context = []): void {
    error_log('[ERROR] ' . $message . json_encode($context));
  }
  public function metric(string $name, float|int $value, array $tags = []): void {
    // push to metrics backend
  }
}
```

---

## Unit Tests

This package includes PHPUnit test scaffolding.

Run tests:

```bash
composer require --dev phpunit/phpunit
vendor/bin/phpunit
```

Tests cover:
- Factory creation
- Config parsing
- Scanner instantiation

---
