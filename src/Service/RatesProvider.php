<?php

namespace App\Service;

use App\Exception\ApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RatesProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(COINPAPRIKA_URL)%')]
        private string $url,
    ) {
    }

    public function getRates(): array
    {
        try {
            $tickers = $this->httpClient->request('GET', $this->url, ['timeout' => 5])->toArray();
        } catch (\Exception) {
            throw new ApiException('Rates service is unavailable', 503);
        }

        $rates = [];
        foreach ($tickers as $ticker) {
            $price = $ticker['quotes']['USD']['price'] ?? 0;
            $symbol = strtoupper($ticker['symbol']);
            if ($price > 0 && !isset($rates[$symbol])) {
                $rates[$symbol] = $price;
            }
        }

        return $rates;
    }
}
