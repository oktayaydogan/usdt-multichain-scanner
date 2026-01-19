<?php
namespace OktayAydogan\UsdtMultichainScanner\Support;

use RuntimeException;

final class HttpClient
{
    public static function get(string $url, int $timeout = 10): array
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeout,
                'header' => ['Accept: application/json'],
            ]
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