<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services;

use App\Services\ConversionService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ConversionServiceTest extends TestCase
{
    private ConversionService $conversionService;
    private Client $mockClient;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->conversionService = new ConversionService($this->mockClient);
    }

    public function testGetExchangeRatesSuccess(): void
    {
        $currencies = 'USD-BRL';
        $responseBody = json_encode([
            'USD' => ['code' => 'USD', 'name' => 'Dólar Americano', 'high' => '5.20', 'low' => '5.00', 'varBid' => '0.10', 'bid' => '5.10'],
            'BRL' => ['code' => 'BRL', 'name' => 'Real', 'high' => '1.00', 'low' => '0.95', 'varBid' => '0.00', 'bid' => '1.00']
        ]);

        $this->mockClient
            ->method('request')
            ->with('GET', 'https://economia.awesomeapi.com.br/json/last/USD-BRL', ['verify' => false])
            ->willReturn(new Response(200, [], $responseBody));

        $result = $this->conversionService->getExchangeRates($currencies);

        $this->assertArrayHasKey('USD', $result);
        $this->assertEquals('Dólar Americano', $result['USD']['name']);
    }

    public function testGetExchangeRatesFailure(): void
    {
        $currencies = 'USD-BRL';

        $this->mockClient
            ->method('request')
            ->willThrowException(new \Exception('Erro ao buscar taxas de câmbio'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro ao buscar taxas de câmbio');

        $this->conversionService->getExchangeRates($currencies);
    }

    public function testCalcExchangeRates()
    {
        $request = (object)[
            'to_currency' => 'USD',
            'from_currency' => 'BRL'
        ];

        $this->mockClient
            ->method('request')
            ->willReturn(new Response(200, [], json_encode([
                'USDBRL' => ['bid' => '5.10'],
                'BRLUSD' => ['bid' => '0.20']
            ])));

        $result = $this->conversionService->calcExchangeRates($request);

        $this->assertEquals('5.10', $result);
    }

    public function testCalcFee(): void
    {
        $result = 100.00;
        $fee = 10;

        $total = $this->conversionService->calcFee($result, $fee);

        $this->assertEquals(110.00, $total);
    }

    public function testCalcfeeWithoutPaymentTypeLessThan3000(): void
    {
        $request = (object)[
            'amount' => 2500
        ];
        $resultExchangeRates = 100.00;

        $result = $this->conversionService->calcfeeWithoutPaymentType($request, $resultExchangeRates);

        $this->assertEquals(102.00, $result);
    }

    public function testCalcfeeWithoutPaymentTypeGreaterThan3000(): void
    {
        $request = (object)[
            'amount' => 4000
        ];
        $resultExchangeRates = 100.00;

        $result = $this->conversionService->calcfeeWithoutPaymentType($request, $resultExchangeRates);

        $this->assertEquals(101.00, $result);
    }

    public function testCalcTotalFeeByTypePaymentCreditCard(): void
    {
        $reflection = new \ReflectionClass(ConversionService::class);
        $creditCardConstant = $reflection->getConstant('CREDIT_CARD');

        $request = (object)[
            'payment_method' => $creditCardConstant
        ];
        $feeWithoutPaymentType = 100.00;

        $result = $this->conversionService->calcTotalFeeByTypePayment($request, $feeWithoutPaymentType);

        $this->assertEquals(107.63, $result);
    }

    public function testCalcTotalFeeByTypePaymentTicket(): void
    {
        $reflection = new \ReflectionClass(ConversionService::class);
        $ticketConstant = $reflection->getConstant('TICKET');

        $request = (object)[
            'payment_method' => $ticketConstant
        ];
        $feeWithoutPaymentType = 100.00;

        $result = $this->conversionService->calcTotalFeeByTypePayment($request, $feeWithoutPaymentType);
        
        $expectedFee = $this->conversionService->calcFee($feeWithoutPaymentType, 1.45);
        $this->assertEquals($expectedFee, $result);
    }
}
