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

use Modules\Admin\Models\AccountMapper;
use Modules\Billing\Models\Attribute\BillAttributeMapper;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\Editor\Models\EditorDocMapper;
use Modules\Media\Models\MediaMapper;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * WARNING: This mapper may use a trigger to update the sequence number on insert.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Bill
 * @extends DataMapperFactory<T>
 */
class BillMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'billing_bill_id'                => ['name' => 'billing_bill_id',      'type' => 'int',    'internal' => 'id'],
        'billing_bill_sequence'          => ['name' => 'billing_bill_sequence',      'type' => 'int',    'internal' => 'sequence'],
        'billing_bill_number'            => ['name' => 'billing_bill_number',      'type' => 'string',    'internal' => 'number'],
        'billing_bill_external'          => ['name' => 'billing_bill_external',      'type' => 'string',    'internal' => 'external'],
        'billing_bill_type'              => ['name' => 'billing_bill_type',      'type' => 'int',    'internal' => 'type'],
        'billing_bill_template'          => ['name' => 'billing_bill_template',      'type' => 'bool',    'internal' => 'isTemplate'],
        'billing_bill_archived'          => ['name' => 'billing_bill_archived',      'type' => 'bool',    'internal' => 'isArchived'],
        'billing_bill_header'            => ['name' => 'billing_bill_header',      'type' => 'string',    'internal' => 'header'],
        'billing_bill_footer'            => ['name' => 'billing_bill_footer',      'type' => 'string',    'internal' => 'footer'],
        'billing_bill_info'              => ['name' => 'billing_bill_info',      'type' => 'string',    'internal' => 'info'],
        'billing_bill_status'            => ['name' => 'billing_bill_status',      'type' => 'int',    'internal' => 'status'],
        'billing_bill_paymentstatus'     => ['name' => 'billing_bill_paymentstatus',      'type' => 'int',    'internal' => 'paymentStatus'],
        'billing_bill_shipTo'            => ['name' => 'billing_bill_shipTo',      'type' => 'string',    'internal' => 'shipTo'],
        'billing_bill_shipFAO'           => ['name' => 'billing_bill_shipFAO',      'type' => 'string',    'internal' => 'shipFAO'],
        'billing_bill_shipAddr'          => ['name' => 'billing_bill_shipAddr',      'type' => 'string',    'internal' => 'shipAddress'],
        'billing_bill_shipCity'          => ['name' => 'billing_bill_shipCity',      'type' => 'string',    'internal' => 'shipCity'],
        'billing_bill_shipZip'           => ['name' => 'billing_bill_shipZip',      'type' => 'string',    'internal' => 'shipZip'],
        'billing_bill_shipCountry'       => ['name' => 'billing_bill_shipCountry',      'type' => 'string',    'internal' => 'shipCountry'],
        'billing_bill_billTo'            => ['name' => 'billing_bill_billTo',      'type' => 'string',    'internal' => 'billTo'],
        'billing_bill_billFAO'           => ['name' => 'billing_bill_billFAO',      'type' => 'string',    'internal' => 'billFAO'],
        'billing_bill_billAddr'          => ['name' => 'billing_bill_billAddr',      'type' => 'string',    'internal' => 'billAddress'],
        'billing_bill_billCity'          => ['name' => 'billing_bill_billCity',      'type' => 'string',    'internal' => 'billCity'],
        'billing_bill_billZip'           => ['name' => 'billing_bill_billZip',      'type' => 'string',    'internal' => 'billZip'],
        'billing_bill_billCountry'       => ['name' => 'billing_bill_billCountry',      'type' => 'string',    'internal' => 'billCountry'],
        'billing_bill_netprofit'         => ['name' => 'billing_bill_netprofit',      'type' => 'Serializable',    'internal' => 'netProfit'],
        'billing_bill_netcosts'          => ['name' => 'billing_bill_netcosts',      'type' => 'Serializable',    'internal' => 'netCosts'],
        'billing_bill_netsales'          => ['name' => 'billing_bill_netsales',      'type' => 'Serializable',    'internal' => 'netSales'],
        'billing_bill_grosssales'        => ['name' => 'billing_bill_grosssales',      'type' => 'Serializable',    'internal' => 'grossSales'],
        'billing_bill_netdiscount'       => ['name' => 'billing_bill_netdiscount',      'type' => 'Serializable',    'internal' => 'netDiscount'],
        'billing_bill_taxp'              => ['name' => 'billing_bill_taxp',      'type' => 'Serializable',    'internal' => 'taxP'],
        'billing_bill_fiaccount'         => ['name' => 'billing_bill_fiaccount',      'type' => 'string',    'internal' => 'fiAccount'],
        'billing_bill_currency'          => ['name' => 'billing_bill_currency',      'type' => 'string',    'internal' => 'currency'],
        'billing_bill_language'          => ['name' => 'billing_bill_language',      'type' => 'string',    'internal' => 'language'],
        'billing_bill_referral'          => ['name' => 'billing_bill_referral',      'type' => 'int',    'internal' => 'referral'],
        'billing_bill_reference'         => ['name' => 'billing_bill_reference',      'type' => 'int',    'internal' => 'reference'],
        'billing_bill_accsegment'        => ['name' => 'billing_bill_accsegment',      'type' => 'int',    'internal' => 'accSegment'],
        'billing_bill_accsection'        => ['name' => 'billing_bill_accsection',      'type' => 'int',    'internal' => 'accSection'],
        'billing_bill_accgroup'          => ['name' => 'billing_bill_accgroup',      'type' => 'int',    'internal' => 'accGroup'],
        'billing_bill_acctype'           => ['name' => 'billing_bill_acctype',      'type' => 'int',    'internal' => 'accType'],
        'billing_bill_payment'           => ['name' => 'billing_bill_payment',      'type' => 'int',    'internal' => 'payment'],
        'billing_bill_payment_text'      => ['name' => 'billing_bill_payment_text',      'type' => 'string',    'internal' => 'paymentText'],
        'billing_bill_paymentterms'      => ['name' => 'billing_bill_paymentterms',      'type' => 'int',    'internal' => 'paymentTerms'],
        'billing_bill_paymentterms_text' => ['name' => 'billing_bill_paymentterms_text',      'type' => 'string',    'internal' => 'termsText'],
        'billing_bill_ship_type'         => ['name' => 'billing_bill_ship_type',      'type' => 'int',    'internal' => 'shippingTerms'],
        'billing_bill_ship_text'         => ['name' => 'billing_bill_ship_text',      'type' => 'string',    'internal' => 'shippingText'],
        'billing_bill_account_no'        => ['name' => 'billing_bill_account_no', 'type' => 'string',      'internal' => 'accountNumber'],
        'billing_bill_tax_type'          => ['name' => 'billing_bill_tax_type', 'type' => 'int',      'internal' => 'accTaxCode'],
        'billing_bill_client'            => ['name' => 'billing_bill_client', 'type' => 'int',      'internal' => 'client'],
        'billing_bill_supplier'          => ['name' => 'billing_bill_supplier', 'type' => 'int',      'internal' => 'supplier'],
        'billing_bill_created_by'        => ['name' => 'billing_bill_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'billing_bill_date'              => ['name' => 'billing_bill_date', 'type' => 'DateTime', 'internal' => 'billDate'],
        'billing_bill_performance_date'  => ['name' => 'billing_bill_performance_date', 'type' => 'DateTime', 'internal' => 'performanceDate', 'readonly' => true],
        'billing_bill_created_at'        => ['name' => 'billing_bill_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'billing_bill_unit'              => ['name' => 'billing_bill_unit', 'type' => 'int', 'internal' => 'unit'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'elements' => [
            'mapper'   => BillElementMapper::class,
            'table'    => 'billing_bill_element',
            'self'     => 'billing_bill_element_bill',
            'external' => null,
        ],
        'files' => [
            'mapper'   => MediaMapper::class,
            'table'    => 'billing_bill_media',
            'external' => 'billing_bill_media_dst',
            'self'     => 'billing_bill_media_src',
        ],
        'notes' => [
            'mapper'   => EditorDocMapper::class,            /* mapper of the related object */
            'table'    => 'billing_bill_note',         /* table of the related object, null if no relation table is used (many->1) */
            'external' => 'billing_bill_note_doc',
            'self'     => 'billing_bill_note_bill',
        ],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'type' => [
            'mapper'   => BillTypeMapper::class,
            'external' => 'billing_bill_type',
        ],
        'referral' => [
            'mapper'   => AccountMapper::class,
            'external' => 'billing_bill_referral',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'billing_bill_created_by',
        ],
        'client' => [
            'mapper'   => ClientMapper::class,
            'external' => 'billing_bill_client',
        ],
        'supplier' => [
            'mapper'   => SupplierMapper::class,
            'external' => 'billing_bill_supplier',
        ],
        'attributes' => [
            'mapper'      => BillAttributeMapper::class,
            'table'       => 'billing_bill_attr',
            'self'        => 'billing_bill_attr_bill',
            'conditional' => true,
            'external'    => null,
        ],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'billing_bill_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'billing_bill';
}
