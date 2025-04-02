<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\SalesBillMapper;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Billing\Models\SalesBillMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\Billing\tests\Models\SalesBillMapperTest: App database mapper')]
final class SalesBillMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetSalesBeforePivotInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getSalesBeforePivot(-1));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetSalesAfterPivotInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getSalesAfterPivot(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetSalesByItemIdInvalid() : void
    {
        self::assertEquals(0, SalesBillMapper::getSalesByItemId(99999, new \DateTime('now'), new \DateTime('now'))->getInt());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetSalesByClientIdInvalid() : void
    {
        self::assertEquals(0, SalesBillMapper::getSalesByClientId(99999, new \DateTime('now'), new \DateTime('now'))->getInt());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetLastOrderDateByItemIdInvalid() : void
    {
        self::assertNull(SalesBillMapper::getLastOrderDateByItemId(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetLastOrderDateByClientIdInvalid() : void
    {
        self::assertNull(SalesBillMapper::getLastOrderDateByClientId(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetNewestItemInvoicesInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getNewestItemInvoices(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetNewestClientInvoicesInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getNewestClientInvoices(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemTopClientsInvalid() : void
    {
        self::assertEquals([[], []], SalesBillMapper::getItemTopClients(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemBillsInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getItemBills(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetClientItem() : void
    {
        self::assertEquals([], SalesBillMapper::getClientItem(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemCountrySalesInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getItemCountrySales(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemMonthlySalesCostsInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getItemMonthlySalesCosts([99999], new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetClientMonthlySalesCostsInvalid() : void
    {
        self::assertEquals([], SalesBillMapper::getClientMonthlySalesCosts(99999, new \DateTime('now'), new \DateTime('now')));
    }
}
