<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\Localization\Defaults\CountryMapper;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * PurchaseBill mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Bill
 * @extends BillMapper<T>
 */
final class PurchaseBillMapper extends BillMapper
{
    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = Bill::class;

    /**
     * Placeholder
     */
    public static function getPurchaseBeforePivot(
        mixed $pivot,
        ?string $column = null,
        int $limit = 50,
        int $depth = 3,
        ?Builder $query = null
    ) : array
    {
        return self::getAll()
            ->with('type')
            ->where('id', $pivot, '<')
            ->where('transferType', BillTransferType::PURCHASE)
            ->limit($limit)
            ->executeGetArray();
    }

    /**
     * Placeholder
     */
    public static function getPurchaseAfterPivot(
        mixed $pivot,
        ?string $column = null,
        int $limit = 50,
        int $depth = 3,
        ?Builder $query = null
    ) : array
    {
        return self::getAll()
            ->with('type')
            ->where('id', $pivot, '>')
            ->where('transferType', BillTransferType::PURCHASE)
            ->limit($limit)
            ->executeGetArray();
    }

    /**
     * Placeholder
     */
    public static function getPurchaseByItemId(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        $query = new Builder(self::$db);

        /** @var array $result */
        $result = $query->select('SUM(billing_bill_element_total_netpurchaseprice)')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ?->fetch() ?? [];

        return new FloatInt((int) ($result[0] ?? 0));
    }

    /**
     * Placeholder
     */
    public static function getPurchaseBySupplierId(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        $query = new Builder(self::$db);

        /** @var array $result */
        $result = $query->select('SUM(billing_bill_netcosts)')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_supplier', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ?->fetch() ?? [];

        return new FloatInt((int) ($result[0] ?? 0));
    }

    /**
     * Placeholder
     */
    public static function getAvgPurchasePriceByItemId(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        $query = new Builder(self::$db);

        /** @var false|array $result */
        $result = $query->select('SUM(billing_bill_element_single_netpurchaseprice)', 'COUNT(billing_bill_element_total_netpurchaseprice)')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ?->fetch() ?? false;

        return new FloatInt($result === false || $result[1] == 0 ? 0 : (int) (((int) $result[0]) / ((int) $result[1])));
    }

    /**
     * Placeholder
     */
    public static function getLastOrderDateByItemId(int $id) : ?\DateTimeImmutable
    {
        // @todo only delivers/invoice/production (no offers ...)
        $query = new Builder(self::$db);

        /** @var false|array $result */
        $result = $query->select('billing_bill_performance_date')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ?->fetch() ?? false;

        return $result === false ? null : new \DateTimeImmutable($result[0]);
    }

    /**
     * Placeholder
     */
    public static function getLastOrderDateBySupplierId(int $id) : ?\DateTimeImmutable
    {
        // @todo only delivers/invoice/production (no offers ...)
        $query = new Builder(self::$db);

        /** @var false|array $result */
        $result = $query->select('billing_bill_performance_date')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_supplier', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ?->fetch() ?? false;

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
    public static function getItemLivetimeValue(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        return new FloatInt();
    }

    /**
     * Placeholder
     */
    public static function getNewestItemInvoices(int $id, int $limit = 10) : array
    {
        $query = self::getQuery();
        $query->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id)
            ->limit($limit);

        /** @phpstan-ignore-next-line */
        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->executeGetArray($query);
    }

    /**
     * Placeholder
     */
    public static function getNewestSupplierInvoices(int $id, int $limit = 10) : array
    {
        $query = self::getQuery();
        $query->where(self::TABLE . '_d1.billing_bill_supplier', '=', $id)
            ->limit($limit);

        /** @phpstan-ignore-next-line */
        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->executeGetArray($query);
    }

    /**
     * Placeholder
     */
    public static function getItemTopSuppliers(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query = SupplierMapper::getQuery();
        $query->selectAs('SUM(billing_bill_element_total_netpurchaseprice)', 'net_purchase')
            ->leftJoin(self::TABLE, self::TABLE . '_d1')
                ->on(SupplierMapper::TABLE . '_d1.suppliermgmt_supplier_id', '=', self::TABLE . '_d1.billing_bill_supplier')
            ->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '_d1.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '_d1.billing_bill_performance_date', '<=', $end)
            ->orderBy('net_purchase', 'DESC')
            ->limit($limit)
            ->groupBy(SupplierMapper::TABLE . '_d1.suppliermgmt_supplier_id');

        $suppliers = SupplierMapper::getAll()->execute($query);
        $data      = SupplierMapper::getRaw()->executeGetArray();

        return [$suppliers, $data];
    }

    /**
     * Placeholder
     */
    public static function getItemCountryPurchase(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::TABLE . '.country_code2')
            ->selectAs('SUM(billing_bill_element_total_netpurchaseprice)', 'net_purchase')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_billCountry', '=', CountryMapper::TABLE . '.country_code2')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::TABLE . '.country_code2')
            ->orderBy('net_purchase', 'DESC')
            ->limit($limit)
            ->execute()
            ?->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $result ?? [];
    }

    /**
     * Placeholder
     */
    public static function getItemMonthlyPurchaseCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_element_total_netpurchaseprice)', 'net_purchase')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ?->fetchAll();

        return $result ?? [];
    }

    /**
     * Placeholder
     */
    public static function getSupplierMonthlyPurchaseCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_netcosts)', 'net_purchase')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_supplier', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ?->fetchAll();

        return $result ?? [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSupplierNetSales(int $supplier, \DateTime $start, \DateTime $end) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_netsales * billing_type_sign) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        WHERE
            billing_bill_supplier = {$supplier}
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}';
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt(-((int) ($result[0]['net_sales'] ?? 0)));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSupplierLastOrder(int $supplier) : ?\DateTime
    {
        $sql = <<<SQL
        SELECT billing_bill_created_at
        FROM billing_bill
        WHERE billing_bill_supplier = {$supplier}
        ORDER BY billing_bill_created_at DESC
        LIMIT 1;
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return isset($result[0]['billing_bill_created_at'])
            ? new \DateTime(($result[0]['billing_bill_created_at']))
            : null;
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSLVHistoric(int $supplier) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_netsales * billing_type_sign) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        WHERE billing_bill_supplier = {$supplier};
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt(-((int) ($result[0]['net_sales'] ?? 0)));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSupplierMonthlySalesCosts(int $supplier, \DateTime $start, \DateTime $end) : array
    {
        $sql = <<<SQL
        SELECT
            SUM(billing_bill_netsales * billing_type_sign * -1) as net_sales,
            SUM(billing_bill_netcosts * billing_type_sign * -1) as net_costs,
            YEAR(billing_bill_performance_date) as year,
            MONTH(billing_bill_performance_date) as month
        FROM billing_bill
        LEFT JOIN billing_type ON billing_bill_type = billing_type_id
        WHERE
            billing_bill_supplier = {$supplier}
            AND billing_type_accounting = 1
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}'
        GROUP BY year, month
        ORDER BY year ASC, month ASC;
        SQL;

        $query = new Builder(self::$db);

        return $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSupplierAttributeNetSales(
        int $supplier,
        string $attribute,
        string $language,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $sql = <<<SQL
        SELECT
            itemmgmt_attr_value_l11n_title as title,
            SUM(billing_bill_element_total_netlistprice * billing_type_sign * -1) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        LEFT JOIN billing_bill_element
            ON billing_bill_id = billing_bill_element_bill
        LEFT JOIN itemmgmt_item
            ON itemmgmt_item_id = billing_bill_element_item
        LEFT JOIN itemmgmt_item_attr
            ON itemmgmt_item_id = itemmgmt_item_attr_item
        LEFT JOIN itemmgmt_attr_type
            ON itemmgmt_item_attr_type = itemmgmt_attr_type_id
        LEFT JOIN itemmgmt_attr_type_l11n
            ON itemmgmt_attr_type_id = itemmgmt_attr_type_l11n_type AND itemmgmt_attr_type_l11n_lang = '{$language}'
        LEFT JOIN itemmgmt_attr_value
            ON itemmgmt_item_attr_value = itemmgmt_attr_value_id
        LEFT JOIN itemmgmt_attr_value_l11n
            ON itemmgmt_attr_value_id = itemmgmt_attr_value_l11n_value AND itemmgmt_attr_value_l11n_lang = '{$language}'
        WHERE
            billing_bill_supplier = {$supplier}
            AND billing_type_accounting = 1
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}'
            AND itemmgmt_attr_type_name = '{$attribute}'
        GROUP BY
            itemmgmt_attr_value_l11n_title;
        SQL;

        $query = new Builder(self::$db);

        return $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSupplierItem(int $supplier, \DateTime $start, \DateTime $end) : array
    {
        return BillElementMapper::getAll()
            ->with('bill')
            ->with('bill/type')
            ->where('bill/supplier', $supplier)
            ->where('bill/type/isAccounting', true)
            ->executeGetArray();
    }
}
