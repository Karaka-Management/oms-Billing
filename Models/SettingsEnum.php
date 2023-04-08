<?php
/**
 * Karaka
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

use phpOMS\Stdlib\Base\Enum;

/**
 * Module settings enum.
 *
 * @package  Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class SettingsEnum extends Enum
{
    public const PREVIEW_MEDIA_TYPE = '1005100001'; // internally generated preview

    public const ORIGINAL_MEDIA_TYPE = '1005100002'; // original document (mostly supplier invoice/delivery note)

    public const VALID_BILL_LANGUAGES = '1005100003'; // List of valid languages for bills
}
