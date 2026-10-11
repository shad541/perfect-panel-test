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

        if ($method === 'rates') {
            $data = $this->rates($request);
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
}
