<?php
namespace ksfraser\FrontAccounting\PaymentDestinations\Controller;

use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;
use ksfraser\FrontAccounting\PaymentDestinations\View\PaymentDestinationViewInterface;

class PaymentDestinationController
{
    protected PaymentDestinationServiceInterface $service;
    protected PaymentDestinationViewInterface $view;
    protected array $prefs;

    public function __construct(
        PaymentDestinationServiceInterface $service,
        PaymentDestinationViewInterface $view,
        array $prefs
    ) {
        $this->service = $service;
        $this->view = $view;
        $this->prefs = $prefs;
    }

    public function run(): void
    {
        if (isset($_POST['payment_term']) && ($_POST['func'] ?? '') !== 'delete') {
            $this->service->addMapping($_POST);
        }
        $this->view->displayMasterForm();
    }

    public function install(): void
    {
        $this->service->createTable();
    }
}
