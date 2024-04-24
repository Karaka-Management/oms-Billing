<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\PurchaseBillMapper;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Billing\Models\PurchaseBillMapper::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\Billing\tests\Models\PurchaseBillMapperTest: App database mapper')]
final class PurchaseBillMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetPurchaseBeforePivotInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getPurchaseBeforePivot(-1));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetPurchaseAfterPivotInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getPurchaseAfterPivot(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetPurchaseByItemIdInvalid() : void
    {
        self::assertEquals(0, PurchaseBillMapper::getPurchaseByItemId(99999, new \DateTime('now'), new \DateTime('now'))->getInt());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetPurchaseBySupplierIdInvalid() : void
    {
        self::assertEquals(0, PurchaseBillMapper::getPurchaseBySupplierId(99999, new \DateTime('now'), new \DateTime('now'))->getInt());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetAvgPurchasePriceByItemIdInvalid() : void
    {
        self::assertEquals(0, PurchaseBillMapper::getAvgPurchasePriceByItemId(99999, new \DateTime('now'), new \DateTime('now'))->getInt());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetLastOrderDateByItemIdInvalid() : void
    {
        self::assertNull(PurchaseBillMapper::getLastOrderDateByItemId(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetLastOrderDateBySupplierIdInvalid() : void
    {
        self::assertNull(PurchaseBillMapper::getLastOrderDateBySupplierId(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetNewestItemInvoicesInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getNewestItemInvoices(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetNewestSupplierInvoicesInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getNewestSupplierInvoices(99999));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemTopSuppliersInvalid() : void
    {
        self::assertEquals([[], []], PurchaseBillMapper::getItemTopSuppliers(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemCountryPurchaseInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getItemCountryPurchase(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetItemMonthlyPurchaseCostsInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getItemMonthlyPurchaseCosts(99999, new \DateTime('now'), new \DateTime('now')));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testGetSupplierMonthlyPurchaseCostsInvalid() : void
    {
        self::assertEquals([], PurchaseBillMapper::getSupplierMonthlyPurchaseCosts(99999, new \DateTime('now'), new \DateTime('now')));
    }
}
