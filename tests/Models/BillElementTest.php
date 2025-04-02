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

use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\NullBill;
use Modules\ItemManagement\Models\NullItem;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Billing\Models\BillElement::class)]
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->element->id);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->element->singleSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->element->totalSalesPriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->element->singlePurchasePriceNet);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->element->totalPurchasePriceNet);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testItemInputOutput() : void
    {
        $this->element->setItem(123);
        self::assertEquals(123, $this->element->item->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testSerialize() : void
    {
        $this->element->order           = 2;
        $this->element->item            = new NullItem(3);
        $this->element->itemNumber      = '123456';
        $this->element->itemName        = 'Test';
        $this->element->itemDescription = 'Description';
        $this->element->bill            = new NullBill(5);
        $this->element->setQuantity(4);

        self::assertEquals(
            [
                'id'              => 0,
                'order'           => 2,
                'item'            => 3,
                'itemNumber'      => '123456',
                'itemName'        => 'Test',
                'itemDescription' => 'Description',
                'quantity'        => new FloatInt(4),
                'bill'            => $this->element->bill,
            ],
            $this->element->jsonSerialize()
        );
    }
}
