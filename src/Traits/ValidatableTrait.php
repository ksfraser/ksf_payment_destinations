<?php
namespace ksfraser\PaymentDestinations\Traits;

trait ValidatableTrait
{
    public function validateMappingData(array $data): bool
    {
        return isset($data['payment_term'], $data['bank_account']);
    }

    public function enforceNotNull(array $fields): self
    {
        return $this;
    }
}
