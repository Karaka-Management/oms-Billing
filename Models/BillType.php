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

use phpOMS\Localization\ISO639x1Enum;

/**
 * Bill type enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class BillType
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    public int $transferType = BillTransferType::SALES;

    public bool $transferStock = true;

    /**
     * Localization
     *
     * @var string|BillTypeL11n
     */
    protected string |

BillTypeL11n $l11n;

    /**
     * Constructor.
     *
     * @param string $name Name/identifier of the attribute type
     *
     * @since 1.0.0
     */
    public function __construct(string $name = '')
    {
        $this->setL11n($name);
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
     * @param string|BillTypeL11n $l11n Tag article l11n
     * @param string              $lang Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n($l11n, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($l11n instanceof BillTypeL11n) {
            $this->l11n = $l11n;
        } elseif (isset($this->l11n) && $this->l11n instanceof BillTypeL11n) {
            $this->l11n->name = $l11n;
        } else {
            $this->l11n       = new BillTypeL11n();
            $this->l11n->name = $l11n;
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
        return $this->l11n instanceof BillTypeL11n ? $this->l11n->name : $this->l11n;
    }
}
