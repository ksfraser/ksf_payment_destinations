<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

/**
 * @BABOK Related: FR-PD-001-002-table-definition.md
 */
class QueryBuilderTest extends TestCase
{
    public function testSelectGeneratesSelectAllSql(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->select();
        $this->assertStringContainsString('SELECT * FROM pref_ksf_payment_destinations', $qb->toSql());
    }

    public function testSelectWithSpecificFields(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->select(['payment_term', 'bank_account']);
        $this->assertStringContainsString('SELECT payment_term, bank_account', $qb->toSql());
    }

    public function testWhereAddsCondition(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->where('payment_term', 5);
        $this->assertStringContainsString('WHERE payment_term =', $qb->toSql());
    }

    public function testWhereWithCustomOperator(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->where('bank_account', 0, '>');
        $this->assertStringContainsString('WHERE bank_account >', $qb->toSql());
    }

    public function testChainedWhere(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->where('payment_term', 5)->where('bank_account', 3);
        $sql = $qb->toSql();
        $this->assertStringContainsString('payment_term =', $sql);
        $this->assertStringContainsString('bank_account =', $sql);
        $this->assertStringContainsString(' AND ', $sql);
    }

    public function testOrderBy(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->orderBy('payment_term', 'DESC');
        $this->assertStringContainsString('ORDER BY payment_term DESC', $qb->toSql());
    }

    public function testFullQuery(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $qb->select(['payment_term', 'bank_account'])->where('payment_term', 5)->orderBy('payment_term', 'ASC');
        $sql = $qb->toSql();
        $this->assertStringContainsString('SELECT payment_term, bank_account FROM pref_ksf_payment_destinations', $sql);
        $this->assertStringContainsString('WHERE payment_term =', $sql);
        $this->assertStringContainsString('ORDER BY payment_term ASC', $sql);
    }

    public function testFluentInterface(): void
    {
        $qb = new QueryBuilder('pref_ksf_payment_destinations');
        $result = $qb->select(['*'])->where('payment_term', 1)->orderBy('bank_account');
        $this->assertInstanceOf(QueryBuilder::class, $result);
    }
}
