<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models\Price
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Price;

use Modules\Attribute\Models\AttributeValue;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\NullClient;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\NullItem;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models\Price
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @todo Find a way to handle references to the total invoice amount and other items
 *      Example: If total invoice > $X no shipping expenses
 *          Maybe additional column referencing total value
 *      Example: If item Y quantity > Z no costs for item A (e.g. service fee)
 *          Maybe by referencing another price (i.e. if other price triggered than this is triggered as well)
 */
class Price implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    public string $name = '';

    public string $promocode = '';

    public Item $item;

    public int $status = PriceStatus::ACTIVE;

    public AttributeValue $itemsalesgroup;

    public AttributeValue $itemproductgroup;

    public AttributeValue $itemsegment;

    public AttributeValue $itemsection;

    public AttributeValue $itemtype;

    public Client $client;

    public AttributeValue $clientgroup;

    public AttributeValue $clientsegment;

    public AttributeValue $clientsection;

    public AttributeValue $clienttype;

    public ?string $clientcountry = null;

    public Supplier $supplier;

    public int $unit = 0;

    public int $type = PriceType::SALES;

    public FloatInt $quantity;

    public FloatInt $price;

    public FloatInt $priceNew;

    public FloatInt $discount;

    public FloatInt $discountPercentage;

    public FloatInt $bonus;

    public bool $multiply = false;

    public bool $isAdditive = false;

    public string $currency = ISO4217CharEnum::_EUR;

    public ?\DateTime $start = null;

    public ?\DateTime $end = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->item             = new NullItem();
        $this->itemsalesgroup   = new NullAttributeValue();
        $this->itemproductgroup = new NullAttributeValue();
        $this->itemsegment      = new NullAttributeValue();
        $this->itemsection      = new NullAttributeValue();
        $this->itemtype         = new NullAttributeValue();

        $this->client        = new NullClient();
        $this->clientgroup   = new NullAttributeValue();
        $this->clientsegment = new NullAttributeValue();
        $this->clientsection = new NullAttributeValue();
        $this->clienttype    = new NullAttributeValue();

        $this->supplier = new NullSupplier();

        $this->price              = new FloatInt();
        $this->quantity           = new FloatInt();
        $this->priceNew           = new FloatInt();
        $this->discount           = new FloatInt();
        $this->discountPercentage = new FloatInt();
        $this->bonus              = new FloatInt();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id' => $this->id,
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
