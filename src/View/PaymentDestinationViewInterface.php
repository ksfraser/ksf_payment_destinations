<?php
namespace ksfraser\FrontAccounting\PaymentDestinations\View;

interface PaymentDestinationViewInterface
{
    public function displayMasterForm(): void;
    public function displayUsageForm(): void;
}
