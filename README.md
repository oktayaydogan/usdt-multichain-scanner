# USDT Multichain Scanner

Multi-network USDT incoming payment scanner.

## Supported Networks
- Ethereum (ERC20)
- BNB Smart Chain (BEP20)
- Polygon / Arbitrum / Optimism (ERC20-compatible)

## Install
composer require oktayaydogan/usdt-multichain-scanner

## Usage
Configure enabled networks and call ScannerRegistry.

This package is **read-only**:
- No private keys
- No wallets
- No signing

You receive normalized USDT transactions and decide how to credit customers.