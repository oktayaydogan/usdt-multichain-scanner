<?php
namespace OktayAydogan\UsdtMultichainScanner\Factory;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\Scanners\EvmScanUsdtScanner;
use OktayAydogan\UsdtMultichainScanner\Scanners\Trc20UsdtScanner;

final class ScannerFactory
{
    public static function make(string $network, array $cfg): ScannerInterface
    {
        $type = $cfg['type'] ?? 'evm';
        $retries = (int)($cfg['retry']['retries'] ?? $cfg['retries'] ?? 2);
        $baseBackoffMs = (int)($cfg['retry']['base_backoff_ms'] ?? $cfg['base_backoff_ms'] ?? 300);

        return match ($type) {
            'tron' => new Trc20UsdtScanner(
                network: $network,
                address: $cfg['address'],
                primaryEndpoint: $cfg['endpoint'] ?? 'https://apilist.tronscanapi.com',
                apiKey: $cfg['api_key'] ?? null,
                timeoutSeconds: (int)($cfg['timeout'] ?? 10),
                fallbackEndpoint: $cfg['fallback_endpoint'] ?? 'https://api.trongrid.io/v1',
                sinceMs: $cfg['since'] ?? null,
                retries: $retries,
                baseBackoffMs: $baseBackoffMs
            ),

            default => new EvmScanUsdtScanner(
                network: $network,
                primaryEndpoint: $cfg['endpoint'] ?? self::defaultEvmEndpoint($network),
                address: $cfg['address'],
                apiKey: $cfg['api_key'] ?? '',
                chainId: (int)($cfg['chain_id'] ?? self::defaultChainId($network)),
                usdtContract: $cfg['usdt_contract'] ?? self::defaultUsdtContract($network),
                timeoutSeconds: (int)($cfg['timeout'] ?? 10),
                fallbackEndpoint: $cfg['fallback_endpoint'] ?? null,
                sinceTs: $cfg['since'] ?? null,
                retries: $retries,
                baseBackoffMs: $baseBackoffMs
            ),
        };
    }

    private static function defaultEvmEndpoint(string $network): string
    {
        return match (true) {
            str_contains($network, 'bsc') || str_contains($network, 'bep') => 'https://api.bscscan.com/api',
            str_contains($network, 'polygon') => 'https://api.polygonscan.com/api',
            str_contains($network, 'arbitrum') => 'https://api.arbiscan.io/api',
            str_contains($network, 'optimism') => 'https://api-optimistic.etherscan.io/api',
            default => 'https://api.etherscan.io/api',
        };
    }

    private static function defaultChainId(string $network): int
    {
        return match (true) {
            str_contains($network, 'bsc') || str_contains($network, 'bep') => 56,
            str_contains($network, 'polygon') => 137,
            str_contains($network, 'arbitrum') => 42161,
            str_contains($network, 'optimism') => 10,
            default => 1,
        };
    }

    private static function defaultUsdtContract(string $network): string
    {
        return str_contains($network, 'bsc')
            ? '0x55d398326f99059fF775485246999027B3197955'
            : '0xdAC17F958D2ee523a2206206994597C13D831ec7';
    }
}