<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Bill type mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class BillTypeL11nMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_type_l11n_id'        => ['name' => 'billing_type_l11n_id',       'type' => 'int',    'internal' => 'id'],
        'billing_type_l11n_name'      => ['name' => 'billing_type_l11n_name',    'type' => 'string', 'internal' => 'name', 'autocomplete' => true],
        'billing_type_l11n_type'      => ['name' => 'billing_type_l11n_type',      'type' => 'int',    'internal' => 'type'],
        'billing_type_l11n_language'  => ['name' => 'billing_type_l11n_language', 'type' => 'string', 'internal' => 'language'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_type_l11n';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='billing_type_l11n_id';
}
