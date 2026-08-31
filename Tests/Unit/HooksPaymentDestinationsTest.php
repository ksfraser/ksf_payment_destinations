<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\PaymentDestinations\Hooks\HooksPaymentDestinations;
use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository;
use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

/**
 * @BABOK Related: FR-PD-001-003-payment-redirect.md, UT-PD-001-003-001-payment-redirect.md
 */
class HooksPaymentDestinationsTest extends TestCase
{
    protected HooksPaymentDestinations $hooks;
    protected PaymentDestinationServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $repo = new \ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository(
            new \ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder('pref_ksf_payment_destinations'),
            'pref_ksf_payment_destinations'
        );
        $this->service = new \ksfraser\PaymentDestinations\Service\PaymentDestinationService($repo);
        $this->hooks = new HooksPaymentDestinations($this->service);
    }

    public function testKsfGetValueReturnsNullForUnknownKey(): void
    {
        $key = 'payment_destinations.unknown';
        $result = $this->hooks->ksf_get_value($key);
        $this->assertNull($result);
    }

    public function testKsfGetValuesReturnsSubset(): void
    {
        $keys = ['payment_destinations.routing'];
        $result = $this->hooks->ksf_get_values($keys);
        $this->assertIsArray($result);
    }

    public function testKsfGetValuesWithEmptyKeysReturnsAll(): void
    {
        $result = $this->hooks->ksf_get_values();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_destinations.routing', $result);
    }

    public function testKsfSetValueDoesNotThrow(): void
    {
        $data = ['key' => 'payment_destinations.routing', 'value' => []];
        $this->hooks->ksf_set_value($data);
        $this->assertTrue(true);
    }

    public function testGetRoutingForCartWithMissingTermReturnsNull(): void
    {
        $cart = new class {
            public $payment_terms = [];
            public $pos = ['pos_account' => 1];
        };
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithZeroTermReturnsNull(): void
    {
        $cart = new class {
            public $payment_terms = ['terms_indicator' => 0];
            public $pos = ['pos_account' => 1];
        };
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testGetRoutingForCartWithNoMappingReturnsNull(): void
    {
        global $cart;
        $cart = new class {
            public $payment_terms = ['terms_indicator' => 9999];
            public $pos = ['pos_account' => 1];
        };
        $result = $this->hooks->getRoutingForCart($cart);
        $this->assertNull($result);
    }

    public function testHookInstanceHasServiceFromConstructor(): void
    {
        $this->assertSame($this->service, $this->hooks->getService());
    }
}
