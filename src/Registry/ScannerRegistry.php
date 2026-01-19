<?php
namespace OktayAydogan\UsdtMultichainScanner\Registry;

use OktayAydogan\UsdtMultichainScanner\Factory\ScannerFactory;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;

final class ScannerRegistry
{
    private array $scanners = [];

    public function __construct(array $config)
    {
        foreach ($config as $network => $cfg) {
            if (!($cfg['enabled'] ?? false)) continue;
            $this->scanners[] = ScannerFactory::make($network, $cfg);
        }
    }

    /** @return UsdtTransfer[] */
    public function fetchAll(): array
    {
        $all = [];
        foreach ($this->scanners as $scanner) {
            $all = array_merge($all, $scanner->fetch());
        }
        return $all;
    }
}