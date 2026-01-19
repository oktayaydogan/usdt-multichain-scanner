<?php
namespace OktayAydogan\UsdtMultichainScanner\Factory;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\Scanners\EvmScanUsdtScanner;
use OktayAydogan\UsdtMultichainScanner\Scanners\Trc20UsdtScanner;
use RuntimeException;

final class ScannerFactory
{
    public static function make(string $network, array $cfg): ScannerInterface
    {
        $type = $cfg['type'] ?? $cfg['driver'] ?? 'evm';

        return match ($type) {
            'tron', 'trc20' => new Trc20UsdtScanner(
                network: $network,
                address: $cfg['address'],
                endpoint: $cfg['endpoint'] ?? 'https://apilist.tronscanapi.com',
                apiKey: $cfg['api_key'] ?? null,
                timeoutSeconds: (int)($cfg['timeout'] ?? 10)
            ),

            'evm' => new EvmScanUsdtScanner(
                network: $network,
                endpoint: $cfg['endpoint'] ?? self::defaultEvmEndpoint($network),
                address: $cfg['address'],
                apiKey: (string)($cfg['api_key'] ?? ''),
                usdtContract: $cfg['usdt_contract'] ?? self::defaultUsdtContract($network),
                timeoutSeconds: (int)($cfg['timeout'] ?? 10)
            ),

            default => throw new RuntimeException('Unknown network type: ' . (string)$type),
        };
    }

    private static function defaultEvmEndpoint(string $network): string
    {
        return match (true) {
            $network === 'bsc' || str_contains($network, 'bsc') || str_contains($network, 'bep') =>
                'https://api.bscscan.com/api',
            $network === 'polygon' || str_contains($network, 'polygon') =>
                'https://api.polygonscan.com/api',
            $network === 'arbitrum' || str_contains($network, 'arbitrum') =>
                'https://api.arbiscan.io/api',
            $network === 'optimism' || str_contains($network, 'optimism') =>
                'https://api-optimistic.etherscan.io/api',
            default =>
                'https://api.etherscan.io/api',
        };
    }

    private static function defaultUsdtContract(string $network): string
    {
        // BSC has its own USDT contract; most other EVM networks commonly use the ERC20 USDT contract address.
        return match (true) {
            $network === 'bsc' || str_contains($network, 'bsc') || str_contains($network, 'bep') =>
                '0x55d398326f99059fF775485246999027B3197955',
            default =>
                '0xdAC17F958D2ee523a2206206994597C13D831ec7',
        };
    }
}