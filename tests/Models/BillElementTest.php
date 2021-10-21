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

use Modules\Billing\Models\BillElement;
use phpOMS\Localization\ISO4217CharEnum;

/**
 * @internal
 */
final class BillElementTest extends \PHPUnit\Framework\TestCase
{
    private BillElement $element;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->element = new BillElement();
    }

    /**
     * @covers Modules\Billing\Models\BillElement
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->element->getId());
        self::assertInstanceOf('\phpOMS\Localization\Money', $this->element->singleSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Localization\Money', $this->element->totalSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Localization\Money', $this->element->singlePurchasePriceNet);
        self::assertInstanceOf('\phpOMS\Localization\Money', $this->element->totalPurchasePriceNet);
    }

    public function testItemInputOutput() : void
    {
        $this->element->setItem(123);
        self::assertEquals(123, $this->element->item);
    }
}
