<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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

    public const INTERNAL_MEDIA_TYPE = '1005100002'; // original document (client = invoice sent to client, supplier = invoice from supplier)

    public const EXTERNAL_MEDIA_TYPE = '1005100006'; // original document (client = invoice sent to client, supplier = invoice from supplier)

    public const VALID_BILL_LANGUAGES = '1005100003'; // List of valid languages for bills

    public const BILLING_CUSTOMER_EMAIL_TEMPLATE = '1005100004'; // Email template for customer billing

    public const BILLING_SUPPLIER_EMAIL_TEMPLATE = '1005100005'; // Email template for supplier billing

    public const BILLING_DOCUMENT_SPACER_COLOR = '1005100101';

    public const BILLING_DOCUMENT_SPACER_TOLERANCE = '1005100102';
}
