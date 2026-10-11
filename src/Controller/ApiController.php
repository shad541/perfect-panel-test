<?php

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\Exchange;
use App\Service\RatesProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController
{
    public function __construct(
        private RatesProvider $ratesProvider,
        private Exchange $exchange,
    ) {
    }

    #[Route('/api/v1')]
    public function index(Request $request): JsonResponse
    {
        $method = $request->query->get('method');

        if ($method === 'rates' && $request->isMethod('GET')) {
            $data = $this->rates($request);
        } elseif ($method === 'convert' && $request->isMethod('POST')) {
            $data = $this->convert($request);
        } else {
            throw new ApiException('Unknown method', 404);
        }

        return new JsonResponse(['status' => 'success', 'code' => 200, 'data' => $data]);
    }

    private function rates(Request $request): array
    {
        $currency = $request->query->get('currency');
        if ($currency && !preg_match('/^[A-Za-z0-9,]+$/', $currency)) {
            throw new ApiException('Invalid currency');
        }

        $currencies = $currency ? explode(',', $currency) : [];

        $rates = $this->ratesProvider->getRates();

        return $this->exchange->rates($rates, $currencies);
    }

    private function convert(Request $request): array
    {
        $params = $request->getPayload();
        $from = strtoupper($params->get('currency_from', ''));
        $to = strtoupper($params->get('currency_to', ''));
        $value = $params->get('value');

        if (!preg_match('/^[A-Z0-9]+$/', $from) || !preg_match('/^[A-Z0-9]+$/', $to) || !preg_match('/^\d+(\.\d+)?$/', (string) $value)) {
            throw new ApiException('Invalid parameters');
        }

        $rates = $this->ratesProvider->getRates();
        $result = $this->exchange->convert($rates, $from, $to, $value);

        return [
            'currency_from' => $from,
            'currency_to' => $to,
            'value' => (float) $value,
            'converted_value' => $result['converted_value'],
            'rate' => $result['rate'],
        ];
    }
}
