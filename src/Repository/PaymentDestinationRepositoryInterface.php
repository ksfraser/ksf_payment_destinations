<?php
namespace ksfraser\PaymentDestinations\Repository;

interface PaymentDestinationRepositoryInterface
{
    public function findByPaymentTerm(int $term): ?array;
    public function findAll(): array;
    public function insert(array $data): bool;
    public function deleteByTerm(int $term): bool;
    public function createTable(): bool;
}
