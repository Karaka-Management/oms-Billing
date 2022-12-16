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

use Modules\Billing\Models\NullBillElement;

/**
 * @internal
 */
final class NullBillElementTest extends \PHPUnit\Framework\TestCase
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
