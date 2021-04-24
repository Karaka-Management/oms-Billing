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

use Modules\SupplierManagement\Models\SupplierMapper;
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
final class PurchaseBillMapper extends BillMapper
{
    /**
     * Model to use by the mapper.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $model = Bill::class;

    public static function getPurchaseBeforePivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        string $order = 'ASC',
        int $relations = RelationType::ALL,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        $query = self::getQuery(null, [], $relations, $depth);
        $query->where(BillTypeMapper::getTable() . '_' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::PURCHASE);

        return self::getBeforePivot($pivot, $column, $limit, $order, $relations, $depth, $query);
    }

    public static function getPurchaseAfterPivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        string $order = 'ASC',
        int $relations = RelationType::ALL,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        $query = self::getQuery(null, [], $relations, $depth);
        $query->where(BillTypeMapper::getTable() . '_' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::PURCHASE);

        return self::getAfterPivot($pivot, $column, $limit, $order, $relations, $depth, $query);
    }

    public static function getPurchaseByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_element_total_purchaseprice_net)')
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

    public static function getPurchaseBySupplierId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_net)')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_supplier', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ->fetch();

        return new Money((int) $result[0]);
    }

    public static function getAvgPurchasePriceByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_element_single_purchaseprice_net)', 'COUNT(billing_bill_element_total_purchaseprice_net)')
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

    public static function getLastOrderDateBySupplierId(int $id) : ?\DateTimeImmutable
    {
        // @todo: only delivers/invoice/production (no offers ...)
        $query  = new Builder(self::$db);
        $result = $query->select('billing_bill_performance_date')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_supplier', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ->fetch();

        return $result === false ? null : new \DateTimeImmutable($result[0]);
    }

    public static function getItemRetentionRate(int $id, \DateTime $start, \DateTime $end) : float
    {
        return 0.0;
    }

    public static function getItemLivetimeValue(int $id, \DateTime $start, \DateTime $end) : Money
    {
        return new Money();
    }

    public static function getNewestItemInvoices(int $id, int $limit = 10) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_' . $depth)
                ->on(self::$table . '_' . $depth . '.billing_bill_id', '=', BillElementMapper::getTable() . '_' . $depth . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '_' . $depth . '.billing_bill_element_item', '=', $id)
            ->limit($limit);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    public static function getNewestSupplierInvoices(int $id, int $limit = 10) : array
    {
        $depth = 3;

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query ??= self::getQuery(null, [], RelationType::ALL, $depth);
        $query->where(self::$table . '_' . $depth . '.billing_bill_supplier', '=', $id)
            ->limit($limit);

        if (!empty(self::$createdAt)) {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$createdAt]['name'], 'DESC');
        } else {
            $query->orderBy(self::$table  . '_' . $depth . '.' . self::$columns[self::$primaryField]['name'], 'DESC');
        }

        return self::getAllByQuery($query, RelationType::ALL, $depth);
    }

    public static function getItemTopSuppliers(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $depth = 3;

        $query ??= SupplierMapper::getQuery(null, [], RelationType::ALL, $depth);
        $query->selectAs('SUM(billing_bill_element_total_purchaseprice_net)', 'net_purchase')
            ->leftJoin(self::$table, self::$table . '_' . $depth)
                ->on(SupplierMapper::getTable() . '_' . $depth . '.suppliermgmt_supplier_id', '=', self::$table . '_' . $depth . '.billing_bill_supplier')
            ->leftJoin(BillElementMapper::getTable(), BillElementMapper::getTable() . '_' . $depth)
                ->on(self::$table . '_' . $depth . '.billing_bill_id', '=', BillElementMapper::getTable() . '_' . $depth . '.billing_bill_element_bill')
            ->where(BillElementMapper::getTable() . '_' . $depth . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '_' . $depth . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '_' . $depth . '.billing_bill_performance_date', '<=', $end)
            ->orderBy('net_purchase', 'DESC')
            ->limit($limit)
            ->groupBy(SupplierMapper::getTable() . '_' . $depth . '.suppliermgmt_supplier_id');

        $suppliers = SupplierMapper::getAllByQuery($query, RelationType::ALL, $depth);
        $data      = SupplierMapper::getDataLastQuery();

        return [$suppliers, $data];
    }

    public static function getItemRegionPurchase(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_region')
            ->selectAs('SUM(billing_bill_element_total_purchaseprice_net)', 'net_purchase')
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

    public static function getItemCountryPurchase(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::getTable() . '.country_code2')
            ->selectAs('SUM(billing_bill_element_total_purchaseprice_net)', 'net_purchase')
            ->from(self::$table)
            ->leftJoin(BillElementMapper::getTable())
                ->on(self::$table . '.billing_bill_id', '=', BillElementMapper::getTable() . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::getTable())
                ->on(self::$table . '.billing_bill_billCountry', '=', CountryMapper::getTable() . '.country_code2')
            ->where(BillElementMapper::getTable() . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::getTable() . '.country_code2')
            ->orderBy('net_purchase', 'DESC')
            ->limit($limit)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result;
    }

    public static function getItemMonthlyPurchaseCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_element_total_purchaseprice_net)', 'net_purchase')
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

    public static function getSupplierMonthlyPurchaseCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_net)', 'net_purchase')
            ->selectAs('SUM(billing_bill_costs)', 'net_costs')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::$table)
            ->where(self::$table . '.billing_bill_supplier', '=', $id)
            ->andWhere(self::$table . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::$table . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ->fetchAll();

        return $result;
    }
}
