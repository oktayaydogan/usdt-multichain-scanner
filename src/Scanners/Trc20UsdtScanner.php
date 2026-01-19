<?php
namespace OktayAydogan\UsdtMultichainScanner\Scanners;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;
use OktayAydogan\UsdtMultichainScanner\Support\HttpClient;
use RuntimeException;

final class Trc20UsdtScanner implements ScannerInterface
{
    // USDT on Tron (TRC20)
    private const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
    private const DECIMALS = 6;

    public function __construct(
        private readonly string $network,
        private readonly string $address,
        private readonly string $endpoint,
        private readonly ?string $apiKey = null,
        private readonly int $timeoutSeconds = 10
    ) {}

    public function fetch(): array
    {
        // TronScan endpoint (documentation): /api/token_trc20/transfers
        // Params: contract_address, relatedAddress, confirm=true, start, limit
        $url = rtrim($this->endpoint, '/') . '/api/token_trc20/transfers?' . http_build_query([
            'contract_address' => self::USDT_CONTRACT,
            'relatedAddress'   => $this->address,
            'confirm'          => 'true',
            'start'            => 0,
            'limit'            => 50,
            'filterTokenValue' => 1,
        ]);

        $headers = [];
        if ($this->apiKey !== null && $this->apiKey !== '') {
            $headers[] = 'TRON-PRO-API-KEY: ' . $this->apiKey;
        }

        $json = HttpClient::get($url, $this->timeoutSeconds, $headers);

        // TronScan response commonly includes: token_transfers (array)
        $rows = $json['token_transfers'] ?? $json['data'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $tx) {
            if (!is_array($tx)) continue;

            $to = (string)($tx['to_address'] ?? $tx['to'] ?? '');
            if ($to !== $this->address) {
                continue;
            }

            $txid = (string)($tx['transaction_id'] ?? $tx['transactionId'] ?? '');
            $from = (string)($tx['from_address'] ?? $tx['from'] ?? '');
            $ts = (int)($tx['block_ts'] ?? $tx['block_timestamp'] ?? $tx['timestamp'] ?? 0);

            // amount fields vary; handle common ones
            $raw = (string)($tx['quant'] ?? $tx['value'] ?? $tx['amount'] ?? $tx['amount_str'] ?? '0');

            // Some responses include tokenInfo.tokenDecimal
            $decimals = (int)($tx['tokenInfo']['tokenDecimal'] ?? self::DECIMALS);
            if ($decimals <= 0) $decimals = self::DECIMALS;

            $amount = bcdiv($raw, bcpow('10', (string)$decimals), $decimals);

            $out[] = new UsdtTransfer(
                $this->network,
                $txid,
                $from,
                $to,
                $amount,
                $ts
            );
        }

        return $out;
    }
}