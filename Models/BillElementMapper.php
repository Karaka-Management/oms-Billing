<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ItemManagement\Models\ContainerMapper;
use Modules\ItemManagement\Models\ItemMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Billelement mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of BillElement
 * @extends DataMapperFactory<T>
 */
final class BillElementMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_bill_element_id'          => ['name' => 'billing_bill_element_id',      'type' => 'int',    'internal' => 'id'],
        'billing_bill_element_order'       => ['name' => 'billing_bill_element_order',      'type' => 'int',    'internal' => 'order'],
        'billing_bill_element_item'        => ['name' => 'billing_bill_element_item',      'type' => 'int',    'internal' => 'item'],
        'billing_bill_element_container'   => ['name' => 'billing_bill_element_container',      'type' => 'int',    'internal' => 'container'],
        'billing_bill_element_item_number' => ['name' => 'billing_bill_element_item_number',      'type' => 'string',    'internal' => 'itemNumber'],
        'billing_bill_element_item_name'   => ['name' => 'billing_bill_element_item_name',      'type' => 'string',    'internal' => 'itemName'],
        'billing_bill_element_item_desc'   => ['name' => 'billing_bill_element_item_desc',      'type' => 'string',    'internal' => 'itemDescription'],
        'billing_bill_element_quantity'    => ['name' => 'billing_bill_element_quantity',      'type' => 'Serializable',    'internal' => 'quantity', 'private' => true],

        'billing_bill_element_single_netlistprice'   => ['name' => 'billing_bill_element_single_netlistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceNet'],
        'billing_bill_element_single_grosslistprice' => ['name' => 'billing_bill_element_single_grosslistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceGross'],
        'billing_bill_element_total_netlistprice'    => ['name' => 'billing_bill_element_total_netlistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceNet'],
        'billing_bill_element_total_grosslistprice'  => ['name' => 'billing_bill_element_total_grosslistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceGross'],

        'billing_bill_element_single_netsalesprice'          => ['name' => 'billing_bill_element_single_netsalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceNet'],
        'billing_bill_element_single_effectivenetsalesprice' => ['name' => 'billing_bill_element_single_effectivenetsalesprice',      'type' => 'Serializable',    'internal' => 'effectiveSingleSalesPriceNet'],
        'billing_bill_element_single_grosssalesprice'        => ['name' => 'billing_bill_element_single_grosssalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceGross'],
        'billing_bill_element_total_netsalesprice'           => ['name' => 'billing_bill_element_total_netsalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceNet'],
        'billing_bill_element_total_grosssalesprice'         => ['name' => 'billing_bill_element_total_grosssalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceGross'],

        'billing_bill_element_single_netprofit' => ['name' => 'billing_bill_element_single_netprofit',      'type' => 'Serializable',    'internal' => 'singleProfitNet'],
        'billing_bill_element_total_netprofit'  => ['name' => 'billing_bill_element_total_netprofit',      'type' => 'Serializable',    'internal' => 'totalProfitNet'],

        'billing_bill_element_single_netpurchaseprice' => ['name' => 'billing_bill_element_single_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'singlePurchasePriceNet'],
        'billing_bill_element_total_netpurchaseprice'  => ['name' => 'billing_bill_element_total_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'totalPurchasePriceNet'],
        'billing_bill_element_bill'                    => ['name' => 'billing_bill_element_bill',      'type' => 'int',    'internal' => 'bill'],

        'billing_bill_element_tax_combination' => ['name' => 'billing_bill_element_tax_combination',      'type' => 'int',    'internal' => 'taxCombination'],
        'billing_bill_element_tax_type'        => ['name' => 'billing_bill_element_tax_type',      'type' => 'string',    'internal' => 'taxCode'],
        'billing_bill_element_tax_price'       => ['name' => 'billing_bill_element_tax_price',      'type' => 'Serializable',    'internal' => 'taxP'],
        'billing_bill_element_tax_percentage'  => ['name' => 'billing_bill_element_tax_percentage',      'type' => 'Serializable',    'internal' => 'taxR'],

        'billing_bill_element_segment'      => ['name' => 'billing_bill_element_segment',      'type' => 'int',    'internal' => 'itemSegment'],
        'billing_bill_element_section'      => ['name' => 'billing_bill_element_section',      'type' => 'int',    'internal' => 'itemSection'],
        'billing_bill_element_salesgroup'   => ['name' => 'billing_bill_element_salesgroup',      'type' => 'int',    'internal' => 'itemSalesGroup'],
        'billing_bill_element_productgroup' => ['name' => 'billing_bill_element_productgroup',      'type' => 'int',    'internal' => 'itemProductGroup'],
        'billing_bill_element_itemtype'     => ['name' => 'billing_bill_element_itemtype',      'type' => 'int',    'internal' => 'itemType'],

        'billing_bill_element_fiaccount'  => ['name' => 'billing_bill_element_fiaccount',      'type' => 'string',    'internal' => 'fiAccount'],
        'billing_bill_element_costcenter' => ['name' => 'billing_bill_element_costcenter',      'type' => 'string',    'internal' => 'costcenter'],
        'billing_bill_element_costobject' => ['name' => 'billing_bill_element_costobject',      'type' => 'string',    'internal' => 'costobject'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'bill' => [
            'mapper'   => BillMapper::class,
            'external' => 'billing_bill_element_bill',
        ],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'subscription' => [
            'mapper'   => SubscriptionMapper::class,
            'external' => 'billing_bill_element_subscription',
        ],
        'item' => [
            'mapper'   => ItemMapper::class,
            'external' => 'billing_bill_element_item',
        ],
        'container' => [
            'mapper'   => ContainerMapper::class,
            'external' => 'billing_bill_element_container',
        ],
        'taxCombination' => [
            'mapper'   => TaxCombinationMapper::class,
            'external' => 'billing_bill_element_tax_combination',
        ],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_bill_element_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_bill_element';
}
