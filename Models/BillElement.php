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

use Modules\Billing\Models\Tax\TaxCombination;
use Modules\Finance\Models\TaxCode;
use Modules\ItemManagement\Models\Container;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\NullItem;
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

    public ?Item $item = null;

    public ?Container $container = null;

    public string $itemNumber = '';

    public string $itemName = '';

    public string $itemDescription = '';

    /**
     * Line quantity
     *
     * Careful this also includes the bonus items defined in $discountQ!
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $quantity;

    public ?Subscription $subscription = null;

    /**
     * Single unit price
     *
     * Careful this is NOT corrected by bonus items defined in $discountQ
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $singleSalesPriceNet;

    public FloatInt $effectiveSingleSalesPriceNet;

    public FloatInt $singleSalesPriceGross;

    public FloatInt $totalSalesPriceNet;

    public FloatInt $totalSalesPriceGross;

    public FloatInt $singleDiscountP;

    public FloatInt $totalDiscountP;

    public FloatInt $singleDiscountR;

    public FloatInt $discountQ;

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

    public ?int $itemSegment = null;

    public ?int $itemSection = null;

    public ?int $itemSalesGroup = null;

    public ?int $itemProductGroup = null;

    public ?int $itemType = null;

    public string $fiAccount = '';

    public ?string $costcenter = null;

    public ?string $costobject = null;

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

    // Distribution of lots/sn and from which stock location
    public array $identifiers = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->bill = new NullBill();

        $this->quantity = new FloatInt();

        $this->singleListPriceNet   = new FloatInt();
        $this->singleListPriceGross = new FloatInt();

        $this->totalListPriceNet   = new FloatInt();
        $this->totalListPriceGross = new FloatInt();

        $this->singleSalesPriceNet   = new FloatInt();
        $this->singleSalesPriceGross = new FloatInt();

        $this->effectiveSingleSalesPriceNet = new FloatInt();

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
        $this->singleDiscountR = new FloatInt();
        $this->discountQ       = new FloatInt();

        $this->taxP = new FloatInt();
        $this->taxR = new FloatInt();
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

        $this->recalculatePrices();
    }

    public function recalculatePrices() : void
    {
        $this->totalListPriceNet->value  = (int) \round(($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()) * $this->singleListPriceNet->value, 0);
        $this->totalSalesPriceNet->value = (int) \round(($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()) * $this->singleListPriceNet->value, 0);

        // @todo Check if this is correct, this should maybe happen after applying the discounts?!
        // This depends on if the single price is already discounted or not
        $this->singleProfitNet->value = $this->singleSalesPriceNet->value - $this->singlePurchasePriceNet->value;
        $this->totalProfitNet->value  = $this->totalSalesPriceNet->value - $this->totalPurchasePriceNet->value;

        $this->taxP->value = (int) \round($this->taxR->value / 1000000 * $this->totalSalesPriceNet->value, 0);

        $this->singleListPriceGross->value  = (int) \round($this->singleListPriceNet->value + $this->singleListPriceNet->value * $this->taxR->value / 10000, 0);
        $this->totalListPriceGross->value   = (int) \round($this->totalListPriceNet->value + $this->totalListPriceNet->value * $this->taxR->value / 10000, 0);
        $this->singleSalesPriceGross->value = (int) \round($this->singleSalesPriceNet->value + $this->singleSalesPriceNet->value * $this->taxR->value / 10000, 0);
        $this->totalSalesPriceGross->value  = (int) \round($this->totalSalesPriceNet->value + $this->totalSalesPriceNet->value * $this->taxR->value / 10000, 0);

        $this->singleProfitGross->value = $this->singleSalesPriceGross->value - $this->singlePurchasePriceGross->value;
        $this->totalProfitGross->value  = (int) \round(($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()) * ($this->totalSalesPriceGross->value - $this->totalPurchasePriceGross->value), 0);

        $this->singleDiscountP->value = $this->quantity->value - $this->discountQ->value === 0
            ? 0
            : (int) \round($this->totalDiscountP->value / ($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()));

        // important because the quantity includes $discountQ
        $this->effectiveSingleSalesPriceNet->value = (int) \round($this->totalSalesPriceNet->value / ($this->quantity->value / 10000));
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
        $this->item = new NullItem($item);
    }

    /**
     * Create element from item
     *
     * @param Item    $item     Item
     * @param TaxCode $taxCode  Tax code used for gross amount calculation
     * @param int     $quantity Quantity
     * @param Bill    $bill     Bill
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function fromItem(
        Item $item,
        TaxCombination $taxCombination,
        int $quantity = 10000,
        Bill $bill = null,
        ?Container $container = null
    ) : self
    {
        $element                  = new self();
        $element->bill            = $bill;
        $element->item            = empty($item->id) ? null : $item;
        $element->container       = empty($container->id) ? null : $container;
        $element->itemNumber      = $item->number;
        $element->itemName        = $item->getL11n('name1')->content;
        $element->itemDescription = $item->getL11n('description_short')->content;
        $element->quantity->value = $quantity;

        $element->taxR      = new FloatInt($taxCombination->taxCode->percentageInvoice);
        $element->taxCode   = $taxCombination->taxCode->abbr;
        $element->fiAccount = $taxCombination->account;

        // @todo the purchase price is based on lot/sn/avg prices if available
        $element->singlePurchasePriceNet->value = $item->purchasePrice->value;
        $element->totalPurchasePriceNet->value  = (int) ($element->quantity->getNormalizedValue() * $item->purchasePrice->value);

        $element->singlePurchasePriceGross->value = (int) \round($element->singlePurchasePriceNet->value + $element->singlePurchasePriceNet->value * $element->taxR->value / 10000, 0);
        $element->totalPurchasePriceGross->value  = (int) \round($element->totalPurchasePriceNet->value + $element->totalPurchasePriceNet->value * $element->taxR->value / 10000, 0);

        if ($element->bill->id !== 0
            && $item->getAttribute('subscription')->value->getValue() === 1
            && $element->item !== null
        ) {
            $element->subscription        = new Subscription();
            $element->subscription->bill  = $element->bill->id;
            $element->subscription->item  = $element->item->id;
            $element->subscription->start = $bill?->performanceDate ?? new \DateTime('now');
            $element->subscription->end   = (new SmartDateTime('now'))->smartModify(m: 1); // @todo depends on subscription type

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
            'item'            => $this->item->id,
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
