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

    public function testRatesForCurrencies(): void
    {
        $this->mockRates();

        $data = $this->request('GET', '/api/v1?method=rates&currency=BTC,doge')['data'];

        $this->assertSame(['DOGE' => 0.0845036099, 'BTC' => 81217.994234948], $data);
    }

    public function testConvert(): void
    {
        $this->mockRates();

        $this->assertSame([
            'status' => 'success',
            'code' => 200,
            'data' => [
                'currency_from' => 'BTC',
                'currency_to' => 'USD',
                'value' => 1,
                'converted_value' => 81217.99,
                'rate' => 81217.994234948,
            ],
        ], $this->request('POST', '/api/v1?method=convert', [
            'currency_from' => 'BTC',
            'currency_to' => 'USD',
            'value' => '1.00',
        ]));
    }

    public function testConvertMinimumValue(): void
    {
        $this->mockRates();
        $this->request('POST', '/api/v1?method=convert', [
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => '0.001',
        ]);

        $this->assertError(400, 'Minimum value is 0.01');
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

    private function assertError(int $code, string $message): void
    {
        $this->assertResponseStatusCodeSame($code);
        $this->assertSame([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ], json_decode($this->client->getResponse()->getContent(), true));
    }
}
