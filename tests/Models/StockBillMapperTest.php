<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\StockBillMapper;

/**
 * @testdox Modules\Billing\tests\Models\StockBillMapperTest: App database mapper
 *
 * @internal
 */
final class StockBillMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Billing\Models\StockBillMapper
     * @group module
     */
    public function testGetStockBeforePivotInvalid() : void
    {
        self::assertEquals([], StockBillMapper::getStockBeforePivot(-1));
    }

    /**
     * @covers Modules\Billing\Models\StockBillMapper
     * @group module
     */
    public function testGetStockAfterPivotInvalid() : void
    {
        self::assertEquals([], StockBillMapper::getStockAfterPivot(99999));
    }
}
