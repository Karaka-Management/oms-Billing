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

use Modules\Billing\Models\BillElement;

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
        self::assertEquals(0, $this->element->id);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\IntFloat', $this->element->singleSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\IntFloat', $this->element->totalSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\IntFloat', $this->element->singlePurchasePriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\IntFloat', $this->element->totalPurchasePriceNet);
    }

    /**
     * @covers Modules\Billing\Models\BillElement
     * @group module
     */
    public function testItemInputOutput() : void
    {
        $this->element->setItem(123);
        self::assertEquals(123, $this->element->item);
    }

    /**
     * @covers Modules\Billing\Models\BillElement
     * @group module
     */
    public function testSerialize() : void
    {
        $this->element->order           = 2;
        $this->element->item            = 3;
        $this->element->itemNumber      = '123456';
        $this->element->itemName        = 'Test';
        $this->element->itemDescription = 'Description';
        $this->element->bill            = 5;
        $this->element->setQuantity(4);

        self::assertEquals(
            [
                'id'              => 0,
                'order'           => 2,
                'item'            => 3,
                'itemNumber'      => '123456',
                'itemName'        => 'Test',
                'itemDescription' => 'Description',
                'quantity'        => 4,
                'bill'            => 5,
            ],
            $this->element->jsonSerialize()
        );
    }
}
