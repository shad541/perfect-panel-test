<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRates(): void
    {
        $this->mockRates();

        $this->assertSame([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'DOGE' => 0.0845036099,
                'ETH' => 2446.4208989451,
                'BTC' => 81217.994234948,
            ],
        ], $this->request('GET', '/api/v1?method=rates'));
    }

    private function mockRates(): void
    {
        $response = new JsonMockResponse([
            ['symbol' => 'BTC', 'quotes' => ['USD' => ['price' => 82875.5043213755]]],
            ['symbol' => 'ETH', 'quotes' => ['USD' => ['price' => 2496.3478560664]]],
            ['symbol' => 'DOGE', 'quotes' => ['USD' => ['price' => 0.0862281733352548]]],
        ]);

        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient($response));
    }

    private function request(string $method, string $uri, array $params = []): array
    {
        $this->client->request($method, $uri, $params, server: [
            'HTTP_AUTHORIZATION' => 'Bearer '.$_ENV['API_TOKEN'],
        ]);

        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
