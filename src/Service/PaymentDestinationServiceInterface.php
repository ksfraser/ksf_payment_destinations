<?php
namespace ksfraser\PaymentDestinations\Service;

use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;

interface PaymentDestinationServiceInterface
{
    public function getPaymentTerms(): array;
    public function getBankAccountFromTerm(int $term): int;
    public function addMapping(array $data): bool;
    public function resolveMappingName(array $data): array;
    public function createTable(): bool;
}
