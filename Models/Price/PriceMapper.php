<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models\Price
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Price;

use Modules\ClientManagement\Models\ClientAttributeValueMapper;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ItemManagement\Models\ItemAttributeValueMapper;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\Localization\Defaults\CountryMapper;

/**
 * Billing mapper class.
 *
 * @package Modules\Billing\Models\Price
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Price
 * @extends DataMapperFactory<T>
 */
final class PriceMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_price_id'                => ['name' => 'billing_price_id',               'type' => 'int',      'internal' => 'id'],
        'billing_price_name'              => ['name' => 'billing_price_name',          'type' => 'string',   'internal' => 'name'],
        'billing_price_promocode'         => ['name' => 'billing_price_promocode',     'type' => 'string',   'internal' => 'promocode'],
        'billing_price_item'              => ['name' => 'billing_price_item',          'type' => 'int',      'internal' => 'item'],
        'billing_price_itemgroup'         => ['name' => 'billing_price_itemgroup',     'type' => 'int',      'internal' => 'itemgroup'],
        'billing_price_itemsegment'       => ['name' => 'billing_price_itemsegment',   'type' => 'int',      'internal' => 'itemsegment'],
        'billing_price_itemsection'       => ['name' => 'billing_price_itemsection',   'type' => 'int',      'internal' => 'itemsection'],
        'billing_price_itemtype'          => ['name' => 'billing_price_itemtype',      'type' => 'int',      'internal' => 'itemtype'],
        'billing_price_client'            => ['name' => 'billing_price_client',        'type' => 'int',      'internal' => 'client'],
        'billing_price_clientgroup'       => ['name' => 'billing_price_clientgroup',   'type' => 'int',      'internal' => 'clientgroup'],
        'billing_price_clientsegment'     => ['name' => 'billing_price_clientsegment', 'type' => 'int',      'internal' => 'clientsegment'],
        'billing_price_clientsection'     => ['name' => 'billing_price_clientsection', 'type' => 'int',      'internal' => 'clientsection'],
        'billing_price_clienttype'        => ['name' => 'billing_price_clienttype',    'type' => 'int',      'internal' => 'clienttype'],
        'billing_price_clientcountry'     => ['name' => 'billing_price_clientcountry', 'type' => 'string',   'internal' => 'clientcountry'],
        'billing_price_supplier'          => ['name' => 'billing_price_supplier',      'type' => 'int',      'internal' => 'supplier'],
        'billing_price_unit'              => ['name' => 'billing_price_unit',          'type' => 'int',      'internal' => 'unit'],
        'billing_price_type'              => ['name' => 'billing_price_type',          'type' => 'int',      'internal' => 'type'],
        'billing_price_quantity'          => ['name' => 'billing_price_quantity',      'type' => 'int',      'internal' => 'quantity'],
        'billing_price_price'             => ['name' => 'billing_price_price',         'type' => 'Serializable',      'internal' => 'price'],
        'billing_price_price_new'         => ['name' => 'billing_price_price_new',         'type' => 'int',      'internal' => 'priceNew'],
        'billing_price_discount'          => ['name' => 'billing_price_discount',      'type' => 'int',      'internal' => 'discount'],
        'billing_price_discountp'         => ['name' => 'billing_price_discountp',     'type' => 'int',      'internal' => 'discountPercentage'],
        'billing_price_bonus'             => ['name' => 'billing_price_bonus',         'type' => 'int',      'internal' => 'bonus'],
        'billing_price_multiply'          => ['name' => 'billing_price_multiply',      'type' => 'bool',     'internal' => 'multiply'],
        'billing_price_currency'          => ['name' => 'billing_price_currency',      'type' => 'string',   'internal' => 'currency'],
        'billing_price_start'             => ['name' => 'billing_price_start',         'type' => 'DateTime', 'internal' => 'start'],
        'billing_price_end'               => ['name' => 'billing_price_end',           'type' => 'DateTime', 'internal' => 'end'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'item' => [
            'mapper'   => ItemMapper::class,
            'external' => 'billing_price_item',
        ],
        'itemgroup' => [
            'mapper'   => ItemAttributeValueMapper::class,
            'external' => 'billing_price_itemgroup',
        ],
        'itemsegment' => [
            'mapper'   => ItemAttributeValueMapper::class,
            'external' => 'billing_price_itemsegment',
        ],
        'itemsection' => [
            'mapper'   => ItemAttributeValueMapper::class,
            'external' => 'billing_price_itemsection',
        ],
        'itemtype' => [
            'mapper'   => ItemAttributeValueMapper::class,
            'external' => 'billing_price_itemtype',
        ],
        'client' => [
            'mapper'   => ClientMapper::class,
            'external' => 'billing_price_client',
        ],
        'clientgroup' => [
            'mapper'   => ClientAttributeValueMapper::class,
            'external' => 'billing_price_clientgroup',
        ],
        'clientsegment' => [
            'mapper'   => ClientAttributeValueMapper::class,
            'external' => 'billing_price_clientsegment',
        ],
        'clientsection' => [
            'mapper'   => ClientAttributeValueMapper::class,
            'external' => 'billing_price_clientsection',
        ],
        'clienttype' => [
            'mapper'   => ClientAttributeValueMapper::class,
            'external' => 'billing_price_clienttype',
        ],
        'clientcountry' => [
            'mapper'      => CountryMapper::class,
            'external'    => 'billing_price_clientcountry',
            'by'          => 'code2',
            'column'      => 'code2',
            'conditional' => true,
        ],
        'supplier' => [
            'mapper'   => SupplierMapper::class,
            'external' => 'billing_price_supplier',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Price::class;

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
    public const PRIMARYFIELD = 'billing_price_id';

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
