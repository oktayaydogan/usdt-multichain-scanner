<?php
namespace OktayAydogan\UsdtMultichainScanner\Scanners;

use OktayAydogan\UsdtMultichainScanner\Contracts\ScannerInterface;
use OktayAydogan\UsdtMultichainScanner\DTO\UsdtTransfer;
use OktayAydogan\UsdtMultichainScanner\Support\HttpClient;

final class EvmScanUsdtScanner implements ScannerInterface
{
    public function __construct(
        private readonly string $network,
        private readonly string $primaryEndpoint,
        private readonly string $address,
        private readonly string $apiKey,
        private readonly string $usdtContract,
        private readonly int $timeoutSeconds = 10,
        private readonly ?string $fallbackEndpoint = null,
        private readonly ?int $sinceTs = null,
        private readonly int $retries = 2,
        private readonly int $baseBackoffMs = 300
    ) {}

    public function fetch(): array
    {
        try {
            return $this->fetchFrom($this->primaryEndpoint);
        } catch (\Throwable $e) {
            if ($this->fallbackEndpoint) {
                return $this->fetchFrom($this->fallbackEndpoint);
            }
            throw $e;
        }
    }

    private function fetchFrom(string $endpoint): array
    {
        $url = $endpoint . '?' . http_build_query([
            'module' => 'account',
            'action' => 'tokentx',
            'contractaddress' => $this->usdtContract,
            'address' => $this->address,
            'sort' => 'desc',
            'apikey' => $this->apiKey,
        ]);

        $json = HttpClient::get($url, $this->timeoutSeconds, [], $this->retries, $this->baseBackoffMs);
        $out = [];

        foreach ($json['result'] ?? [] as $tx) {
            if (!is_array($tx)) continue;
            if (strtolower((string)($tx['to'] ?? '')) !== strtolower($this->address)) continue;

            $ts = (int)($tx['timeStamp'] ?? 0);
            if ($this->sinceTs !== null && $ts <= $this->sinceTs) continue;

            $decimals = (int)($tx['tokenDecimal'] ?? 6);
            $value = (string)($tx['value'] ?? '0');
            $amount = bcdiv($value, bcpow('10', (string)$decimals), $decimals);

            $out[] = new UsdtTransfer(
                $this->network,
                (string)($tx['hash'] ?? ''),
                (string)($tx['from'] ?? ''),
                (string)($tx['to'] ?? ''),
                $amount,
                $ts * 1000
            );
        }

        return $out;
    }
}