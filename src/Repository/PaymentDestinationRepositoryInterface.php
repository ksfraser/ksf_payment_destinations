<?php
namespace ksfraser\PaymentDestinations\Repository;

interface PaymentDestinationRepositoryInterface
{
    public function findByPaymentTerm(int $term): ?array;
    public function findAll(): array;
    public function insert(int $paymentTerm, int $bankAccount): bool;
    public function update(int $paymentTerm, int $bankAccount): bool;
    public function upsert(int $paymentTerm, int $bankAccount): bool;
    public function deleteByTerm(int $term): bool;
    public function createTable(): bool;
    public function tableExists(): bool;
}
