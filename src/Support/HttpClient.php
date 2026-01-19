<?php
namespace OktayAydogan\UsdtMultichainScanner\Support;

use RuntimeException;

final class HttpClient
{
    public static function get(
        string $url,
        int $timeout = 10,
        array $headers = [],
        int $retries = 2,
        int $baseBackoffMs = 300,
        int $maxBackoffMs = 5000,
        bool $jitter = true,
        array $retryOnStatus = [429, 500, 502, 503, 504],
        ?Observer $observer = null
    ): array {
        $observer ??= new NullObserver();

        $headers = array_values(array_filter($headers, fn($h) => is_string($h) && $h !== ''));
        if (!in_array('Accept: application/json', $headers, true)) {
            $headers[] = 'Accept: application/json';
        }

        $attempt = 0;
        $start = microtime(true);

        do {
            $attempt++;
            try {
                $observer->info('http.request', ['url' => $url, 'attempt' => $attempt]);

                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => $timeout,
                        'header' => $headers,
                        'ignore_errors' => true,
                    ],
                ]);

                $raw = @file_get_contents($url, false, $ctx);
                $status = self::extractStatus($http_response_header ?? []);

                if ($raw === false || ($status !== null && in_array($status, $retryOnStatus, true))) {
                    throw new RuntimeException("HTTP error {$status}");
                }

                $json = json_decode($raw, true);
                if (!is_array($json)) {
                    throw new RuntimeException("Invalid JSON");
                }

                $observer->metric('http.latency_ms', (int)((microtime(true) - $start) * 1000), [
                    'url' => $url
                ]);

                return $json;

            } catch (\Throwable $e) {
                $observer->error('http.error', [
                    'url' => $url,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt > $retries) {
                    throw $e;
                }

                $sleep = min($maxBackoffMs, $baseBackoffMs * (2 ** ($attempt - 1)));
                if ($jitter) {
                    $sleep = random_int(intdiv($sleep, 2), $sleep);
                }
                usleep($sleep * 1000);
            }
        } while (true);
    }

    private static function extractStatus(array $headers): ?int
    {
        foreach ($headers as $h) {
            if (preg_match('#HTTP/\S+\s+(\d{3})#', $h, $m)) {
                return (int)$m[1];
            }
        }
        return null;
    }
}