<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use phpOMS\Localization\Money;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
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

    public ?int $item = null;

    public string $itemNumber = '';

    public string $itemName = '';

    public string $itemDescription = '';

    public int $quantity = 0;

    public Money $singleSalesPriceNet;

    public Money $totalSalesPriceNet;

    public ?FloatInt $singleDiscountP = null;

    public ?FloatInt $totalDiscountP = null;

    public ?FloatInt $singleDiscountR = null;

    public ?FloatInt $discountQ = null;

    public ?FloatInt $singlePriceNet = null;

    public ?FloatInt $totalPriceNet = null;

    public Money $singlePurchasePriceNet;

    public Money $totalPurchasePriceNet;

    public ?FloatInt $taxP = null;

    public ?FloatInt $taxR = null;

    public ?FloatInt $singleSalesPriceGross = null;

    public ?FloatInt $totalSalesPriceGross = null;

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
        $this->singleSalesPriceNet = new Money();
        $this->totalSalesPriceNet  = new Money();

        $this->singlePurchasePriceNet = new Money();
        $this->totalPurchasePriceNet  = new Money();
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
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
