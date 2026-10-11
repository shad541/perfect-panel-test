<?php

namespace App\Service;

use App\Exception\ApiException;

class Exchange
{
    private const COMMISSION = 0.02;

    public function rates(array $rates, array $currencies = []): array
    {
        $result = [];
        foreach ($currencies ?: array_keys($rates) as $currency) {
            if (!isset($rates[$currency])) {
                throw new ApiException('Unknown currency: '.$currency);
            }
            $result[$currency] = round($rates[$currency] * (1 - self::COMMISSION), 10);
        }
        asort($result);

        return $result;
    }

    public function convert(array $rates, string $from, string $to, float $value): array
    {
        if ($value < 0.01) {
            throw new ApiException('Minimum value is 0.01');
        }

        $rate = $this->usdRate($rates, $from) / $this->usdRate($rates, $to) * (1 - self::COMMISSION);

        return [
            'converted_value' => round($value * $rate, $to === 'USD' ? 2 : 10),
            'rate' => round($rate, 10),
        ];
    }

    private function usdRate(array $rates, string $currency): float
    {
        if ($currency === 'USD') {
            return 1;
        }
        if (!isset($rates[$currency])) {
            throw new ApiException('Unknown currency: '.$currency);
        }

        return $rates[$currency];
    }
}
