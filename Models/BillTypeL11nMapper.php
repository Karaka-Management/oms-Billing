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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\Localization\BaseStringL11n;

/**
 * Bill type mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
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
        'billing_type_l11n_name'      => ['name' => 'billing_type_l11n_name',    'type' => 'string', 'internal' => 'content', 'autocomplete' => true],
        'billing_type_l11n_type'      => ['name' => 'billing_type_l11n_type',      'type' => 'int',    'internal' => 'ref'],
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

    /**
     * Model to use by the mapper.
     *
     * @var string
     * @since 1.0.0
     */
    public const MODEL = BaseStringL11n::class;
}
