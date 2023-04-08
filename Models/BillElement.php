<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Finance\Models\TaxCode;
use Modules\ItemManagement\Models\Item;
use phpOMS\Localization\Money;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class BillElement implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    public int $order = 0;

    /** @todo: consider to reference the model instead of the int, this would make it much easier in other places like the shop */
    public ?int $item = null;

    public string $itemNumber = '';

    public string $itemName = '';

    public string $itemDescription = '';

    protected int $quantity = 0;

    public Money $singleSalesPriceNet;

    public Money $singleSalesPriceGross;

    public Money $totalSalesPriceNet;

    public Money $totalSalesPriceGross;

    public Money $singleDiscountP;

    public Money $totalDiscountP;

    public ?FloatInt $singleDiscountR = null;

    public ?FloatInt $discountQ = null;

    public Money $singleListPriceNet;

    public Money $singleListPriceGross;

    public Money $totalListPriceNet;

    public Money $totalListPriceGross;

    public Money $singlePurchasePriceNet;

    public Money $singlePurchasePriceGross;

    public Money $totalPurchasePriceNet;

    public Money $totalPurchasePriceGross;

    public Money $singleProfitNet;

    public Money $singleProfitGross;

    public Money $totalProfitNet;

    public Money $totalProfitGross;

    /**
     * Tax amount
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $taxP;

    /**
     * Tax percentage
     *
     * @var null|FloatInt
     * @since 1.0.0
     */
    public FloatInt $taxR;

    public string $taxCode = '';

    /**
     * Event assigned to this element.
     *
     * @var int
     * @since 1.0.0
     */
    public int $event = 0;

    /**
     * Promotion assigned to this element.
     *
     * @var int
     * @since 1.0.0
     */
    public int $promotion = 0;

    public int | Bill $bill = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->singleListPriceNet   = new Money();
        $this->singleListPriceGross = new Money();

        $this->totalListPriceNet   = new Money();
        $this->totalListPriceGross = new Money();

        $this->singleSalesPriceNet   = new Money();
        $this->singleSalesPriceGross = new Money();

        $this->totalSalesPriceNet   = new Money();
        $this->totalSalesPriceGross = new Money();

        $this->singlePurchasePriceNet   = new Money();
        $this->singlePurchasePriceGross = new Money();

        $this->totalPurchasePriceNet   = new Money();
        $this->totalPurchasePriceGross = new Money();

        $this->singleProfitNet   = new Money();
        $this->singleProfitGross = new Money();

        $this->totalProfitNet   = new Money();
        $this->totalProfitGross = new Money();

        $this->singleDiscountP = new Money();
        $this->totalDiscountP  = new Money();

        $this->taxP = new Money();
        $this->taxR = new FloatInt();
    }

    /**
     * Get id.
     *
     * @return int Model id
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    public function setQuantity(int $quantity) : void
    {
        if ($this->quantity === $quantity) {
            return;
        }

        $this->quantity = $quantity;
        // @todo: recalculate all the prices!!!
    }

    public function getQuantity() : int
    {
        return $this->quantity;
    }

    /**
     * Set item.
     *
     * @param int $item Item
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setItem(int $item) : void
    {
        $this->item = $item;
    }

    public static function fromItem(Item $item, TaxCode $code, int $quantity = 1) : self
    {
        $element = new self();
        $element->item = $item->getId();
        $element->itemNumber = $item->number;
        $element->itemName = $item->getL11n('name1')->description;
        $element->itemDescription = $item->getL11n('description_short')->description;
        $element->quantity = $quantity;

        // @todo: Use pricing instead of the default sales price
        // @todo: discounts might be in quantities
        $element->singleListPriceNet->setInt($item->salesPrice->getInt());
        $element->totalListPriceNet->setInt($element->quantity * $item->salesPrice->getInt());
        $element->singleSalesPriceNet->setInt($item->salesPrice->getInt());
        $element->totalSalesPriceNet->setInt($element->quantity * $item->salesPrice->getInt());
        $element->singlePurchasePriceNet->setInt($item->purchasePrice->getInt());
        $element->totalPurchasePriceNet->setInt($element->quantity * $item->purchasePrice->getInt());

        $element->singleProfitNet->setInt($element->singleSalesPriceNet->getInt() - $element->singlePurchasePriceNet->getInt());
        $element->totalProfitNet->setInt($element->quantity * ($element->totalSalesPriceNet->getInt() - $element->totalPurchasePriceNet->getInt()));

        $element->taxP    = new FloatInt((int) (($code->percentageInvoice * $element->totalSalesPriceNet->getInt()) / 10000));
        $element->taxR    = new FloatInt($code->percentageInvoice);
        $element->taxCode = $code->abbr;

        $element->singleListPriceGross->setInt((int) ($element->singleListPriceNet->getInt() + $element->singleListPriceNet->getInt() * $element->taxR->getInt() / 10000));
        $element->totalListPriceGross->setInt((int) ($element->totalListPriceNet->getInt() + $element->totalListPriceNet->getInt() * $element->taxR->getInt() / 10000));
        $element->singleSalesPriceGross->setInt((int) ($element->singleSalesPriceNet->getInt() + $element->singleSalesPriceNet->getInt() * $element->taxR->getInt() / 10000));
        $element->totalSalesPriceGross->setInt((int) ($element->totalSalesPriceNet->getInt() + $element->totalSalesPriceNet->getInt() * $element->taxR->getInt() / 10000));
        $element->singlePurchasePriceGross->setInt((int) ($element->singlePurchasePriceNet->getInt() + $element->singlePurchasePriceNet->getInt() * $element->taxR->getInt() / 10000));
        $element->totalPurchasePriceGross->setInt((int) ($element->totalPurchasePriceNet->getInt() + $element->totalPurchasePriceNet->getInt() * $element->taxR->getInt() / 10000));

        $element->singleProfitGross->setInt($element->singleSalesPriceGross->getInt() - $element->singlePurchasePriceGross->getInt());
        $element->totalProfitGross->setInt($element->quantity * ($element->totalSalesPriceGross->getInt() - $element->totalPurchasePriceGross->getInt()));

        return $element;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'              => $this->id,
            'order'           => $this->order,
            'item'            => $this->item,
            'itemNumber'      => $this->itemNumber,
            'itemName'        => $this->itemName,
            'itemDescription' => $this->itemDescription,
            'quantity'        => $this->quantity,
            'bill'            => $this->bill,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
