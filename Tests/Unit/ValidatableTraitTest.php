<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TraitConsumer
{
    use \ksfraser\PaymentDestinations\Traits\ValidatableTrait;
}

/**
 * @BABOK Related: FR-PD-002-001-insert-data.md
 */
class ValidatableTraitTest extends TestCase
{
    public function testValidateMappingDataReturnsTrueForValidData(): void
    {
        $obj = new TraitConsumer();
        $result = $obj->validateMappingData(['payment_term' => 5, 'bank_account' => 3]);
        $this->assertTrue($result);
    }

    public function testValidateMappingDataReturnsFalseForMissingPaymentTerm(): void
    {
        $obj = new TraitConsumer();
        $result = $obj->validateMappingData(['bank_account' => 3]);
        $this->assertFalse($result);
    }

    public function testValidateMappingDataReturnsFalseForMissingBankAccount(): void
    {
        $obj = new TraitConsumer();
        $result = $obj->validateMappingData(['payment_term' => 5]);
        $this->assertFalse($result);
    }

    public function testValidateMappingDataReturnsFalseForEmptyArray(): void
    {
        $obj = new TraitConsumer();
        $result = $obj->validateMappingData([]);
        $this->assertFalse($result);
    }

    public function testEnforceNotNullReturnsSelf(): void
    {
        $obj = new TraitConsumer();
        $result = $obj->enforceNotNull(['payment_term' => 5]);
        $this->assertSame($obj, $result);
    }
}
