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

use Modules\Billing\Models\StockBillMapper;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Billing\Models\StockBillMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\Billing\tests\Models\StockBillMapperTest: App database mapper')]
final class StockBillMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetStockBeforePivotInvalid() : void
    {
        self::assertEquals([], StockBillMapper::getStockBeforePivot(-1));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetStockAfterPivotInvalid() : void
    {
        self::assertEquals([], StockBillMapper::getStockAfterPivot(99999));
    }
}
