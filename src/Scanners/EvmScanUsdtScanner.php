<?php
namespace OktayAydogan\UsdtMultichainScanner\Scanners;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;
use OktayAydogan\UsdtMultichainScanner\Support\HttpClient;

final class EvmScanUsdtScanner implements ScannerInterface
{
    public function __construct(
        private readonly string $network,
        private readonly string $endpoint,
        private readonly string $address,
        private readonly string $apiKey,
        private readonly string $usdtContract,
        private readonly int $timeoutSeconds = 10
    ) {}

    public function fetch(): array
    {
        $url = $this->endpoint . '?' . http_build_query([
            'module' => 'account',
            'action' => 'tokentx',
            'contractaddress' => $this->usdtContract,
            'address' => $this->address,
            'sort' => 'desc',
            'apikey' => $this->apiKey,
        ]);

        $json = HttpClient::get($url, $this->timeoutSeconds);
        $out = [];

        foreach ($json['result'] ?? [] as $tx) {
            if (!is_array($tx)) continue;

            if (strtolower((string)($tx['to'] ?? '')) !== strtolower($this->address)) {
                continue;
            }

            $decimals = (int)($tx['tokenDecimal'] ?? 0);
            if ($decimals <= 0) {
                // fall back for USDT if API doesn't return tokenDecimal
                $decimals = 6;
            }

            $value = (string)($tx['value'] ?? '0');
            $amount = bcdiv($value, bcpow('10', (string)$decimals), $decimals);

            $out[] = new UsdtTransfer(
                $this->network,
                (string)($tx['hash'] ?? ''),
                (string)($tx['from'] ?? ''),
                (string)($tx['to'] ?? ''),
                $amount,
                ((int)($tx['timeStamp'] ?? 0)) * 1000
            );
        }

        return $out;
    }
}