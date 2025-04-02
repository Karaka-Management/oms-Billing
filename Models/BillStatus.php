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
 * Bill status enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class BillStatus extends Enum
{
    public const ACTIVE = 1;

    public const ARCHIVED = 2;

    public const DELETED = 4;

    // Bill is completed and ready to be finalized
    // This means the bill can now be approved (if the workflow is defined that way)
    public const DONE = 8;

    public const DRAFT = 16;

    public const UNPARSED = 32;
}
