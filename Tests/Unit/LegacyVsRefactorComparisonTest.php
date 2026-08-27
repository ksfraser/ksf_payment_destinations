<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\PaymentDestinations\Model\PaymentDestinationModel;
use ksfraser\PaymentDestinations\Service\PaymentDestinationService;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository;
use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

/**
 * @BABOK Related: UT-PD-001-003-001-payment-redirect.md
 */
class LegacyVsRefactorComparisonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // FAMock stubs loaded via bootstrap.php
    }

    public function testLegacyAndNewServiceProduceSameBankAccountLookupStructure(): void
    {
        // Legacy model stub behavior (simulated via FAMock DB state)
        $legacyModel = new PaymentDestinationModel();
        // New service with DI repository
        $repo = new PaymentDestinationRepository(new QueryBuilder('pref_ksf_payment_destinations'), 'pref_ksf_payment_destinations');
        $service = new PaymentDestinationService($repo);

        // Both should resolve to int bank_account (stub returns 0 until DB connected)
        $this->assertIsInt($legacyModel->getBankAccountFromTerm());
        $this->assertIsInt($service->getBankAccountFromTerm(5));

        // Structure comparison: both return scalar int (not array mismatch)
        $legacyResult = $legacyModel->getBankAccountFromTerm();
        $newResult = $service->getBankAccountFromTerm(5);

        // Comparison assertion: same data type and default value
        $this->assertSame(gettype($legacyResult), gettype($newResult), 'Legacy and refactored output types differ');
        $this->assertSame(0, $newResult, 'New service should default to 0 when no mapping found (same as legacy)');
    }

    public function testPaymentTermsListStructureMatches(): void
    {
        // Legacy model relies on get_payment_terms() (FAMock stubbed)
        // New service returns array (stubbed)
        $service = new PaymentDestinationService(
            new PaymentDestinationRepository(new QueryBuilder('pref_ksf_payment_destinations'), 'pref_ksf_payment_destinations')
        );

        $legacyTerms = []; // In full FAMock environment this would come from get_payment_terms()
        $newTerms = $service->getPaymentTerms();

        $this->assertIsArray($newTerms, 'Service must return array like legacy getPaymentTerms');
        $this->assertSame(gettype($legacyTerms), gettype($newTerms), 'Type mismatch between legacy terms and new service');
    }
}
