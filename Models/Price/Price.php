<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Price
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Price;

use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientAttributeValue;
use Modules\ClientManagement\Models\NullClient;
use Modules\ClientManagement\Models\NullClientAttributeValue;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemAttributeValue;
use Modules\ItemManagement\Models\NullItem;
use Modules\ItemManagement\Models\NullItemAttributeValue;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO4217CharEnum;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models\Price
 * @license OMS License 1.0
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
    protected int $id = 0;

    public string $name = '';

    public string $promocode = '';

    public Item $item;

    public ItemAttributeValue $itemgroup;

    public ItemAttributeValue $itemsegment;

    public ItemAttributeValue $itemsection;

    public ItemAttributeValue $itemtype;

    public Client $client;

    public ClientAttributeValue $clientgroup;

    public ClientAttributeValue $clientsegment;

    public ClientAttributeValue $clientsection;

    public ClientAttributeValue $clienttype;

    public ?string $clientcountry = null;

    public Supplier $supplier;

    public int $unit = 0;

    public int $type = PriceType::SALES;

    public int $quantity = 0;

    public int $price = 0;

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
        $this->item = new NullItem();
        $this->itemgroup = new NullItemAttributeValue();
        $this->itemsegment = new NullItemAttributeValue();
        $this->itemsection = new NullItemAttributeValue();
        $this->itemtype = new NullItemAttributeValue();

        $this->client = new NullClient();
        $this->clientgroup = new NullClientAttributeValue();
        $this->clientsegment = new NullClientAttributeValue();
        $this->clientsection = new NullClientAttributeValue();
        $this->clienttype = new NullClientAttributeValue();

        $this->supplier = new NullSupplier();
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
