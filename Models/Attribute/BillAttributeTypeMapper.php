<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models\Attribute
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Attribute;

use Modules\Attribute\Models\AttributeType;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Bill mapper class.
 *
 * @package Modules\Billing\Models\Attribute
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AttributeType
 * @extends DataMapperFactory<T>
 */
final class BillAttributeTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_attr_type_id'         => ['name' => 'billing_attr_type_id',       'type' => 'int',    'internal' => 'id'],
        'billing_attr_type_name'       => ['name' => 'billing_attr_type_name',     'type' => 'string', 'internal' => 'name', 'autocomplete' => true],
        'billing_attr_type_datatype'   => ['name' => 'billing_attr_type_datatype',   'type' => 'int',    'internal' => 'datatype'],
        'billing_attr_type_fields'     => ['name' => 'billing_attr_type_fields',   'type' => 'int',    'internal' => 'fields'],
        'billing_attr_type_custom'     => ['name' => 'billing_attr_type_custom',   'type' => 'bool',   'internal' => 'custom'],
        'billing_attr_type_repeatable' => ['name' => 'billing_attr_type_repeatable',   'type' => 'bool',   'internal' => 'isRepeatable'],
        'billing_attr_type_internal'   => ['name' => 'billing_attr_type_internal',   'type' => 'bool',   'internal' => 'isInternal'],
        'billing_attr_type_pattern'    => ['name' => 'billing_attr_type_pattern',  'type' => 'string', 'internal' => 'validationPattern'],
        'billing_attr_type_required'   => ['name' => 'billing_attr_type_required', 'type' => 'bool',   'internal' => 'isRequired'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'   => BillAttributeTypeL11nMapper::class,
            'table'    => 'billing_attr_type_l11n',
            'self'     => 'billing_attr_type_l11n_type',
            'column'   => 'content',
            'external' => null,
        ],
        'defaults' => [
            'mapper'   => BillAttributeValueMapper::class,
            'table'    => 'billing_bill_attr_default',
            'self'     => 'billing_bill_attr_default_type',
            'external' => 'billing_bill_attr_default_value',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AttributeType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_attr_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_attr_type_id';
}
