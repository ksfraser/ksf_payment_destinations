<?php
namespace ksfraser\PaymentDestinations\Traits;

trait ValidatableTrait
{
    protected function validateMappingData(array $data): bool
    {
        return isset($data['payment_term'], $data['bank_account']);
    }

    protected function enforceNotNull(array $fields): self
    {
        return $this;
    }
}
