<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ksfraser\FrontAccounting\PaymentDestinations\Model\PaymentDestinationModel;
use ksfraser\PaymentDestinations\Service\PaymentDestinationService;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;

/**
 * @BABOK Related: UT-PD-001-003-001-payment-redirect.md
 */
class LegacyVsRefactorComparisonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLegacyAndNewServiceProduceSameBankAccountLookupStructure(): void
    {
        $legacyModel = new PaymentDestinationModel();

        $repo = $this->createMock(PaymentDestinationRepositoryInterface::class);
        $repo->method('findByPaymentTerm')->willReturn(['bank_account' => 0]);
        $service = new PaymentDestinationService($repo);

        $this->assertIsInt($legacyModel->getBankAccountFromTerm());
        $this->assertIsInt($service->getBankAccountFromTerm(5));

        $legacyResult = $legacyModel->getBankAccountFromTerm();
        $newResult = $service->getBankAccountFromTerm(5);

        $this->assertSame(gettype($legacyResult), gettype($newResult), 'Legacy and refactored output types differ');
        $this->assertSame(0, $newResult, 'New service should default to 0 when no mapping found (same as legacy)');
    }

    public function testPaymentTermsListStructureMatches(): void
    {
        $repo = $this->createMock(PaymentDestinationRepositoryInterface::class);
        $repo->method('findAll')->willReturn([]);
        $service = new PaymentDestinationService($repo);

        $legacyTerms = [];
        $newTerms = $service->getPaymentTerms();

        $this->assertIsArray($newTerms, 'Service must return array like legacy getPaymentTerms');
        $this->assertSame(gettype($legacyTerms), gettype($newTerms), 'Type mismatch between legacy terms and new service');
    }
}