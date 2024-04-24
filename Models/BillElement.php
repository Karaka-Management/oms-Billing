<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
use Modules\ItemManagement\Models\Container;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\NullItem;
use phpOMS\Localization\ISO4217DecimalEnum;
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

    public FloatInt $totalPurchasePriceNet;

    public FloatInt $singleProfitNet;

    public FloatInt $totalProfitNet;

    public ?int $itemSegment = null;

    public ?int $itemSection = null;

    public ?int $itemSalesGroup = null;

    public ?int $itemProductGroup = null;

    public ?int $itemType = null;

    public string $fiAccount = '';

    public ?string $costcenter = null;

    public ?string $costobject = null;

    public ?TaxCombination $taxCombination = null;

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

        $this->quantity = new FloatInt(FloatInt::DIVISOR);

        $this->singleListPriceNet   = new FloatInt();
        $this->singleListPriceGross = new FloatInt();

        $this->totalListPriceNet   = new FloatInt();
        $this->totalListPriceGross = new FloatInt();

        $this->singleSalesPriceNet   = new FloatInt();
        $this->singleSalesPriceGross = new FloatInt();

        $this->effectiveSingleSalesPriceNet = new FloatInt();

        $this->totalSalesPriceNet   = new FloatInt();
        $this->totalSalesPriceGross = new FloatInt();

        $this->singlePurchasePriceNet = new FloatInt();
        $this->totalPurchasePriceNet  = new FloatInt();

        $this->singleProfitNet = new FloatInt();
        $this->totalProfitNet  = new FloatInt();

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
        if ($this->quantity->value === $quantity) {
            return;
        }

        $this->quantity->value = $quantity;

        $this->recalculatePrices();
    }

    /**
     * Re-calculate prices.
     *
     * This function is very important to call after changing any prices/quantities
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function recalculatePrices() : void
    {
        $rd = -FloatInt::MAX_DECIMALS + ISO4217DecimalEnum::getByName('_' . $this->bill->currency);

        $this->totalListPriceNet->value  = (int) \round($this->quantity->getNormalizedValue() * $this->singleListPriceNet->value, $rd);
        $this->totalSalesPriceNet->value = (int) \round(($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()) * $this->singleListPriceNet->value, $rd);

        // @todo Check if this is correct, this should maybe happen after applying the discounts?!
        // This depends on if the single price is already discounted or not
        $this->singleProfitNet->value = $this->singleSalesPriceNet->value - $this->singlePurchasePriceNet->value;
        $this->totalProfitNet->value  = $this->totalSalesPriceNet->value - $this->totalPurchasePriceNet->value;

        $this->taxP->value = (int) \round($this->taxR->value / (FloatInt::DIVISOR * 100) * $this->totalSalesPriceNet->value, $rd);

        $this->singleListPriceGross->value  = (int) \round($this->singleListPriceNet->value + $this->singleListPriceNet->value * $this->taxR->value / (FloatInt::DIVISOR * 100), $rd);
        $this->totalListPriceGross->value   = (int) \round($this->totalListPriceNet->value + $this->totalListPriceNet->value * $this->taxR->value / (FloatInt::DIVISOR * 100), $rd);
        $this->singleSalesPriceGross->value = (int) \round($this->singleSalesPriceNet->value + $this->singleSalesPriceNet->value * $this->taxR->value / (FloatInt::DIVISOR * 100), $rd);
        $this->totalSalesPriceGross->value  = (int) \round($this->totalSalesPriceNet->value + $this->totalSalesPriceNet->value * $this->taxR->value / (FloatInt::DIVISOR * 100), $rd);

        $this->singleDiscountP->value = $this->quantity->value - $this->discountQ->value === 0
            ? 0
            : (int) \round($this->totalDiscountP->value / ($this->quantity->getNormalizedValue() - $this->discountQ->getNormalizedValue()));

        // important because the quantity includes $discountQ
        $this->effectiveSingleSalesPriceNet->value = (int) \round($this->totalSalesPriceNet->value / ($this->quantity->value / FloatInt::DIVISOR), $rd);
    }

    /**
     * Validate the correctness of the element
     *
     * @return bool
     *
     * @todo also consider rounding similarly to recalculatePrices
     *
     * @since 1.0.0
     */
    public function isValid() : bool
    {
        return $this->validateNetGross()
            && $this->validateProfit()
            && $this->validateTax()
            && $this->validateTaxRate()
            && $this->validateSingleTotal()
            && $this->validateEffectiveSinglePrice()
            && $this->validateTotalPrice();
    }

    /**
     * Validate the correctness of the net and gross values
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateNetGross() : bool
    {
        return $this->singleListPriceNet->value <= $this->singleListPriceGross->value
            && $this->singleSalesPriceNet->value <= $this->singleSalesPriceGross->value
            && $this->totalListPriceNet->value <= $this->totalListPriceGross->value
            && $this->totalSalesPriceNet->value <= $this->totalSalesPriceGross->value;
    }

    /**
     * Validate the correctness of the profit
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateProfit() : bool
    {
        return $this->totalSalesPriceNet->value - $this->totalPurchasePriceNet->value === $this->totalProfitNet->value;
    }

    /**
     * Validate the correctness of the taxes
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateTax() : bool
    {
        $paidQuantity = $this->quantity->value - $this->discountQ->value;

        return \abs($this->singleListPriceNet->value + ((int) \round($this->taxP->value / ($paidQuantity / FloatInt::DIVISOR), 0)) - $this->singleListPriceGross->value) === 0
            && \abs($this->singleSalesPriceNet->value + ((int) \round($this->taxP->value / ($paidQuantity / FloatInt::DIVISOR), 0)) - $this->singleSalesPriceGross->value) === 0
            && \abs($this->totalListPriceNet->value + $this->taxP->value - $this->totalListPriceGross->value) === 0
            && \abs($this->totalSalesPriceNet->value + $this->taxP->value - $this->totalSalesPriceGross->value) === 0;
    }

    /**
     * Validate the correctness of the tax rate
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateTaxRate() : bool
    {
        return (($this->taxP->value === 0 && $this->taxR->value === 0)
            || (\abs($this->taxP->value / $this->totalSalesPriceNet->value - $this->taxR->value / (FloatInt::DIVISOR * 100)) < 0.001)
            && \abs($this->totalSalesPriceGross->value / $this->totalSalesPriceNet->value - 1.0 - $this->taxR->value / (FloatInt::DIVISOR * 100)) < 0.001);
    }

    /**
     * Validate the correctness of single and total prices
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateSingleTotal() : bool
    {
        $paidQuantity = $this->quantity->value - $this->discountQ->value;

        // Only possible for sales, costs may be different for different lots
        return ((int) \round($this->singleListPriceNet->value * ($this->quantity->value / FloatInt::DIVISOR), 0)) === $this->totalListPriceNet->value
            && ((int) \round($this->singleSalesPriceNet->value * ($paidQuantity / FloatInt::DIVISOR), 0)) === $this->totalSalesPriceNet->value
            && ((int) \round($this->singleDiscountP->value * ($this->quantity->value / FloatInt::DIVISOR), 0)) === $this->totalDiscountP->value;
    }

    /**
     * Validate the correctness of the effective price
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateEffectiveSinglePrice() : bool
    {
        return $this->effectiveSingleSalesPriceNet->value === (int) \round($this->totalSalesPriceNet->value / ($this->quantity->value / FloatInt::DIVISOR));
    }

    /**
     * Validate the correctness of the total price
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateTotalPrice() : bool
    {
        return ((int) \round($this->singleListPriceNet->value * ($this->quantity->value / FloatInt::DIVISOR)
            - $this->singleListPriceNet->value * ($this->quantity->value / FloatInt::DIVISOR) * $this->singleDiscountR->value / (FloatInt::DIVISOR * 100)
            - $this->totalDiscountP->value * ($this->quantity->value / FloatInt::DIVISOR)
            - $this->singleListPriceNet->value * ($this->discountQ->value / FloatInt::DIVISOR), 0))
            === $this->totalSalesPriceNet->value;
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
     * @param Item           $item           Item
     * @param TaxCombination $taxCombination Tax combination
     * @param Bill           $bill           Bill
     * @param int            $quantity       Quantity (1.0 = 10000)
     * @param null|Container $container      Item container
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function fromItem(
        Item $item,
        TaxCombination $taxCombination,
        Bill $bill,
        int $quantity = FloatInt::DIVISOR,
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

        $element->taxR           = new FloatInt($taxCombination->taxCode->percentageInvoice);
        $element->taxCode        = $taxCombination->taxCode->abbr;
        $element->fiAccount      = $taxCombination->account;
        $element->taxCombination = $taxCombination;

        // @todo the purchase price is based on lot/sn/avg prices if available
        $element->singlePurchasePriceNet->value = $item->purchasePrice->value;
        $element->totalPurchasePriceNet->value  = (int) ($element->quantity->getNormalizedValue() * $item->purchasePrice->value);

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
            'item'            => $this->item?->id,
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
