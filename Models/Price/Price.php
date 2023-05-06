<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Price
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
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
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
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

    public AttributeValue $itemgroup;

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

    public int $quantity = 0;

    public FloatInt $price;

    public int $priceNew = 0;

    public int $discount = 0;

    public int $discountPercentage = 0;

    public int $bonus = 0;

    public bool $multiply = false;

    public string $currency = ISO4217CharEnum::_EUR;

    public ?\DateTime $start = null;

    public ?\DateTime $end = null;

    public function __construct()
    {
        $this->item        = new NullItem();
        $this->itemgroup   = new NullAttributeValue();
        $this->itemsegment = new NullAttributeValue();
        $this->itemsection = new NullAttributeValue();
        $this->itemtype    = new NullAttributeValue();

        $this->client        = new NullClient();
        $this->clientgroup   = new NullAttributeValue();
        $this->clientsegment = new NullAttributeValue();
        $this->clientsection = new NullAttributeValue();
        $this->clienttype    = new NullAttributeValue();

        $this->supplier = new NullSupplier();

        $this->price = new FloatInt();
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
