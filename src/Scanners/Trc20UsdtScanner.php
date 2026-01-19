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
        private readonly string $primaryEndpoint,
        private readonly ?string $apiKey = null,
        private readonly int $timeoutSeconds = 10,
        private readonly ?string $fallbackEndpoint = null,
        private readonly ?int $sinceMs = null
    ) {}

    public function fetch(): array
    {
        try {
            return $this->fetchFromTronScan($this->primaryEndpoint);
        } catch (\Throwable $e) {
            if ($this->fallbackEndpoint) {
                return $this->fetchFromTronGrid($this->fallbackEndpoint);
            }
            throw $e;
        }
    }

    private function fetchFromTronScan(string $endpoint): array
    {
        $url = rtrim($endpoint, '/') . '/api/token_trc20/transfers?' . http_build_query([
            'contract_address' => self::USDT_CONTRACT,
            'relatedAddress'   => $this->address,
            'confirm'          => 'true',
            'start'            => 0,
            'limit'            => 50,
        ]);

        $headers = [];
        if ($this->apiKey) $headers[] = 'TRON-PRO-API-KEY: ' . $this->apiKey;

        $json = HttpClient::get($url, $this->timeoutSeconds, $headers);
        $rows = $json['token_transfers'] ?? [];

        return $this->mapRows($rows, 'to_address', 'from_address', 'block_ts', 'quant');
    }

    private function fetchFromTronGrid(string $endpoint): array
    {
        $url = rtrim($endpoint, '/') . "/accounts/{$this->address}/transactions/trc20?limit=50";
        $json = HttpClient::get($url, $this->timeoutSeconds);
        $rows = $json['data'] ?? [];

        return $this->mapRows($rows, 'to', 'from', 'block_timestamp', 'value');
    }

    private function mapRows(array $rows, string $toKey, string $fromKey, string $tsKey, string $valueKey): array
    {
        $out = [];
        foreach ($rows as $tx) {
            if (!is_array($tx)) continue;
            if (($tx[$toKey] ?? '') !== $this->address) continue;

            $ts = (int)($tx[$tsKey] ?? 0);
            if ($this->sinceMs !== null && $ts <= $this->sinceMs) continue;

            $raw = (string)($tx[$valueKey] ?? '0');
            $amount = bcdiv($raw, '1000000', self::DECIMALS);

            $out[] = new UsdtTransfer(
                $this->network,
                (string)($tx['transaction_id'] ?? $tx['transactionId'] ?? ''),
                (string)($tx[$fromKey] ?? ''),
                (string)($tx[$toKey] ?? ''),
                $amount,
                $ts
            );
        }
        return $out;
    }
}