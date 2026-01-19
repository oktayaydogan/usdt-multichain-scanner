# USDT Multichain Scanner

Production-ready, read-only USDT incoming payment scanner for **multiple blockchains**.

This library normalizes USDT transfers across chains and lets **you** decide
how to credit customers.

---

## Supported Networks

| Network | Driver |
|-------|--------|
| Ethereum (ERC20) | evm |
| BNB Smart Chain (BEP20) | evm |
| Polygon | evm |
| Arbitrum | evm |
| Optimism | evm |
| Tron (TRC20) | trc20 |

---

## Installation

```bash
composer require oktayaydogan/usdt-multichain-scanner
```

---

## Configuration Philosophy (Important)

- Config is **network-based**, not wallet-based
- Only **enabled networks** are scanned
- API URLs are **optional**
- Sensible **defaults are applied automatically**
- No private keys, ever

---

## Minimal Configuration (Recommended)

```php
$config = [
    'bep20' => [
        'enabled' => true,
        'address' => '0xYourBscAddress',
        'api_key' => 'BSCSCAN_API_KEY',
    ],

    'trc20' => [
        'enabled' => true,
        'driver' => 'trc20',
        'address' => 'TYourTronAddress',
    ],
];
```

This works because:
- BSC defaults to **BscScan**
- TRC20 defaults to **TronGrid public API**
- USDT contract addresses are auto-selected

---

## Full Configuration (All Options)

```php
'erc20_eth' => [
    'enabled' => true,
    'driver' => 'evm',
    'endpoint' => 'https://api.etherscan.io/api',
    'address' => '0xEthAddress',
    'api_key' => 'ETHERSCAN_KEY',
    'usdt_contract' => '0xdAC17F958D2ee523a2206206994597C13D831ec7',
],

'bep20' => [
    'enabled' => true,
    'driver' => 'evm',
    'endpoint' => 'https://api.bscscan.com/api',
    'address' => '0xBscAddress',
    'api_key' => 'BSCSCAN_KEY',
],

'trc20' => [
    'enabled' => true,
    'driver' => 'trc20',
    'endpoint' => 'https://api.trongrid.io/v1',
    'address' => 'TTronAddress',
],
```

---

## Usage

```php
use OktayAydogan\UsdtMultichainScanner\Registry\ScannerRegistry;

$registry = new ScannerRegistry($config);
$transfers = $registry->fetchAll();

foreach ($transfers as $tx) {
    // idempotent check (txid)
    // customer matching
    // credit balance
}
```

---

## Security Model

- 🔒 Read-only APIs
- 🔒 No private keys
- 🔒 No signing
- 🔒 Safe for cron & workers

---

## Versioning Plan

- v1.0.x → EVM support
- v1.1.x → TRC20 support
- v1.2.x → Cursor / since optimization
- v1.3.x → Token-agnostic scanners

---

## License
MIT