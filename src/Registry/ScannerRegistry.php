<?php
namespace OktayAydogan\UsdtMultichainScanner\Registry;

use OktayAydogan\UsdtMultichainScanner\Factory\ScannerFactory;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;

final class ScannerRegistry
{
    private array $scanners = [];

    /**
     * Expected config:
     * [
     *   'networks' => [
     *     'ethereum' => ['enabled'=>true,'type'=>'evm','address'=>'0x...','api_key'=>'...'],
     *     'tron'     => ['enabled'=>true,'type'=>'tron','address'=>'T...','api_key'=>'...'],
     *   ]
     * ]
     */
    public function __construct(array $config)
    {
        $networks = $config['networks'] ?? $config; // backwards-compatible fallback
        foreach ($networks as $network => $cfg) {
            if (!is_array($cfg)) continue;
            if (!($cfg['enabled'] ?? false)) continue;
            $this->scanners[] = ScannerFactory::make((string)$network, $cfg);
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