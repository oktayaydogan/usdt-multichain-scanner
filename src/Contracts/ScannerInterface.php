<?php
namespace OktayAydogan\UsdtMultichainScanner\Contracts;

use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;

interface ScannerInterface
{
    /** @return UsdtTransfer[] */
    public function fetch(): array;
}