<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ksfraser\FrontAccounting\PaymentDestinations\Hooks\HooksPaymentDestinations;
use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;

/**
 * @BABOK Related: FR-PD-001-003-payment-redirect.md, UT-PD-001-003-001-payment-redirect.md
 */
class HooksPaymentDestinationsTest extends TestCase
{
    protected HooksPaymentDestinations $hooks;
    /** @var MockObject|PaymentDestinationServiceInterface */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(PaymentDestinationServiceInterface::class);
        $this->hooks = new HooksPaymentDestinations($this->service);
    }

    public function testKsfGetValueReturnsNullForUnknownKey(): void
    {
        $key = 'payment_destinations.unknown';
        $result = $this->hooks->ksf_get_value($key);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithMissingTermReturnsNull(): void
    {
        $this->service->method('getBankAccountFromTerm')->willReturn(0);
        $cart = new \stdClass();
        $cart->payment_terms = ['terms_indicator' => 999];
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithZeroTermReturnsNull(): void
    {
        $this->service->method('getBankAccountFromTerm')->willReturn(0);
        $cart = new \stdClass();
        $cart->payment_terms = ['terms_indicator' => 0];
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithNoMappingReturnsNull(): void
    {
        $this->service->method('getBankAccountFromTerm')->willReturn(0);
        $cart = new \stdClass();
        $cart->payment_terms = ['terms_indicator' => 5];
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithMappingReturnsAccount(): void
    {
        $this->service->method('getBankAccountFromTerm')->willReturn(42);
        $cart = new \stdClass();
        $cart->payment_terms = ['terms_indicator' => 5, 'cash_sale' => false];
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertSame(42, $result['bank_account']);
    }
}