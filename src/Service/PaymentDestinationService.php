<?php
namespace ksfraser\PaymentDestinations\Service;

use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;
use Ksfraser\Traits\ValidatableTrait;

class PaymentDestinationService implements PaymentDestinationServiceInterface
{
    use ValidatableTrait;

    protected PaymentDestinationRepositoryInterface $repo;

    public function __construct(PaymentDestinationRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getPaymentTerms(): array
    {
        return []; // delegates to repository / FA layer
    }

    public function getBankAccountFromTerm(int $term): int
    {
        $row = $this->repo->findByPaymentTerm($term);
        return $row['bank_account'] ?? 0;
    }

    public function addMapping(array $data): bool
    {
        $enriched = $this->resolveMappingName($data);
        return $this->repo->insert($enriched);
    }

    public function resolveMappingName(array $data): array
    {
        return $data;
    }

    public function createTable(): bool
    {
        return $this->repo->createTable();
    }

    protected function validate(): array
    {
        return [];
    }
}
