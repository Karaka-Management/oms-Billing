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
use phpOMS\DataStorage\Database\Query\Where;

/**
 * Billing mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class PricingMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_type_id'                  => ['name' => 'billing_type_id',             'type' => 'int',    'internal' => 'id'],
        'billing_type_name'                => ['name' => 'billing_type_name',       'type' => 'string',    'internal' => 'name'],
        'billing_type_number_format'       => ['name' => 'billing_type_number_format',       'type' => 'string',    'internal' => 'numberFormat'],
        'billing_type_transfer_type'       => ['name' => 'billing_type_transfer_type',  'type' => 'int',    'internal' => 'transferType'],
        'billing_type_default_template'       => ['name' => 'billing_type_default_template',  'type' => 'int',    'internal' => 'defaultTemplate'],
        'billing_type_transfer_stock'      => ['name' => 'billing_type_transfer_stock', 'type' => 'bool',   'internal' => 'transferStock'],
        'billing_type_is_template'      => ['name' => 'billing_type_is_template', 'type' => 'bool',   'internal' => 'isTemplate'],
    ];

        /**
     * Model to use by the mapper.
     *
     * @var class-string
     * @since 1.0.0
     */
    public const MODEL = Pricing::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_price';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='billing_price_id';

    public static function findClientPrice() : array
    {
        /*
        select * from prices
            where
                (promoID = ? OR promoID = null)
                AND (itemID = ? OR itemID = null)
                AND (itemGroup = IN (?) OR itemGroup = null)
                AND (itemSegment = ? OR itemSegment = null)
                AND (itemSection = ? OR itemSection = null)
                AND (productType = ? OR productType = null)
                AND (customerID = ? OR customerID = null)
                AND (customerGroup IN (?) OR customerGroup = null)
                AND (customerCountry = IN (?) OR customerCountry = null)
                AND (quantity < ? OR quantity = null)
                AND (start <= ? OR start = null)
                AND (end >= ? OR start = null)
                AND (unit = ? OR unit = null)
        */

        // @todo: allow nested where clause (already possible with the query builder, but not with the mappers)

        return [];
    }
}
