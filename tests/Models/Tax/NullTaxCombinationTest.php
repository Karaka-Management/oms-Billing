<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models\Tax;

use Modules\Billing\Models\Tax\NullTaxCombination;

/**
 * @internal
 */
final class NullTaxCombinationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Modules\Billing\Models\Tax\NullTaxCombination
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Billing\Models\Tax\TaxCombination', new NullTaxCombination());
    }

    /**
     * @covers \Modules\Billing\Models\Tax\NullTaxCombination
     * @group module
     */
    public function testId() : void
    {
        $null = new NullTaxCombination(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers \Modules\Billing\Models\Tax\NullTaxCombination
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTaxCombination(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
