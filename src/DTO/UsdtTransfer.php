<?php
namespace OktayAydogan\UsdtMultichainScanner\DTO;

final class UsdtTransfer
{
    public function __construct(
        public readonly string $network,
        public readonly string $txid,
        public readonly string $from,
        public readonly string $to,
        public readonly string $amount,
        public readonly int $timestampMs
    ) {}
}