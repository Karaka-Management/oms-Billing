<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Attribute
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Attribute;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models\Attribute
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class BillAttribute implements \JsonSerializable
{
    /**
     * Id.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Bill this attribute belongs to
     *
     * @var int
     * @since 1.0.0
     */
    public int $bill = 0;

    /**
     * Attribute type the attribute belongs to
     *
     * @var BillAttributeType
     * @since 1.0.0
     */
    public BillAttributeType $type;

    /**
     * Attribute value the attribute belongs to
     *
     * @var BillAttributeValue
     * @since 1.0.0
     */
    public BillAttributeValue $value;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type  = new NullBillAttributeType();
        $this->value = new NullBillAttributeValue();
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
            'bill'  => $this->bill,
            'type'  => $this->type,
            'value' => $this->value,
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
