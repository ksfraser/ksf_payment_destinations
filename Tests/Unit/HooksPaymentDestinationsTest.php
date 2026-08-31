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

    public function testGetModuleConstantsReturnsArray(): void
    {
        $result = $this->hooks->getModuleConstants();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('PREFS', $result);
        $this->assertSame('ksf_payment_destinations_prefs', $result['PREFS']);
    }

    public function testGetModuleCapabilitiesReturnsRoutingAndMapping(): void
    {
        $result = $this->hooks->getModuleCapabilities();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('routing', $result);
        $this->assertArrayHasKey('mapping', $result);
        $this->assertTrue($result['routing']);
        $this->assertTrue($result['mapping']);
    }

    public function testHasCapabilityReturnsTrueForExistingCap(): void
    {
        $this->assertTrue($this->hooks->hasCapability('routing'));
        $this->assertTrue($this->hooks->hasCapability('mapping'));
    }

    public function testHasCapabilityReturnsFalseForNonexistentCap(): void
    {
        $this->assertFalse($this->hooks->hasCapability('nonexistent'));
    }

    public function testRespondToCapabilityRequestWithCartReturnsRoutingResult(): void
    {
        $cart = new class {
            public $payment_terms = ['terms_indicator' => 5];
            public $pos = ['pos_account' => 1];
        };
        $result = $this->hooks->respondToCapabilityRequest('routing', ['cart' => $cart]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('bank_account', $result);
        $this->assertIsInt($result['bank_account']);
    }

    public function testRespondToCapabilityRequestWithoutCartReturnsNull(): void
    {
        $result = $this->hooks->respondToCapabilityRequest('routing', []);
        $this->assertNull($result);
    }

    public function testRespondToCapabilityRequestWithUnknownCapReturnsNull(): void
    {
        $result = $this->hooks->respondToCapabilityRequest('unknown_cap', ['cart' => null]);
        $this->assertNull($result);
    }
}
