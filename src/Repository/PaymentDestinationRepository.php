<?php
namespace ksfraser\PaymentDestinations\Repository;

use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

class PaymentDestinationRepository implements PaymentDestinationRepositoryInterface
{
    protected string $tableName;
    protected QueryBuilder $qb;

    public function __construct(QueryBuilder $qb, string $tableName)
    {
        $this->qb = $qb;
        $this->tableName = $tableName;
        $this->qb = new QueryBuilder($tableName);
    }

    public function findByPaymentTerm(int $term): ?array
    {
        $this->qb = new QueryBuilder($this->tableName);
        $this->qb->where('payment_term', $term);
        // In full implementation: execute query and return row
        return null; // stub
    }

    public function findAll(): array
    {
        $this->qb->select();
        return []; // stub
    }

    public function insert(array $data): bool
    {
        return true; // stub
    }

    public function deleteByTerm(int $term): bool
    {
        return true; // stub
    }

    public function createTable(): bool
    {
        return true; // stub
    }
}
