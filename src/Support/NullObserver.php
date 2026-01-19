<?php
namespace OktayAydogan\UsdtMultichainScanner\Support;

final class NullObserver implements Observer
{
    public function info(string $message, array $context = []): void {}
    public function error(string $message, array $context = []): void {}
    public function metric(string $name, float|int $value, array $tags = []): void {}
}