<?php

namespace App\Tests;

use App\Service\Exchange;
use PHPUnit\Framework\TestCase;

class ExchangeTest extends TestCase
{
    public function testConvertUsdToBtc(): void
    {
        $result = (new Exchange())->convert(['BTC' => 82875.5043213755], 'USD', 'BTC', 0.01);

        $this->assertSame(0.0000001182, $result['converted_value']);
    }

    public function testConvertBtcToUsdRoundsToCents(): void
    {
        $result = (new Exchange())->convert(['BTC' => 82875.5043213755], 'BTC', 'USD', 1);

        $this->assertSame(81217.99, $result['converted_value']);
    }

    public function testConvertBtcToEth(): void
    {
        $result = (new Exchange())->convert(['BTC' => 82875.5043213755, 'ETH' => 2496.3478560664], 'BTC', 'ETH', 1);

        $this->assertSame(32.5347262953, $result['converted_value']);
    }
}
