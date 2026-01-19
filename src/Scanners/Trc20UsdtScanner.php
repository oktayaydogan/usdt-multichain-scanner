<?php
namespace OktayAydogan\UsdtMultichainScanner\Scanners;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;
use OktayAydogan\UsdtMultichainScanner\Support\HttpClient;

final class Trc20UsdtScanner implements ScannerInterface
{
    private const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
    private const DECIMALS = 6;

    public function __construct(
        private readonly string $network,
        private readonly string $address,
        private readonly string $endpoint
    ) {}

    public function fetch(): array
    {
        $url = rtrim($this->endpoint, '/') . "/accounts/{$this->address}/transactions/trc20?limit=50";
        $json = HttpClient::get($url);

        if (($json['success'] ?? false) !== true) {
            return [];
        }

        $out = [];
        foreach ($json['data'] as $tx) {
            if (($tx['token_info']['address'] ?? null) !== self::USDT_CONTRACT) continue;
            if (($tx['to'] ?? null) !== $this->address) continue;

            $amount = bcdiv($tx['value'], '1000000', self::DECIMALS);

            $out[] = new UsdtTransfer(
                $this->network,
                $tx['transaction_id'],
                $tx['from'],
                $tx['to'],
                $amount,
                (int)$tx['block_timestamp']
            );
        }

        return $out;
    }
}