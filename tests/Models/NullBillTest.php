<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\NullBill;

/**
 * @internal
 */
final class NullBillTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Billing\Models\NullBill
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Billing\Models\Bill', new NullBill());
    }

    /**
     * @covers Modules\Billing\Models\NullBill
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullBill(2);
        self::assertEquals(2, $null->getId());
    }
}
