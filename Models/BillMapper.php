<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
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
use phpOMS\DataStorage\Database\DataMapperAbstract;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\Localization\Money;
use phpOMS\DataStorage\Database\RelationType;
use phpOMS\Localization\Defaults\CountryMapper;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class BillMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'billing_out_id'            => ['name' => 'billing_out_id',      'type' => 'int',    'internal' => 'id'],
        'billing_out_number'        => ['name' => 'billing_out_number',      'type' => 'string',    'internal' => 'number'],
        'billing_out_type'          => ['name' => 'billing_out_type',      'type' => 'int',    'internal' => 'type'],
        'billing_out_info'          => ['name' => 'billing_out_info',      'type' => 'string',    'internal' => 'info'],
        'billing_out_status'        => ['name' => 'billing_out_status',      'type' => 'int',    'internal' => 'status'],
        'billing_out_shipTo'        => ['name' => 'billing_out_shipTo',      'type' => 'string',    'internal' => 'shipTo'],
        'billing_out_shipFAO'       => ['name' => 'billing_out_shipFAO',      'type' => 'string',    'internal' => 'shipFAO'],
        'billing_out_shipAddr'      => ['name' => 'billing_out_shipAddr',      'type' => 'string',    'internal' => 'shipAddress'],
        'billing_out_shipCity'      => ['name' => 'billing_out_shipCity',      'type' => 'string',    'internal' => 'shipCity'],
        'billing_out_shipZip'       => ['name' => 'billing_out_shipZip',      'type' => 'string',    'internal' => 'shipZip'],
        'billing_out_shipCountry'   => ['name' => 'billing_out_shipCountry',      'type' => 'string',    'internal' => 'shipCountry'],
        'billing_out_billTo'        => ['name' => 'billing_out_billTo',      'type' => 'string',    'internal' => 'billTo'],
        'billing_out_billFAO'       => ['name' => 'billing_out_billFAO',      'type' => 'string',    'internal' => 'billFAO'],
        'billing_out_billAddr'      => ['name' => 'billing_out_billAddr',      'type' => 'string',    'internal' => 'billAddress'],
        'billing_out_billCity'      => ['name' => 'billing_out_billCity',      'type' => 'string',    'internal' => 'billCity'],
        'billing_out_billZip'       => ['name' => 'billing_out_billZip',      'type' => 'string',    'internal' => 'billZip'],
        'billing_out_billCountry'   => ['name' => 'billing_out_billCountry',      'type' => 'string',    'internal' => 'billCountry'],
        'billing_out_gross'         => ['name' => 'billing_out_gross',      'type' => 'Serializable',    'internal' => 'gross'],
        'billing_out_net'           => ['name' => 'billing_out_net',      'type' => 'Serializable',    'internal' => 'net'],
        'billing_out_costs'         => ['name' => 'billing_out_costs',      'type' => 'Serializable',    'internal' => 'costs'],
        'billing_out_profit'        => ['name' => 'billing_out_profit',      'type' => 'Serializable',    'internal' => 'profit'],
        'billing_out_currency'      => ['name' => 'billing_out_currency',      'type' => 'int',    'internal' => 'currency'],
        'billing_out_referral' => ['name' => 'billing_out_referral',      'type' => 'int',    'internal' => 'referral'],
        'billing_out_referral_name' => ['name' => 'billing_out_referral_name',      'type' => 'string',    'internal' => 'referralName'],
        'billing_out_reference'     => ['name' => 'billing_out_reference',      'type' => 'int',    'internal' => 'reference'],
        'billing_out_payment'       => ['name' => 'billing_out_payment',      'type' => 'int',    'internal' => 'payment'],
        'billing_out_payment_text'  => ['name' => 'billing_out_payment_text',      'type' => 'string',    'internal' => 'paymentText'],
        'billing_out_paymentterms'     => ['name' => 'billing_out_paymentterms',      'type' => 'int',    'internal' => 'terms'],
        'billing_out_paymentterms_text'     => ['name' => 'billing_out_paymentterms_text',      'type' => 'string',    'internal' => 'termsText'],
        'billing_out_ship_type'     => ['name' => 'billing_out_ship_type',      'type' => 'int',    'internal' => 'shipping'],
        'billing_out_ship_text'     => ['name' => 'billing_out_ship_text',      'type' => 'string',    'internal' => 'shippingText'],
        'billing_out_client'    => ['name' => 'billing_out_client', 'type' => 'int',      'internal' => 'client'],
        'billing_out_created_by'    => ['name' => 'billing_out_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'billing_out_performance_date'    => ['name' => 'billing_out_performance_date', 'type' => 'DateTime', 'internal' => 'performanceDate', 'readonly' => true],
        'billing_out_created_at'    => ['name' => 'billing_out_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
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
            'table'        => 'billing_out_element',
            'self'         => 'billing_out_element_bill',
            'external'     => null,
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
            'external'   => 'billing_out_type',
        ],
        'referral'  => [
            'mapper'     => AccountMapper::class,
            'external'   => 'billing_out_referral',
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
            'external'   => 'billing_out_created_by',
        ],
        'client' => [
            'mapper'     => ClientMapper::class,
            'external'   => 'billing_out_client',
        ],
    ];

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'billing_out_id';

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'billing_out';

    public static function getSalesByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_out_element_total_salesprice_net)')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
            ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_out_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money((int) $result[0]);
    }

    public static function getAvgSalesPriceByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_out_element_single_salesprice_net)', 'COUNT(billing_out_element_total_salesprice_net)')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
            ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_out_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money((int) (((int) $result[0]) / ((int) $result[1])));
    }

    public static function getLastOrderDateByItemId(int $id) : \DateTimeImmutable
    {
        // @todo: only delivers/invoice/production (no offers ...)
        $query  = new Builder(self::$db);
        $result = $query->select('billing_out_performance_date')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
            ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->orderBy('billing_out_id', 'DESC')
            ->limit(1)
            ->execute()
            ->fetch();

        return new \DateTimeImmutable($result[0]);
    }

    public static function getItemRetentionRate(int $id, \DateTime $start, \DateTime $end) : float
    {

    }

    public static function getItemLivetimeValue(int $id, \DateTime $start, \DateTime $end) : Money
    {

    }

    public static function getNewestItemInvoices(int $id, int $limit = 10) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_' . $depth)
            ->on(self::$table . '_' . $depth . '.billing_out_id', '=', BillElementMapper::getTable() . '_' . $depth . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '_' . $depth . '.billing_out_element_item', '=', $id)
            ->limit($limit);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    public static function getItemTopCustomers(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $depth = 3;

        $query ??= ClientMapper::getQuery(null, [], RelationType::ALL, $depth);
        $query->selectAs('SUM(billing_out_element_total_salesprice_net)', 'net_sales')
            ->leftJoin(self::$table, self::$table . '_' . $depth)
                ->on(ClientMapper::getTable() . '_' . $depth . '.clientmgmt_client_id', '=', self::$table . '_' . $depth . '.billing_out_client')
            ->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_' . $depth)
                ->on(self::$table . '_' . $depth . '.billing_out_id', '=', BillElementMapper::getTable() . '_' . $depth . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '_' . $depth . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '_' . $depth . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '_' . $depth . '.billing_out_performance_date', '<=', $end)
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->groupBy(ClientMapper::getTable() . '_' . $depth . '.clientmgmt_client_id');

        $clients = ClientMapper::getAllByQuery($query, RelationType::ALL, $depth);
        $data = ClientMapper::getDataLastQuery();

        return [$clients, $data];
    }

    public static function getItemRegionSales(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_region')
            ->selectAs('SUM(billing_out_element_total_salesprice_net)', 'net_sales')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->leftJoin(CountryMapper::getTable())
                ->on(self::$table . '.billing_out_billCountry', '=', CountryMapper::getTable() . '.country_code2')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_out_performance_date', '<=', $end)
            ->groupBy(CountryMapper::getTable() . '.country_region')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    public static function getItemCountrySales(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_code2')
            ->selectAs('SUM(billing_out_element_total_salesprice_net)', 'net_sales')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->leftJoin(CountryMapper::getTable())
                ->on(self::$table . '.billing_out_billCountry', '=', CountryMapper::getTable() . '.country_code2')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_out_performance_date', '<=', $end)
            ->groupBy(CountryMapper::getTable() . '.country_code2')
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    public static function getItemMonthlySalesCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_out_element_total_salesprice_net)', 'net_sales')
            ->selectAs('SUM(billing_out_element_total_purchaseprice_net)', 'net_costs')
            ->selectAs('YEAR(billing_out_performance_date)', 'year')
            ->selectAs('MONTH(billing_out_performance_date)', 'month')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_out_id', '=', BillElementMapper::getTable() . '.billing_out_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_out_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_out_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_out_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ->fetchAll();

        return $result;
    }
}
