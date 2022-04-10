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

use Modules\Media\Models\CollectionMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Billing mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class BillTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_type_id'                  => ['name' => 'billing_type_id',             'type' => 'int',    'internal' => 'id'],
        'billing_type_number_format'       => ['name' => 'billing_type_number_format',       'type' => 'string',    'internal' => 'numberFormat'],
        'billing_type_template'            => ['name' => 'billing_type_template',       'type' => 'int',    'internal' => 'template'],
        'billing_type_transfer_type'       => ['name' => 'billing_type_transfer_type',  'type' => 'int',    'internal' => 'transferType'],
        'billing_type_transfer_stock'      => ['name' => 'billing_type_transfer_stock', 'type' => 'bool',   'internal' => 'transferStock'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'template' => [
            'mapper'   => CollectionMapper::class,
            'external' => 'billing_type_template',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'            => BillTypeL11nMapper::class,
            'table'             => 'billing_type_l11n',
            'self'              => 'billing_type_l11n_type',
            'column'            => 'name',
            'external'          => null,
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, self:string}>
     * @since 1.0.0
     */
    /*
    public const BELONGS_TO = [
        'owner' => [
            'mapper' => AccountMapper::class,
            'external'   => 'billing_type_owner',
        ],
    ];
    */

    /**
     * Model to use by the mapper.
     *
     * @var string
     * @since 1.0.0
     */
    public const MODEL = BillType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='billing_type_id';
}
