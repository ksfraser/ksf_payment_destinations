<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ksfraser\FrontAccounting\PaymentDestinations\Controller\PaymentDestinationController;
use ksfraser\PaymentDestinations\Service\PaymentDestinationServiceInterface;
use ksfraser\FrontAccounting\PaymentDestinations\View\PaymentDestinationViewInterface;

/**
 * @BABOK Related: FR-PD-001-001-mapping-ui.md, UC-PD-002-add-payment-mapping.md
 */
class PaymentDestinationControllerTest extends TestCase
{
    protected PaymentDestinationController $controller;
    protected $service;
    protected $view;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->createMock(PaymentDestinationServiceInterface::class);
        $this->view = $this->createMock(PaymentDestinationViewInterface::class);
        $this->controller = new PaymentDestinationController(
            $this->service,
            $this->view,
            ['pref' => 'ksf_payment_destinations_prefs']
        );
    }

    public function testControllerInstantiatesWithDependencies(): void
    {
        $this->assertInstanceOf(PaymentDestinationController::class, $this->controller);
    }

    public function testInstallCallsCreateTable(): void
    {
        $this->service->expects($this->once())->method('createTable');
        $this->controller->install();
    }
}
