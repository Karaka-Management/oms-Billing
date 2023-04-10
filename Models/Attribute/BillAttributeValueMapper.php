<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Attribute
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Attribute;

use Modules\Attribute\Models\AttributeValue;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Bill mapper class.
 *
 * @package Modules\Billing\Models\Attribute
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AttributeValue
 * @extends DataMapperFactory<T>
 */
final class BillAttributeValueMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_attr_value_id'            => ['name' => 'billing_attr_value_id',       'type' => 'int',      'internal' => 'id'],
        'billing_attr_value_default'       => ['name' => 'billing_attr_value_default',  'type' => 'bool',     'internal' => 'isDefault'],
        'billing_attr_value_valueStr'      => ['name' => 'billing_attr_value_valueStr', 'type' => 'string',   'internal' => 'valueStr'],
        'billing_attr_value_valueInt'      => ['name' => 'billing_attr_value_valueInt', 'type' => 'int',      'internal' => 'valueInt'],
        'billing_attr_value_valueDec'      => ['name' => 'billing_attr_value_valueDec', 'type' => 'float',    'internal' => 'valueDec'],
        'billing_attr_value_valueDat'      => ['name' => 'billing_attr_value_valueDat', 'type' => 'DateTime', 'internal' => 'valueDat'],
        'billing_attr_value_unit'          => ['name' => 'billing_attr_value_unit', 'type' => 'string', 'internal' => 'unit'],
        'billing_attr_value_deptype'          => ['name' => 'billing_attr_value_deptype', 'type' => 'int', 'internal' => 'dependingAttributeType'],
        'billing_attr_value_depvalue'          => ['name' => 'billing_attr_value_depvalue', 'type' => 'int', 'internal' => 'dependingAttributeValue'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'   => BillAttributeValueL11nMapper::class,
            'table'    => 'billing_attr_value_l11n',
            'self'     => 'billing_attr_value_l11n_value',
            'external' => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AttributeValue::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_attr_value';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_attr_value_id';
}
