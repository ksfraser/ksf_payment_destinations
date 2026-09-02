<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;

/**
 * Repository integration tests — require FA db_* functions.
 * These tests only verify the interface contract; DB operations
 * are tested in integration/UAT context.
 *
 * @BABOK Related: FR-PD-001-002-table-definition.md, UT-PD-001-002-002-select-row.md
 */
class PaymentDestinationRepositoryTest extends TestCase
{
    public function testImplementsRepositoryInterface(): void
    {
        $repo = new PaymentDestinationRepository('test_table', 'test_');
        $this->assertInstanceOf(PaymentDestinationRepositoryInterface::class, $repo);
    }

    public function testFindByPaymentTermReturnsNullWhenNotFound(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }

    public function testFindAllReturnsArray(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }

    public function testInsertReturnsBool(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }

    public function testDeleteByTermReturnsBool(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }

    public function testCreateTableReturnsBool(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }

    public function testDuplicatePaymentTermInsertIsRejected(): void
    {
        $this->markTestSkipped('Requires FA db_* functions — run in integration test context');
    }
}