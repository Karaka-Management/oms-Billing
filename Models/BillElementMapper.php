<?php
/**
 * Karaka
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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
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
        'billing_bill_element_id'                        => ['name' => 'billing_bill_element_id',      'type' => 'int',    'internal' => 'id'],
        'billing_bill_element_order'                     => ['name' => 'billing_bill_element_order',      'type' => 'int',    'internal' => 'order'],
        'billing_bill_element_item'                      => ['name' => 'billing_bill_element_item',      'type' => 'int',    'internal' => 'item'],
        'billing_bill_element_item_number'               => ['name' => 'billing_bill_element_item_number',      'type' => 'string',    'internal' => 'itemNumber'],
        'billing_bill_element_item_name'                 => ['name' => 'billing_bill_element_item_name',      'type' => 'string',    'internal' => 'itemName'],
        'billing_bill_element_item_desc'                 => ['name' => 'billing_bill_element_item_desc',      'type' => 'string',    'internal' => 'itemDescription'],
        'billing_bill_element_quantity'                  => ['name' => 'billing_bill_element_quantity',      'type' => 'int',    'internal' => 'quantity'],

        'billing_bill_element_single_netlistprice'       => ['name' => 'billing_bill_element_single_netlistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceNet'],
        'billing_bill_element_single_grosslistprice'     => ['name' => 'billing_bill_element_single_grosslistprice',      'type' => 'Serializable',    'internal' => 'singleListPriceGross'],
        'billing_bill_element_total_netlistprice'        => ['name' => 'billing_bill_element_total_netlistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceNet'],
        'billing_bill_element_total_grosslistprice'      => ['name' => 'billing_bill_element_total_grosslistprice',      'type' => 'Serializable',    'internal' => 'totalListPriceGross'],

        'billing_bill_element_single_netsalesprice'      => ['name' => 'billing_bill_element_single_netsalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceNet'],
        'billing_bill_element_single_grosssalesprice'    => ['name' => 'billing_bill_element_single_grosssalesprice',      'type' => 'Serializable',    'internal' => 'singleSalesPriceGross'],
        'billing_bill_element_total_netsalesprice'       => ['name' => 'billing_bill_element_total_netsalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceNet'],
        'billing_bill_element_total_grosssalesprice'     => ['name' => 'billing_bill_element_total_grosssalesprice',      'type' => 'Serializable',    'internal' => 'totalSalesPriceGross'],

        'billing_bill_element_single_netprofit'          => ['name' => 'billing_bill_element_single_netprofit',      'type' => 'Serializable',    'internal' => 'singleProfitNet'],
        'billing_bill_element_single_grossprofit'        => ['name' => 'billing_bill_element_single_grossprofit',      'type' => 'Serializable',    'internal' => 'singleProfitGross'],
        'billing_bill_element_total_netprofit'           => ['name' => 'billing_bill_element_total_netprofit',      'type' => 'Serializable',    'internal' => 'totalProfitNet'],
        'billing_bill_element_total_grossprofit'         => ['name' => 'billing_bill_element_total_grossprofit',      'type' => 'Serializable',    'internal' => 'totalProfitGross'],

        'billing_bill_element_single_netpurchaseprice'   => ['name' => 'billing_bill_element_single_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'singlePurchasePriceNet'],
        'billing_bill_element_single_grosspurchaseprice' => ['name' => 'billing_bill_element_single_grosspurchaseprice',      'type' => 'Serializable',    'internal' => 'singlePurchasePriceGross'],
        'billing_bill_element_total_netpurchaseprice'    => ['name' => 'billing_bill_element_total_netpurchaseprice',      'type' => 'Serializable',    'internal' => 'totalPurchasePriceNet'],
        'billing_bill_element_total_grosspurchaseprice'  => ['name' => 'billing_bill_element_total_grosspurchaseprice',      'type' => 'Serializable',    'internal' => 'totalPurchasePriceGross'],
        'billing_bill_element_bill'                      => ['name' => 'billing_bill_element_bill',      'type' => 'int',    'internal' => 'bill'],

        'billing_bill_element_tax_type'                      => ['name' => 'billing_bill_element_tax_type',      'type' => 'string',    'internal' => 'taxCode'],
        'billing_bill_element_tax_price'                      => ['name' => 'billing_bill_element_tax_price',      'type' => 'Serializable',    'internal' => 'taxP'],
        'billing_bill_element_tax_percentage'                      => ['name' => 'billing_bill_element_tax_percentage',      'type' => 'Serializable',    'internal' => 'taxR'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
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
    public const PRIMARYFIELD = 'billing_bill_element_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_bill_element';
}
