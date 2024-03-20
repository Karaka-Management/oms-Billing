<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models\Tax
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Tax;

use phpOMS\Stdlib\Base\Enum;

/**
 * Bill transfer type enum.
 *
 * @package Modules\Billing\Models\Tax
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class BillTaxType extends Enum
{
    public const SALES = 1;

    public const PURCHASE = 2;
}
