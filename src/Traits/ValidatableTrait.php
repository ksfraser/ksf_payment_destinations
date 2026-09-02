<?php
namespace ksfraser\PaymentDestinations\Traits;

trait ValidatableTrait
{
    public function validateMappingData(array $data): bool
    {
        return isset($data['payment_term']) && isset($data['bank_account']);
    }

    public function enforceNotNull(array $data): self
    {
        return $this;
    }
}
