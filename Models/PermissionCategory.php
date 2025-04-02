<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Permission category enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PermissionCategory extends Enum
{
    public const SALES_INVOICE = 1;

    public const PURCHASE_INVOICE = 2;

    public const SALES_ANALYSIS = 4;

    public const PRIVATE_DASHBOARD = 5;

    public const PRIVATE_BILL_UPLOAD = 6;

    public const BILL_NOTE = 7;

    public const PRICE = 8;

    public const PAYMENT_TERM = 9;

    public const SHIPPING_TERM = 10;

    public const BILL_LOG = 101;

    public const TAX = 201;
}
