<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Tax
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Tax;

use Modules\ClientManagement\Models\ClientAttributeValue;
use Modules\ClientManagement\Models\NullClientAttributeValue;
use Modules\ItemManagement\Models\ItemAttributeValue;
use Modules\ItemManagement\Models\NullItemAttributeValue;
use Modules\SupplierManagement\Models\NullSupplierAttributeValue;
use Modules\SupplierManagement\Models\SupplierAttributeValue;

/**
 * Billing class.
 *
 * @package Modules\Billing\Models\Tax
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaxCombination implements \JsonSerializable
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    public ?ClientAttributeValue $clientCode = null;

    public ?SupplierAttributeValue $supplierCode = null;

    public ItemAttributeValue $itemCode;

    public string $taxCode = '';

    public int $taxType = BillTaxType::SALES;

    public string $account = '';

    public string $refundAccount = '';

    public string $discountAccount = '';

    public ?int $minPrice = null;

    public ?int $maxPrice = null;

    public ?\DateTime $start = null;

    public ?\DateTime $end = null;

    public function __construct()
    {
        $this->itemCode = new NullItemAttributeValue();
    }

    /**
     * Get id
     *
     * @return int
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
            'id'    => $this->id,
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