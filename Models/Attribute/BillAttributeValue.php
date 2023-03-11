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

use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;

/**
 * Bill attribute value class.
 *
 * The relation with the type/bill is defined in the BillAttribute class.
 *
 * @package Modules\Billing\Models\Attribute
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class BillAttributeValue implements \JsonSerializable
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Depending attribute type
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $dependingAttributeType = null;

    /**
     * Depending attribute value
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $dependingAttributeValue = null;

    /**
     * Int value
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $valueInt = null;

    /**
     * String value
     *
     * @var null|string
     * @since 1.0.0
     */
    public ?string $valueStr = null;

    /**
     * Decimal value
     *
     * @var null|float
     * @since 1.0.0
     */
    public ?float $valueDec = null;

    /**
     * DateTime value
     *
     * @var null|\DateTimeInterface
     * @since 1.0.0
     */
    public ?\DateTimeInterface $valueDat = null;

    /**
     * Is a default value which can be selected
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isDefault = false;

    /**
     * Unit of the value
     *
     * @var string
     * @since 1.0.0
     */
    public string $unit = '';

    /**
     * Localization
     *
     * @var null|BaseStringL11n
     */
    private ?BaseStringL11n $l11n = null;

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
     * Set l11n
     *
     * @param string|BaseStringL11n $l11n Tag article l11n
     * @param string                $lang Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | BaseStringL11n $l11n, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($l11n instanceof BaseStringL11n) {
            $this->l11n = $l11n;
        } elseif (isset($this->l11n) && $this->l11n instanceof BaseStringL11n) {
            $this->l11n->content  = $l11n;
            $this->l11n->setLanguage($lang);
        } else {
            $this->l11n          = new BaseStringL11n();
            $this->l11n->content = $l11n;
            $this->l11n->ref     = $this->id;
            $this->l11n->setLanguage($lang);
        }
    }

    /**
     * Get localization
     *
     * @return null|string
     *
     * @since 1.0.0
     */
    public function getL11n() : ?string
    {
        return $this->l11n instanceof BaseStringL11n ? $this->l11n->content : $this->l11n;
    }

    /**
     * Set value
     *
     * @param int|string|float $value    Value
     * @param int              $datatype Datatype
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setValue(mixed $value, int $datatype) : void
    {
        if ($datatype === AttributeValueType::_STRING) {
            $this->valueStr = (string) $value;
        } elseif ($datatype === AttributeValueType::_INT
            || $datatype === AttributeValueType::_FLOAT_INT
            || $datatype === AttributeValueType::_BOOL
        ) {
            $this->valueInt = (int) $value;
        } elseif ($datatype === AttributeValueType::_FLOAT) {
            $this->valueDec = (float) $value;
        } elseif ($datatype === AttributeValueType::_DATETIME) {
            $this->valueDat = new \DateTime((string) $value);
        }
    }

    /**
     * Get value
     *
     * @return null|int|string|float|\DateTimeInterface
     *
     * @since 1.0.0
     */
    public function getValue() : mixed
    {
        if (!empty($this->valueStr)) {
            return $this->valueStr;
        } elseif (!empty($this->valueInt)) {
            return $this->valueInt;
        } elseif (!empty($this->valueDec)) {
            return $this->valueDec;
        } elseif ($this->valueDat instanceof \DateTimeInterface) {
            return $this->valueDat;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'        => $this->id,
            'valueInt'  => $this->valueInt,
            'valueStr'  => $this->valueStr,
            'valueDec'  => $this->valueDec,
            'valueDat'  => $this->valueDat,
            'isDefault' => $this->isDefault,
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
