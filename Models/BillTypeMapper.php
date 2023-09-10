<?php
/**
 * Jingga
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

use Modules\Media\Models\CollectionMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Billing mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of BillType
 * @extends DataMapperFactory<T>
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
        'billing_type_id'                     => ['name' => 'billing_type_id',             'type' => 'int',    'internal' => 'id'],
        'billing_type_name'                   => ['name' => 'billing_type_name',       'type' => 'string',    'internal' => 'name'],
        'billing_type_number_format'          => ['name' => 'billing_type_number_format',       'type' => 'string',    'internal' => 'numberFormat'],
        'billing_type_account_format'         => ['name' => 'billing_type_account_format',       'type' => 'string',    'internal' => 'accountFormat'],
        'billing_type_transfer_type'          => ['name' => 'billing_type_transfer_type',  'type' => 'int',    'internal' => 'transferType'],
        'billing_type_default_template'       => ['name' => 'billing_type_default_template',  'type' => 'int',    'internal' => 'defaultTemplate'],
        'billing_type_transfer_stock'         => ['name' => 'billing_type_transfer_stock', 'type' => 'bool',   'internal' => 'transferStock'],
        'billing_type_sign'         => ['name' => 'billing_type_sign', 'type' => 'bool',   'internal' => 'sign'],
        'billing_type_is_template'            => ['name' => 'billing_type_is_template', 'type' => 'bool',   'internal' => 'isTemplate'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'            => BillTypeL11nMapper::class,
            'table'             => 'billing_type_l11n',
            'self'              => 'billing_type_l11n_type',
            'column'            => 'content',
            'external'          => null,
        ],
        'templates'        => [
            'mapper'   => CollectionMapper::class,
            'table'    => 'billing_bill_type_media_rel',
            'external' => 'billing_bill_type_media_rel_dst',
            'self'     => 'billing_bill_type_media_rel_src',
        ],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'defaultTemplate'  => [
            'mapper'     => CollectionMapper::class,
            'external'   => 'billing_type_default_template',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, self:string}>
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
     * @var class-string<T>
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
    public const PRIMARYFIELD = 'billing_type_id';
}
