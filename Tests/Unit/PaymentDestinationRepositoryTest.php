<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;
use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

/**
 * @BABOK Related: FR-PD-001-002-table-definition.md, UT-PD-001-002-002-select-row.md
 */
class PaymentDestinationRepositoryTest extends TestCase
{
    protected PaymentDestinationRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PaymentDestinationRepository(
            new QueryBuilder('pref_ksf_payment_destinations'),
            'pref_ksf_payment_destinations'
        );
    }

    public function testImplementsRepositoryInterface(): void
    {
        $this->assertInstanceOf(PaymentDestinationRepositoryInterface::class, $this->repo);
    }

    public function testFindByPaymentTermReturnsNullWhenNotFound(): void
    {
        $result = $this->repo->findByPaymentTerm(9999);
        $this->assertNull($result);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->repo->findAll();
        $this->assertIsArray($result);
    }

    public function testInsertReturnsBool(): void
    {
        $result = $this->repo->insert(['payment_term' => 5, 'bank_account' => 3]);
        $this->assertIsBool($result);
    }

    public function testDeleteByTermReturnsBool(): void
    {
        $result = $this->repo->deleteByTerm(5);
        $this->assertIsBool($result);
    }

    public function testCreateTableReturnsBool(): void
    {
        $result = $this->repo->createTable();
        $this->assertIsBool($result);
    }
}
