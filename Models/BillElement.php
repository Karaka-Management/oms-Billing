<?php
/**
 * Jingga
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
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Stdlib\Base\SmartDateTime;

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
    public int $id = 0;

    public int $order = 0;

    /** @todo: consider to reference the model instead of the int, this would make it much easier in other places like the shop */
    public ?int $item = null;

    public string $itemNumber = '';

    public string $itemName = '';

    public string $itemDescription = '';

    protected int $quantity = 0;

    public ?Subscription $subscription = null;

    public FloatInt $singleSalesPriceNet;

    public FloatInt $singleSalesPriceGross;

    public FloatInt $totalSalesPriceNet;

    public FloatInt $totalSalesPriceGross;

    public FloatInt $singleDiscountP;

    public FloatInt $totalDiscountP;

    public ?FloatInt $singleDiscountR = null;

    public ?FloatInt $discountQ = null;

    public FloatInt $singleListPriceNet;

    public FloatInt $singleListPriceGross;

    public FloatInt $totalListPriceNet;

    public FloatInt $totalListPriceGross;

    public FloatInt $singlePurchasePriceNet;

    public FloatInt $singlePurchasePriceGross;

    public FloatInt $totalPurchasePriceNet;

    public FloatInt $totalPurchasePriceGross;

    public FloatInt $singleProfitNet;

    public FloatInt $singleProfitGross;

    public FloatInt $totalProfitNet;

    public FloatInt $totalProfitGross;

    /**
     * Tax amount
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $taxP;

    /**
     * Tax percentage
     *
     * @var FloatInt
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

    public Bill $bill;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->bill = new NullBill();

        $this->singleListPriceNet   = new FloatInt();
        $this->singleListPriceGross = new FloatInt();

        $this->totalListPriceNet   = new FloatInt();
        $this->totalListPriceGross = new FloatInt();

        $this->singleSalesPriceNet   = new FloatInt();
        $this->singleSalesPriceGross = new FloatInt();

        $this->totalSalesPriceNet   = new FloatInt();
        $this->totalSalesPriceGross = new FloatInt();

        $this->singlePurchasePriceNet   = new FloatInt();
        $this->singlePurchasePriceGross = new FloatInt();

        $this->totalPurchasePriceNet   = new FloatInt();
        $this->totalPurchasePriceGross = new FloatInt();

        $this->singleProfitNet   = new FloatInt();
        $this->singleProfitGross = new FloatInt();

        $this->totalProfitNet   = new FloatInt();
        $this->totalProfitGross = new FloatInt();

        $this->singleDiscountP = new FloatInt();
        $this->totalDiscountP  = new FloatInt();

        $this->taxP = new FloatInt();
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

    /**
     * Set the element quantity.
     *
     * @param int $quantity Quantity
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setQuantity(int $quantity) : void
    {
        if ($this->quantity === $quantity) {
            return;
        }

        $this->quantity = $quantity;
        // @todo: recalculate all the prices!!!
    }

    /**
     * Get quantity.
     *
     * @return int
     *
     * @since 1.0.0
     */
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

    /**
     * Create element from item
     *
     * @param Item    $item     Item
     * @param TaxCode $code     Tax code used for gross amount calculation
     * @param int     $quantity Quantity
     * @param int     $bill     Bill
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function fromItem(Item $item, TaxCode $code, int $quantity = 1, int $bill = 0) : self
    {
        $element                  = new self();
        $element->bill            = $bill;
        $element->item            = empty($item->id) ? null : $item->id;
        $element->itemNumber      = $item->number;
        $element->itemName        = $item->getL11n('name1')->content;
        $element->itemDescription = $item->getL11n('description_short')->content;
        $element->quantity        = $quantity;

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

        if (!empty($element->bill)
            && $item->getAttribute('subscription')->value->getValue() === 1
            && $element->item !== null
        ) {
            $element->subscription        = new Subscription();
            $element->subscription->bill  = $element->bill;
            $element->subscription->item  = $element->item;
            $element->subscription->start = new \DateTime('now'); // @todo: change to bill performanceDate
            $element->subscription->end   = new SmartDateTime('now'); // @todo: depends on subscription type
            $element->subscription->end->smartModify(m: 1);

            $element->subscription->quantity  = $element->quantity;
            $element->subscription->autoRenew = $item->getAttribute('subscription_renewal_type')->value->getValue() === 1;
        }

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
