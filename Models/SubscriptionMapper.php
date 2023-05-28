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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Subscription
 * @extends DataMapperFactory<T>
 */
final class SubscriptionMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_subscription_id'        => ['name' => 'billing_subscription_id',      'type' => 'int',    'internal' => 'id'],
        'billing_subscription_status'    => ['name' => 'billing_subscription_status',      'type' => 'int',    'internal' => 'status'],
        'billing_subscription_start'     => ['name' => 'billing_subscription_start',      'type' => 'DateTime',    'internal' => 'start'],
        'billing_subscription_end'       => ['name' => 'billing_subscription_end',      'type' => 'DateTime',    'internal' => 'end'],
        'billing_subscription_price'     => ['name' => 'billing_subscription_price',      'type' => 'Serializable',    'internal' => 'price'],
        'billing_subscription_quantity'  => ['name' => 'billing_subscription_quantity',      'type' => 'int',    'internal' => 'quantity'],
        'billing_subscription_bill'      => ['name' => 'billing_subscription_bill',      'type' => 'int',    'internal' => 'bill'],
        'billing_subscription_item'      => ['name' => 'billing_subscription_item',      'type' => 'int',    'internal' => 'item'],
        'billing_subscription_autorenew' => ['name' => 'billing_subscription_autorenew',      'type' => 'bool',    'internal' => 'autoRenew'],
        'billing_subscription_client'    => ['name' => 'billing_subscription_client',      'type' => 'int',    'internal' => 'client'],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_subscription_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_subscription';
}
