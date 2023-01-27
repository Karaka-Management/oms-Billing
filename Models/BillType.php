<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Media\Models\Collection;
use Modules\Media\Models\NullCollection;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;

/**
 * Bill type enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class BillType implements \JsonSerializable
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    public string $name = '';

    public Collection $template;

    public string $numberFormat = '';

    public int $transferType = BillTransferType::SALES;

    public bool $transferStock = true;

    /**
     * Localization
     *
     * @var string|BaseStringL11n
     */
    protected string | BaseStringL11n $l11n;

    public bool $isTemplate = false;

    /**
     * Constructor.
     *
     * @param string $name Name
     *
     * @since 1.0.0
     */
    public function __construct(string $name = '')
    {
        $this->name     = $name;
        $this->template = new NullCollection();
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
     * @return string
     *
     * @since 1.0.0
     */
    public function getL11n() : string
    {
        if (!isset($this->l11n)) {
            return '';
        }

        return $this->l11n instanceof BaseStringL11n ? $this->l11n->content : $this->l11n;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'numberFormat'   => $this->numberFormat,
            'transferType'   => $this->transferType,
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
