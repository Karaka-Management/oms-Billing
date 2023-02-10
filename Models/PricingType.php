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

use phpOMS\Stdlib\Base\Enum;

/**
 * Module settings enum.
 *
 * @package  Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PricingType extends Enum
{
    public const SALES = 1;

    public const PURCHASE = 2;
}
