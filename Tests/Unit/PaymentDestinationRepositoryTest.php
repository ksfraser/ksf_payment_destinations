<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepository;
use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;
use Tests\Support\InMemoryDbConnection;

/**
 * Repository tests.
 *
 * These were previously skipped with the reason "Requires FA db_* functions —
 * run in integration test context". That was never true: the repository takes
 * its DbConnectionInterface by constructor injection, so it is exercised here
 * against an in-memory double and needs no FA runtime, no MySQL server and no
 * famock stubs. What genuinely needs a live server is whether the hand-written
 * SQL in insert()/update()/findAll() is accepted by MySQL, and that belongs in
 * the UAT pass named in UAT-PD-001-*, not here.
 *
 * @BABOK Related: FR-PD-001-002-table-definition.md, UT-PD-001-002-002-select-row.md
 */
class PaymentDestinationRepositoryTest extends TestCase
{
    /** @var InMemoryDbConnection */
    private $db;

    /** @var PaymentDestinationRepository */
    private $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new InMemoryDbConnection();
        $this->repo = new PaymentDestinationRepository('0_payment_destinations', '0_', $this->db);
    }

    public function testImplementsRepositoryInterface(): void
    {
        $repo = new PaymentDestinationRepository('test_table', 'test_');
        $this->assertInstanceOf(PaymentDestinationRepositoryInterface::class, $repo);
    }

    public function testFindByPaymentTermReturnsNullWhenNotFound(): void
    {
        $this->assertNull($this->repo->findByPaymentTerm(7));
    }

    public function testFindAllReturnsArray(): void
    {
        $this->assertSame([], $this->repo->findAll());
    }

    public function testInsertReturnsBool(): void
    {
        $this->assertTrue($this->repo->insert(1, 5));
        $this->assertArrayHasKey(1, $this->db->rows());
        $this->assertSame(5, $this->db->rows()[1]['bank_account']);
    }

    public function testDeleteByTermReturnsBool(): void
    {
        $this->repo->insert(1, 5);
        $this->assertTrue($this->repo->deleteByTerm(1));
        $this->assertArrayNotHasKey(1, $this->db->rows());
    }

    public function testCreateTableReturnsBool(): void
    {
        // DDL reports 0 affected rows; createTable() accepts `>= 0`.
        $this->assertTrue($this->repo->createTable());
        $this->assertTrue($this->repo->tableExists());
    }

    /**
     * Duplicates are rejected by the schema, not by application logic:
     * createTable() declares PRIMARY KEY (payment_term), so a second insert
     * for the same term must fail instead of silently overwriting the mapping.
     * Both halves are asserted - the DDL that enforces it, and the insert that
     * loses.
     */
    public function testDuplicatePaymentTermInsertIsRejected(): void
    {
        $this->assertTrue($this->repo->createTable());
        $this->assertRegExp(
            '/PRIMARY KEY\s*\(\s*payment_term\s*\)/i',
            $this->db->lastStatement()
        );

        $this->assertTrue($this->repo->insert(1, 5));
        $this->assertFalse($this->repo->insert(1, 9));
        $this->assertSame(5, $this->db->rows()[1]['bank_account'], 'first mapping must survive');
    }

    public function testUpsertInsertsThenUpdates(): void
    {
        $this->assertTrue($this->repo->upsert(3, 5));
        $this->assertTrue($this->repo->upsert(3, 9));
        $this->assertCount(1, $this->db->rows());
        $this->assertSame(9, $this->db->rows()[3]['bank_account']);
    }

    public function testFindByPaymentTermReturnsStoredRow(): void
    {
        $this->repo->insert(2, 8);
        $row = $this->repo->findByPaymentTerm(2);
        $this->assertIsArray($row);
        $this->assertSame(2, $row['payment_term']);
        $this->assertSame(8, $row['bank_account']);
    }

    /**
     * findByPaymentTerm()/deleteByTerm() go through QueryBuilder, so the
     * payment_term must travel as a bound parameter rather than being
     * interpolated into the SQL.
     */
    public function testWhereClauseIsParameterised(): void
    {
        $this->repo->findByPaymentTerm(4);
        $queried = $this->db->queried[0];
        $this->assertStringContainsString('payment_term = ?', $queried[0]);
        $this->assertStringNotContainsString('payment_term = 4', $queried[0]);
        $this->assertSame([4], $queried[1]);
    }
}