<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models\Price
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Price;

use phpOMS\Stdlib\Base\Enum;

/**
 * Module settings enum.
 *
 * @package  Modules\Billing\Models\Price
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class PriceStatus extends Enum
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;
}
