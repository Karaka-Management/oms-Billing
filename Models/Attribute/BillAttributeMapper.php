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

use Modules\Attribute\Models\Attribute;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Bill mapper class.
 *
 * @package Modules\Billing\Models\Attribute
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class BillAttributeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_bill_attr_id'    => ['name' => 'billing_bill_attr_id',    'type' => 'int', 'internal' => 'id'],
        'billing_bill_attr_bill'  => ['name' => 'billing_bill_attr_bill',  'type' => 'int', 'internal' => 'ref'],
        'billing_bill_attr_type'  => ['name' => 'billing_bill_attr_type',  'type' => 'int', 'internal' => 'type'],
        'billing_bill_attr_value' => ['name' => 'billing_bill_attr_value', 'type' => 'int', 'internal' => 'value'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'type' => [
            'mapper'   => BillAttributeTypeMapper::class,
            'external' => 'billing_bill_attr_type',
        ],
        'value' => [
            'mapper'   => BillAttributeValueMapper::class,
            'external' => 'billing_bill_attr_value',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string
     * @since 1.0.0
     */
    public const MODEL = Attribute::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_bill_attr';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_bill_attr_id';
}
