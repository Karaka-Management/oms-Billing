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

use Modules\ClientManagement\Models\ClientMapper;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\RelationType;
use phpOMS\Localization\Defaults\CountryMapper;
use phpOMS\Localization\Money;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class SalesBillMapper extends BillMapper
{
    /**
     * Model to use by the mapper.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $model = Bill::class;

    /**
     * Placeholder
     */
    public static function getSalesBeforePivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        int $relations = RelationType::ALL,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        $query = self::getQuery(null, [], $relations, $depth);
        $query->where(BillTypeMapper::getTable() . '_d' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::SALES);

        return self::getBeforePivot($pivot, $column, $limit, $relations, $depth, $query);
    }

    /**
     * Placeholder
     */
    public static function getSalesAfterPivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        int $relations = RelationType::ALL,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        $query = self::getQuery(null, [], $relations, $depth);
        $query->where(BillTypeMapper::getTable() . '_d' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::SALES);

        return self::getAfterPivot($pivot, $column, $limit, $relations, $depth, $query);
    }

    /**
     * Placeholder
     */
    public static function getSalesByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_element_total_salesprice_net)')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money((int) $result[0]);
    }

    /**
     * Placeholder
     */
    public static function getSalesByClientId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_net)')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_client', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money((int) $result[0]);
    }

    /**
     * Placeholder
     */
    public static function getAvgSalesPriceByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_element_single_salesprice_net)', 'COUNT(billing_bill_element_total_salesprice_net)')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money($result === false || $result[1] == 0 ? 0 : (int) (((int) $result[0]) / ((int) $result[1])));
    }

    /**
     * Placeholder
     */
    public static function getLastOrderDateByItemId(int $id) : ?\DateTimeImmutable
    {
        // @todo: only delivers/invoice/production (no offers ...)
        $query  = new Builder(self::$db);
        $result = $query->select('billing_bill_performance_date')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ->fetch();

        return $result === false ? null : new \DateTimeImmutable($result[0]);
    }

    /**
     * Placeholder
     */
    public static function getLastOrderDateByClientId(int $id) : ?\DateTimeImmutable
    {
        // @todo: only delivers/invoice/production (no offers ...)
        $query  = new Builder(self::$db);
        $result = $query->select('billing_bill_performance_date')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_client', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ->fetch();

        return $result === false ? null : new \DateTimeImmutable($result[0]);
    }

    /**
     * Placeholder
     */
    public static function getItemRetentionRate(int $id, \DateTime $start, \DateTime $end) : float
    {
        return 0.0;
    }

    /**
     * Placeholder
     */
    public static function getItemLivetimeValue(int $id, \DateTime $start, \DateTime $end) : Money
    {
        return new Money();
    }

    /**
     * Placeholder
     */
    public static function getNewestItemInvoices(int $id, int $limit = 10) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_d' . $depth)
                ->on(self::$table . '_d' . $depth . '.billing_bill_id', '=', BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_item', '=', $id)
            ->limit($limit);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    /**
     * Placeholder
     */
    public static function getNewestClientInvoices(int $id, int $limit = 10) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->where(self::$table . '_d' . $depth . '.billing_bill_client', '=', $id)
            ->limit($limit);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    /**
     * Placeholder
     */
    public static function getItemTopCustomers(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $depth = 3;

        $query ??= ClientMapper::getQuery(null, [], RelationType::ALL, $depth);
        $query->selectAs('SUM(billing_bill_element_total_salesprice_net)', 'net_sales')
            ->leftJoin(self::$table, self::$table . '_d' . $depth)
                ->on(ClientMapper::getTable() . '_d' . $depth . '.clientmgmt_client_id', '=', self::$table . '_d' . $depth . '.billing_bill_client')
            ->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_d' . $depth)
                ->on(self::$table . '_d' . $depth . '.billing_bill_id', '=', BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '_d' . $depth . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '_d' . $depth . '.billing_bill_performance_date', '<=', $end)
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->groupBy(ClientMapper::getTable() . '_d' . $depth . '.clientmgmt_client_id');

        $clients = ClientMapper::getAllByQuery($query, RelationType::ALL, $depth);
        $data    = ClientMapper::getDataLastQuery();

        return [$clients, $data];
    }

    /**
     * Placeholder
     */
    public static function getItemBills(int $id, \DateTime $start, \DateTime $end) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_d' . $depth)
                ->on(self::$table . '_d' . $depth . '.billing_bill_id', '=', BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_item', '=', $id)
            ->limit($limit = 10);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    /**
     * Placeholder
     */
    public static function getClientItem(int $client, \DateTime $start, \DateTime $end) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= BillElementMapper::getQuery(null, [], RelationType::ALL, $depth);
        $query->leftJoin(self::$table, self::$table . '_d' . $depth)
                ->on(BillElementMapper::getTable() . '_d' . $depth . '.billing_bill_element_bill', '=', self::$table . '_d' . $depth . '.billing_bill_id')
            ->where(self::$table . '_d' . $depth . '.billing_bill_client', '=', $client)
            ->limit($limit = 10);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_d' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return BillElementMapper::getAllByQuery($query, RelationType::ALL, $depth);
    }

    /**
     * Placeholder
     */
    public static function getItemRegionSales(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_region')
            ->selectAs('SUM(billing_bill_element_total_salesprice_net)', 'net_sales')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::getTable())
                ->on(self::$table . '.billing_bill_billCountry', '=', CountryMapper::getTable() . '.country_code2')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::getTable() . '.country_region')
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    /**
     * Placeholder
     */
    public static function getItemCountrySales(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_code2')
            ->selectAs('SUM(billing_bill_element_total_salesprice_net)', 'net_sales')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::getTable())
                ->on(self::$table . '.billing_bill_billCountry', '=', CountryMapper::getTable() . '.country_code2')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::getTable() . '.country_code2')
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    /**
     * Placeholder
     */
    public static function getItemMonthlySalesCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_element_total_salesprice_net)', 'net_sales')
            ->selectAs('SUM(billing_bill_element_total_purchaseprice_net)', 'net_costs')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ->fetchAll();

        return $result;
    }

    /**
     * Placeholder
     */
    public static function getClientMonthlySalesCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_net)', 'net_sales')
            ->selectAs('SUM(billing_bill_costs)', 'net_costs')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_client', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ->fetchAll();

        return $result;
    }
}
