<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models\Tax
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models\Tax;

use Modules\ClientManagement\Models\Attribute\ClientAttributeValueMapper;
use Modules\Finance\Models\TaxCodeMapper;
use Modules\ItemManagement\Models\Attribute\ItemAttributeValueMapper;
use Modules\SupplierManagement\Models\Attribute\SupplierAttributeValueMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Billing mapper class.
 *
 * @package Modules\Billing\Models\Tax
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of TaxCombination
 * @extends DataMapperFactory<T>
 */
final class TaxCombinationMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_tax_id'                   => ['name' => 'billing_tax_id',                          'type' => 'int',    'internal' => 'id'],
        'billing_tax_client_code'          => ['name' => 'billing_tax_client_code',           'type' => 'int', 'internal' => 'clientCode'],
        'billing_tax_supplier_code'        => ['name' => 'billing_tax_supplier_code',       'type' => 'int', 'internal' => 'supplierCode'],
        'billing_tax_item_code'            => ['name' => 'billing_tax_item_code',               'type' => 'int', 'internal' => 'itemCode'],
        'billing_tax_code'                 => ['name' => 'billing_tax_code',                         'type' => 'string', 'internal' => 'taxCode'],
        'billing_tax_type'                 => ['name' => 'billing_tax_type',                         'type' => 'int', 'internal' => 'taxType'],
        'billing_tax_account'              => ['name' => 'billing_tax_account',                   'type' => 'string', 'internal' => 'account'],
        'billing_tax_tax1_account'         => ['name' => 'billing_tax_tax1_account',     'type' => 'string', 'internal' => 'taxAccount1'],
        'billing_tax_tax2_account'         => ['name' => 'billing_tax_tax2_account',     'type' => 'string', 'internal' => 'taxAccount2'],
        'billing_tax_refund_account'       => ['name' => 'billing_tax_refund_account',     'type' => 'string', 'internal' => 'refundAccount'],
        'billing_tax_discount_account'     => ['name' => 'billing_tax_discount_account', 'type' => 'string', 'internal' => 'discountAccount'],
        'billing_tax_cashback_account'     => ['name' => 'billing_tax_cashback_account', 'type' => 'string', 'internal' => 'cashbackAccount'],
        'billing_tax_overpayment_account'  => ['name' => 'billing_tax_overpayment_account', 'type' => 'string', 'internal' => 'overpaymentAccount'],
        'billing_tax_underpayment_account' => ['name' => 'billing_tax_underpayment_account', 'type' => 'string', 'internal' => 'underpaymentAccount'],
        'billing_tax_min_price'            => ['name' => 'billing_tax_min_price',               'type' => 'int', 'internal' => 'minPrice'],
        'billing_tax_max_price'            => ['name' => 'billing_tax_max_price',               'type' => 'int', 'internal' => 'maxPrice'],
        'billing_tax_start'                => ['name' => 'billing_tax_start',                       'type' => 'DateTime', 'internal' => 'start'],
        'billing_tax_end'                  => ['name' => 'billing_tax_end',                           'type' => 'DateTime', 'internal' => 'end'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'clientCode' => [
            'mapper'   => ClientAttributeValueMapper::class,
            'external' => 'billing_tax_client_code',
        ],
        'supplierCode' => [
            'mapper'   => SupplierAttributeValueMapper::class,
            'external' => 'billing_tax_supplier_code',
        ],
        'itemCode' => [
            'mapper'   => ItemAttributeValueMapper::class,
            'external' => 'billing_tax_item_code',
        ],
        'taxCode' => [
            'mapper'   => TaxCodeMapper::class,
            'external' => 'billing_tax_code',
            'by'       => 'abbr',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = TaxCombination::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_tax';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_tax_id';
}
