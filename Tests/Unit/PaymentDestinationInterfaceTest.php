<?php
declare(strict_types=1);
namespace Tests\Unit;

use ksfraser\FrontAccounting\PaymentDestinations\Interfaces\PaymentDestinationInterface;
use ksfraser\FrontAccounting\PaymentDestinations\Model\PaymentDestinationModel;

/**
 * @BABOK Related: UT-PD-001-002-001-define-table.md
 */
class PaymentDestinationInterfaceTest extends \PHPUnit\Framework\TestCase
{
    public function testInterfaceImplementation(): void
    {
        $model = $this->createMock(PaymentDestinationModel::class);
        $this->assertInstanceOf(PaymentDestinationInterface::class, $model);
    }
}
