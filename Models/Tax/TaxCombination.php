<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Tax
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Tax;

use Modules\Attribute\Models\AttributeValue;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\Finance\Models\NullTaxCode;
use Modules\Finance\Models\TaxCode;

/**
 * Billing class.
 *
 * @package Modules\Billing\Models\Tax
 * @license OMS License 2.0
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
    public int $id = 0;

    public ?AttributeValue $clientCode = null;

    public ?AttributeValue $supplierCode = null;

    public AttributeValue $itemCode;

    public TaxCode $taxCode;

    public int $taxType = BillTaxType::SALES;

    public string $account = '';

    // Tax accounts can be defined in:
    //      1. Account (gross postings are automatically split)
    //      2. Tax code
    //      3. Tax combination
    public string $taxAccount1 = '';

    public string $taxAccount2 = '';

    public string $refundAccount = '';

    public string $discountAccount = '';

    public string $cashbackAccount = '';

    public string $overpaymentAccount = '';

    public string $underpaymentAccount = '';

    public ?int $minPrice = null;

    public ?int $maxPrice = null;

    public ?\DateTime $start = null;

    public ?\DateTime $end = null;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->itemCode = new NullAttributeValue();
        $this->taxCode  = new NullTaxCode();
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
