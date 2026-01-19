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
        private readonly string $usdtContract
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

        $json = HttpClient::get($url);
        $out = [];

        foreach ($json['result'] ?? [] as $tx) {
            if (strtolower($tx['to']) !== strtolower($this->address)) continue;

            $decimals = (int)$tx['tokenDecimal'];
            $amount = bcdiv($tx['value'], bcpow('10', (string)$decimals), $decimals);

            $out[] = new UsdtTransfer(
                $this->network,
                $tx['hash'],
                $tx['from'],
                $tx['to'],
                $amount,
                ((int)$tx['timeStamp']) * 1000
            );
        }

        return $out;
    }
}