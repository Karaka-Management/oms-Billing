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
 * Workflow type enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class WorkflowStepStatusEnum extends Enum
{
    // The price/profit of the element needs approval
    public const BILL_ELEMENT_PRICE_APPROVAL = 1 << 0;

    // Large quantity of element is large and needs to be approved
    public const BILL_ELEMENT_QUANTITY_APPROVAL = 1 << 1;

    // Large quantity of element is large and needs to be approved
    public const BILL_ELEMENT_PROFIT_APPROVAL = 1 << 2;

    // The element is completely approved
    public const BILL_ELEMENT_APPROVAL = 1 << 3;

    // The bill got checked for correctness
    public const BILL_CORRECTNESS_APPROVAL = 1 << 4;

    // The total price is approved (e.g. large orders)
    public const BILL_PRICE_APPROVAL = 1 << 5;

    // The profit of the bill is below a certain threshold
    public const BILL_PROFIT_APPROVAL = 1 << 6;

    // The payment term is worse than the default term of the customer
    public const BILL_PAYMENT_TERM_APPROVAL = 1 << 7;

    // Approval despite credit limit reached
    public const BILL_CREDIT_LIMIT_APPROVAL = 1 << 8;

    // Allows bill to be paid
    public const BILL_PAYMENT_APPROVAL = 1 << 9;

    // The bill is completely approved
    public const BILL_APPROVAL = 1 << 10;
}
