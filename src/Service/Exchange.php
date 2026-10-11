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
}
