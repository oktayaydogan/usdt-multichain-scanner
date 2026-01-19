<?php
use PHPUnit\Framework\TestCase;
use OktayAydogan\UsdtMultichainScanner\Factory\ScannerFactory;

final class ScannerFactoryTest extends TestCase
{
    public function testCreatesEvmScanner(): void
    {
        $scanner = ScannerFactory::make('bsc', [
            'type' => 'evm',
            'address' => '0x123',
            'api_key' => 'key'
        ]);

        $this->assertNotNull($scanner);
    }

    public function testCreatesTronScanner(): void
    {
        $scanner = ScannerFactory::make('tron', [
            'type' => 'tron',
            'address' => 'T123'
        ]);

        $this->assertNotNull($scanner);
    }
}