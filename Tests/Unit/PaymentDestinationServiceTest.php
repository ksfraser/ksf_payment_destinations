<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ksfraser\PaymentDestinations\Service\PaymentDestinationService;
use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;

/**
 * @BABOK Related: FR-PD-002-001-insert-data.md, FR-PD-002-002-get-payment-terms.md, FR-PD-002-003-bank-account-lookup.md
 */
class PaymentDestinationServiceTest extends TestCase
{
    protected PaymentDestinationServiceInterface $service;
    /** @var MockObject|PaymentDestinationRepositoryInterface */
    protected $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->createMock(PaymentDestinationRepositoryInterface::class);
        $this->service = new PaymentDestinationService($this->repo);
    }

    public function testImplementsServiceInterface(): void
    {
        $this->assertInstanceOf(PaymentDestinationServiceInterface::class, $this->service);
    }

    public function testGetPaymentTermsReturnsArray(): void
    {
        $this->repo->method('findAll')->willReturn([]);
        $result = $this->service->getPaymentTerms();
        $this->assertIsArray($result);
    }

    public function testGetBankAccountFromTermReturnsInt(): void
    {
        $this->repo->method('findByPaymentTerm')->willReturn(['bank_account' => 5]);
        $result = $this->service->getBankAccountFromTerm(5);
        $this->assertIsInt($result);
    }

    public function testGetBankAccountFromTermReturnsZeroWhenNotFound(): void
    {
        $this->repo->method('findByPaymentTerm')->willReturn(null);
        $result = $this->service->getBankAccountFromTerm(9999);
        $this->assertSame(0, $result);
    }

    public function testAddMappingReturnsBool(): void
    {
        $this->repo->method('insert')->willReturn(true);
        $result = $this->service->addMapping(['payment_term' => 5, 'bank_account' => 3]);
        $this->assertIsBool($result);
    }

    public function testResolveMappingNameReturnsArray(): void
    {
        $result = $this->service->resolveMappingName(['payment_term' => 5, 'bank_account' => 3]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment_term', $result);
        $this->assertArrayHasKey('bank_account', $result);
    }
}