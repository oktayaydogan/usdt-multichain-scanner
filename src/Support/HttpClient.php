<?php
namespace OktayAydogan\UsdtMultichainScanner\Support;

use RuntimeException;

final class HttpClient
{
    /**
     * @param string[] $headers
     */
    public static function get(string $url, int $timeout = 10, array $headers = []): array
    {
        $headers = array_values(array_filter($headers, fn($h) => is_string($h) && $h !== ''));
        if (!in_array('Accept: application/json', $headers, true)) {
            $headers[] = 'Accept: application/json';
        }

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => $timeout,
                'header'  => $headers,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            throw new RuntimeException("HTTP request failed: {$url}");
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            throw new RuntimeException("Invalid JSON from {$url}");
        }

        return $json;
    }
}