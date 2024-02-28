<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Media\Models\Collection;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;

/**
 * Bill type enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
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
    public int $id = 0;

    public string $name = '';

    public array $templates = [];

    public ?Collection $defaultTemplate = null;

    public string $numberFormat = '';

    public string $accountFormat = '';

    public int $transferType = BillTransferType::SALES;

    public bool $transferStock = true;

    public bool $isAccounting = false;

    public int $sign = 1;

    public bool $email = false;

    /**
     * Localization
     *
     * @var string|BaseStringL11n
     */
    public string | BaseStringL11n $l11n;

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
        $this->name = $name;
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
            $this->l11n->language = $lang;
        } else {
            $this->l11n           = new BaseStringL11n();
            $this->l11n->content  = $l11n;
            $this->l11n->ref      = $this->id;
            $this->l11n->language = $lang;
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
     * Add rendering template
     *
     * @param Collection $template Template
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addTemplate(Collection $template) : void
    {
        $this->templates[] = $template;
    }

    /**
     * Get templates
     *
     * @return Collection[]
     *
     * @since 1.0.0
     */
    public function getTemplates() : array
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'           => $this->id,
            'numberFormat' => $this->numberFormat,
            'transferType' => $this->transferType,
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
