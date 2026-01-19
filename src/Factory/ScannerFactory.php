<?php
namespace OktayAydogan\UsdtMultichainScanner\Factory;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\Scanners\EvmScanUsdtScanner;

final class ScannerFactory
{
    public static function make(string $network, array $cfg): ScannerInterface
    {
        return new EvmScanUsdtScanner(
            $network,
            $cfg['endpoint'],
            $cfg['address'],
            $cfg['api_key'],
            $cfg['usdt_contract']
        );
    }
}