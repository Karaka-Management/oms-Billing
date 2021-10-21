<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\NullBillElement;

/**
 * @internal
 */
final class Null extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Billing\Models\NullBillElement
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Billing\Models\BillElement', new NullBillElement());
    }

    /**
     * @covers Modules\Billing\Models\NullBillElement
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullBillElement(2);
        self::assertEquals(2, $null->getId());
    }
}
