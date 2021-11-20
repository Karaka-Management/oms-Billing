<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class BillElementMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'billing_bill_element_id'                                  => ['name' => 'billing_bill_element_id',      'type' => 'int',    'internal' => 'id'],
        'billing_bill_element_order'                               => ['name' => 'billing_bill_element_order',      'type' => 'int',    'internal' => 'order'],
        'billing_bill_element_item'                                => ['name' => 'billing_bill_element_item',      'type' => 'int',    'internal' => 'item'],
        'billing_bill_element_item_number'                         => ['name' => 'billing_bill_element_item_number',      'type' => 'string',    'internal' => 'itemNumber'],
        'billing_bill_element_item_name'                           => ['name' => 'billing_bill_element_item_name',      'type' => 'string',    'internal' => 'itemName'],
        'billing_bill_element_item_desc'                           => ['name' => 'billing_bill_element_item_desc',      'type' => 'string',    'internal' => 'itemDescription'],
        'billing_bill_element_quantity'                            => ['name' => 'billing_bill_element_quantity',      'type' => 'int',    'internal' => 'quantity'],

        'billing_bill_element_single_netlistprice'               => ['name' => 'billing_bill_element_single_netlistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceNet'],
        'billing_bill_element_single_grosslistprice'               => ['name' => 'billing_bill_element_single_grosslistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceGross'],
        'billing_bill_element_total_netlistprice'                => ['name' => 'billing_bill_element_total_netlistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceNet'],
        'billing_bill_element_total_grosslistprice'                => ['name' => 'billing_bill_element_total_grosslistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceGross'],

        'billing_bill_element_single_netsalesprice'               => ['name' => 'billing_bill_element_single_netsalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceNet'],
        'billing_bill_element_single_grosssalesprice'               => ['name' => 'billing_bill_element_single_grosssalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceGross'],
        'billing_bill_element_total_netsalesprice'                => ['name' => 'billing_bill_element_total_netsalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceNet'],
        'billing_bill_element_total_grosssalesprice'                => ['name' => 'billing_bill_element_total_grosssalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceGross'],

        'billing_bill_element_single_netprofit'               => ['name' => 'billing_bill_element_single_netprofit',      'type' => 'Serializable',    'internal' => 'singleProfitNet'],
        'billing_bill_element_single_grossprofit'               => ['name' => 'billing_bill_element_single_grossprofit',      'type' => 'Serializable',    'internal' => 'singleProfitGross'],
        'billing_bill_element_total_netprofit'                => ['name' => 'billing_bill_element_total_netprofit',      'type' => 'Serializable',    'internal' => 'totalProfitNet'],
        'billing_bill_element_total_grossprofit'                => ['name' => 'billing_bill_element_total_grossprofit',      'type' => 'Serializable',    'internal' => 'totalProfitGross'],

        'billing_bill_element_single_netpurchaseprice'               => ['name' => 'billing_bill_element_single_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'singlePurchasePriceNet'],
        'billing_bill_element_single_grosspurchaseprice'               => ['name' => 'billing_bill_element_single_grosspurchaseprice',      'type' => 'Serializable',    'internal' => 'singlePurchasePriceGross'],
        'billing_bill_element_total_netpurchaseprice'                => ['name' => 'billing_bill_element_total_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'totalPurchasePriceNet'],
        'billing_bill_element_total_grosspurchaseprice'                => ['name' => 'billing_bill_element_total_grosspurchaseprice',      'type' => 'Serializable',    'internal' => 'totalPurchasePriceGross'],
        'billing_bill_element_bill'                                => ['name' => 'billing_bill_element_bill',      'type' => 'int',    'internal' => 'bill'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string}>
     * @since 1.0.0
     */
    protected static array $belongsTo = [
        'bill' => [
            'mapper'     => BillMapper::class,
            'external'   => 'billing_bill_element_bill',
        ],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public static string $primaryField = 'billing_bill_element_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public static string $table = 'billing_bill_element';
}
