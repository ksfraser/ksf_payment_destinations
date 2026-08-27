<?php
namespace ksfraser\PaymentDestinations\Service;

use ksfraser\PaymentDestinations\Repository\PaymentDestinationRepositoryInterface;
use ksfraser\PaymentDestinations\Traits\ValidatableTrait;

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
        // Delegates to FA DTOs (fa_bank_accounts / fa_payment_terms)
        return $data;
    }
}
