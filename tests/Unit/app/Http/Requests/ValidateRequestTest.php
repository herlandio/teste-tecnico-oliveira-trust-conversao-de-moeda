<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateRequestTest extends TestCase
{
    private const URL = "/conversion";

    #[Test]
    public function it_validates_required_fields(): void
    {
        $response = $this->json('POST', self::URL, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'from_currency',
                'to_currency',
                'amount',
                'payment_method',
            ]);
    }

    #[Test]
    public function it_validates_amount_is_numeric(): void
    {
        $response = $this->json('POST', self::URL, [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 'not-a-number',
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_passes_with_valid_data(): void
    {
        $response = $this->json('POST', self::URL, [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 10000,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_fails_when_amount_is_too_low(): void
    {
        $response = $this->json('POST', self::URL, [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 500,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Valor da Compra em BRL (deve ser maior que R$ 1.000,00 e menor que R$ 100.000,00)']);
    }

    #[Test]
    public function it_fails_when_amount_is_too_high(): void
    {
        $response = $this->json('POST', self::URL, [
            'from_currency' => 'USD',
            'to_currency' => 'EUR',
            'amount' => 150000,
            'payment_method' => 'credit_card',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Valor da Compra em BRL (deve ser maior que R$ 1.000,00 e menor que R$ 100.000,00)']);
    }
}

