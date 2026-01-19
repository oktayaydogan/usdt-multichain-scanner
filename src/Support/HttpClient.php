<?php
namespace OktayAydogan\UsdtMultichainScanner\Support;

use RuntimeException;

final class HttpClient
{
    /**
     * @param string[] $headers
     */
    public static function get(
        string $url,
        int $timeout = 10,
        array $headers = [],
        int $retries = 2,
        int $backoffMs = 300
    ): array {
        $headers = array_values(array_filter($headers, fn($h) => is_string($h) && $h !== ''));
        if (!in_array('Accept: application/json', $headers, true)) {
            $headers[] = 'Accept: application/json';
        }

        $attempt = 0;
        do {
            $attempt++;
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'method'  => 'GET',
                        'timeout' => $timeout,
                        'header'  => $headers,
                    ],
                ]);

                $raw = @file_get_contents($url, false, $ctx);
                if ($raw === false) {
                    throw new RuntimeException("HTTP request failed");
                }

                $json = json_decode($raw, true);
                if (!is_array($json)) {
                    throw new RuntimeException("Invalid JSON");
                }

                return $json;
            } catch (\Throwable $e) {
                if ($attempt > $retries) {
                    throw $e;
                }
                usleep($backoffMs * 1000 * $attempt); // exponential-ish
            }
        } while (true);
    }
}