<?php
namespace ksfraser\PaymentDestinations\Interfaces;

interface PaymentDestinationInterface {
    public function getPaymentTerms(): array;
    public function getBankAccountFromTerm(): int;
}

namespace ksfraser\FrontAccounting\PaymentDestinations\Interfaces;

interface PaymentDestinationInterface {
    public function getPaymentTerms(): array;
    public function getBankAccountFromTerm(): int;
}
