<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use Tests\TestCase;
use App\Services\ConversionService;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

class ConversionControllerTest extends TestCase
{
    private ConversionService $conversionService;
    private const URL = "/conversion";

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversionService = $this->createMock(ConversionService::class);
        Route::post(self::URL, [\App\Http\Controllers\ConversionController::class, 'convert'])
            ->defaults('conversionService', $this->conversionService);
    }

    #[Test]
    public function it_should_convert_currency_successfully(): void
    {
        $this->conversionService->method('calcExchangeRates')->willReturn(5.0);
        $this->conversionService->method('calcfeeWithoutPaymentType')->willReturn(10.0);
        $this->conversionService->method('calcTotalFeeByTypePayment')->willReturn(15.0);
        
        $requestData = [
            'from_currency' => 'BRL',
            'to_currency' => 'USD',
            'amount' => 2000,
            'payment_method' => 'credit_card'
        ];

        $response = $this->json('POST', self::URL, $requestData);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'fromCurrency' => 'BRL',
                         'toCurrency' => 'USD',
                         'valueToConversion' => '2.000,00',
                         'paymentType' => 'credit_card',
                     ]
                 ]);
    }

    #[Test]
    public function it_should_return_error_if_amount_is_out_of_bounds(): void
    {
        $requestData = [
            'from_currency' => 'BRL',
            'to_currency' => 'USD',
            'amount' => 500,
            'payment_method' => 'credit_card'
        ];

        $response = $this->json('POST', self::URL, $requestData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
                 ->assertJson([
                     'error' => 'Valor da Compra em BRL (deve ser maior que R$ 1.000,00 e menor que R$ 100.000,00)'
                 ]);
    }
}
