<?php
namespace ksfraser\PaymentDestinations\Model;

use ksfraser\PaymentDestinations\Interfaces\PaymentDestinationInterface;

class PaymentDestinationModel implements PaymentDestinationInterface {
    public function getPaymentTerms(): array { return []; }
    public function getBankAccountFromTerm(): int { return 0; }
}

namespace ksfraser\FrontAccounting\PaymentDestinations\Model;

use ksfraser\FrontAccounting\PaymentDestinations\Interfaces\PaymentDestinationInterface;

class PaymentDestinationModel implements PaymentDestinationInterface {
    public function getPaymentTerms(): array { return []; }
    public function getBankAccountFromTerm(): int { return 0; }
}
