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

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\NullBillType;

/**
 * @internal
 */
final class NullBillTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Modules\Billing\Models\NullBillType
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Billing\Models\BillType', new NullBillType());
    }

    /**
     * @covers \Modules\Billing\Models\NullBillType
     * @group module
     */
    public function testId() : void
    {
        $null = new NullBillType(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers \Modules\Billing\Models\NullBillType
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullBillType(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
