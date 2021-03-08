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

use Modules\Admin\Models\AccountMapper;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\Media\Models\MediaMapper;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class BillMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'billing_bill_id'                      => ['name' => 'billing_bill_id',      'type' => 'int',    'internal' => 'id'],
        'billing_bill_number'                  => ['name' => 'billing_bill_number',      'type' => 'string',    'internal' => 'number'],
        'billing_bill_type'                    => ['name' => 'billing_bill_type',      'type' => 'int',    'internal' => 'type'],
        'billing_bill_info'                    => ['name' => 'billing_bill_info',      'type' => 'string',    'internal' => 'info'],
        'billing_bill_status'                  => ['name' => 'billing_bill_status',      'type' => 'int',    'internal' => 'status'],
        'billing_bill_shipTo'                  => ['name' => 'billing_bill_shipTo',      'type' => 'string',    'internal' => 'shipTo'],
        'billing_bill_shipFAO'                 => ['name' => 'billing_bill_shipFAO',      'type' => 'string',    'internal' => 'shipFAO'],
        'billing_bill_shipAddr'                => ['name' => 'billing_bill_shipAddr',      'type' => 'string',    'internal' => 'shipAddress'],
        'billing_bill_shipCity'                => ['name' => 'billing_bill_shipCity',      'type' => 'string',    'internal' => 'shipCity'],
        'billing_bill_shipZip'                 => ['name' => 'billing_bill_shipZip',      'type' => 'string',    'internal' => 'shipZip'],
        'billing_bill_shipCountry'             => ['name' => 'billing_bill_shipCountry',      'type' => 'string',    'internal' => 'shipCountry'],
        'billing_bill_billTo'                  => ['name' => 'billing_bill_billTo',      'type' => 'string',    'internal' => 'billTo'],
        'billing_bill_billFAO'                 => ['name' => 'billing_bill_billFAO',      'type' => 'string',    'internal' => 'billFAO'],
        'billing_bill_billAddr'                => ['name' => 'billing_bill_billAddr',      'type' => 'string',    'internal' => 'billAddress'],
        'billing_bill_billCity'                => ['name' => 'billing_bill_billCity',      'type' => 'string',    'internal' => 'billCity'],
        'billing_bill_billZip'                 => ['name' => 'billing_bill_billZip',      'type' => 'string',    'internal' => 'billZip'],
        'billing_bill_billCountry'             => ['name' => 'billing_bill_billCountry',      'type' => 'string',    'internal' => 'billCountry'],
        'billing_bill_gross'                   => ['name' => 'billing_bill_gross',      'type' => 'Serializable',    'internal' => 'gross'],
        'billing_bill_net'                     => ['name' => 'billing_bill_net',      'type' => 'Serializable',    'internal' => 'net'],
        'billing_bill_costs'                   => ['name' => 'billing_bill_costs',      'type' => 'Serializable',    'internal' => 'costs'],
        'billing_bill_profit'                  => ['name' => 'billing_bill_profit',      'type' => 'Serializable',    'internal' => 'profit'],
        'billing_bill_currency'                => ['name' => 'billing_bill_currency',      'type' => 'int',    'internal' => 'currency'],
        'billing_bill_referral'                => ['name' => 'billing_bill_referral',      'type' => 'int',    'internal' => 'referral'],
        'billing_bill_referral_name'           => ['name' => 'billing_bill_referral_name',      'type' => 'string',    'internal' => 'referralName'],
        'billing_bill_reference'               => ['name' => 'billing_bill_reference',      'type' => 'int',    'internal' => 'reference'],
        'billing_bill_payment'                 => ['name' => 'billing_bill_payment',      'type' => 'int',    'internal' => 'payment'],
        'billing_bill_payment_text'            => ['name' => 'billing_bill_payment_text',      'type' => 'string',    'internal' => 'paymentText'],
        'billing_bill_paymentterms'            => ['name' => 'billing_bill_paymentterms',      'type' => 'int',    'internal' => 'terms'],
        'billing_bill_paymentterms_text'       => ['name' => 'billing_bill_paymentterms_text',      'type' => 'string',    'internal' => 'termsText'],
        'billing_bill_ship_type'               => ['name' => 'billing_bill_ship_type',      'type' => 'int',    'internal' => 'shipping'],
        'billing_bill_ship_text'               => ['name' => 'billing_bill_ship_text',      'type' => 'string',    'internal' => 'shippingText'],
        'billing_bill_client'                  => ['name' => 'billing_bill_client', 'type' => 'int',      'internal' => 'client'],
        'billing_bill_supplier'                => ['name' => 'billing_bill_supplier', 'type' => 'int',      'internal' => 'supplier'],
        'billing_bill_created_by'              => ['name' => 'billing_bill_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'billing_bill_performance_date'        => ['name' => 'billing_bill_performance_date', 'type' => 'DateTime', 'internal' => 'performanceDate', 'readonly' => true],
        'billing_bill_created_at'              => ['name' => 'billing_bill_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    protected static array $hasMany = [
        'elements' => [
            'mapper'       => BillElementMapper::class,
            'table'        => 'billing_bill_element',
            'self'         => 'billing_bill_element_bill',
            'external'     => null,
        ],
        'media'        => [
            'mapper'   => MediaMapper::class,
            'table'    => 'billing_bill_media',
            'external' => 'billing_bill_media_dst',
            'self'     => 'billing_bill_media_src',
        ],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    protected static array $ownsOne = [
        'type'  => [
            'mapper'     => BillTypeMapper::class,
            'external'   => 'billing_bill_type',
        ],
        'referral'  => [
            'mapper'     => AccountMapper::class,
            'external'   => 'billing_bill_referral',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string}>
     * @since 1.0.0
     */
    protected static array $belongsTo = [
        'createdBy' => [
            'mapper'     => AccountMapper::class,
            'external'   => 'billing_bill_created_by',
        ],
        'client' => [
            'mapper'     => ClientMapper::class,
            'external'   => 'billing_bill_client',
        ],
        'supplier' => [
            'mapper'     => SupplierMapper::class,
            'external'   => 'billing_bill_supplier',
        ],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'billing_bill_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'billing_bill';
}
