<?php
namespace OktayAydogan\UsdtMultichainScanner\Factory;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\Scanners\EvmScanUsdtScanner;
use OktayAydogan\UsdtMultichainScanner\Scanners\Trc20UsdtScanner;

final class ScannerFactory
{
    public static function make(string $network, array $cfg): ScannerInterface
    {
        $driver = $cfg['driver'] ?? 'evm';

        return match ($driver) {
            'trc20' => new Trc20UsdtScanner(
                network: $network,
                address: $cfg['address'],
                endpoint: $cfg['endpoint'] ?? 'https://api.trongrid.io/v1'
            ),

            'evm' => new EvmScanUsdtScanner(
                network: $network,
                endpoint: $cfg['endpoint'] ?? self::defaultEvmEndpoint($network),
                address: $cfg['address'],
                apiKey: $cfg['api_key'] ?? '',
                usdtContract: $cfg['usdt_contract'] ?? self::defaultUsdtContract($network)
            ),

            default => throw new \RuntimeException('Unknown driver')
        };
    }

    private static function defaultEvmEndpoint(string $network): string
    {
        return match (true) {
            str_contains($network, 'bep') => 'https://api.bscscan.com/api',
            str_contains($network, 'polygon') => 'https://api.polygonscan.com/api',
            str_contains($network, 'arbitrum') => 'https://api.arbiscan.io/api',
            default => 'https://api.etherscan.io/api',
        };
    }

    private static function defaultUsdtContract(string $network): string
    {
        return match (true) {
            str_contains($network, 'bep') =>
                '0x55d398326f99059fF775485246999027B3197955',
            default =>
                '0xdAC17F958D2ee523a2206206994597C13D831ec7',
        };
    }
}