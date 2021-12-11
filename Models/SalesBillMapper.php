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
    public const MODEL = Bill::class;

    /**
     * Placeholder
     */
    public static function getSalesBeforePivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        return self::getAll()
            ->with('type')
            ->where('id', $pivot, '<')
            ->where('transferType', BillTransferType::SALES)
            ->limit($limit)
            ->execute();
    }

    /**
     * Placeholder
     */
    public static function getSalesAfterPivot(
        mixed $pivot,
        string $column = null,
        int $limit = 50,
        int $depth = 3,
        Builder $query = null
    ) : array
    {
        return self::getAll()
            ->with('type')
            ->where('id', $pivot, '>')
            ->where('transferType', BillTransferType::SALES)
            ->limit($limit)
            ->execute();
    }

    /**
     * Placeholder
     */
    public static function getSalesByItemId(int $id, \DateTime $start, \DateTime $end) : Money
    {
        $query  = new Builder(self::$db);
        $result = $query->select('SUM(billing_bill_element_total_netsalesprice)')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
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
        $result = $query->select('SUM(billing_bill_netsales)')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_client', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
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
        $result = $query->select('SUM(billing_bill_element_single_netsalesprice)', 'COUNT(billing_bill_element_total_netsalesprice)')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
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
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
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
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_client', '=', $id)
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

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query = self::getQuery();
        $query->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id)
            ->limit($limit);

        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->execute($query);
    }

    /**
     * Placeholder
     */
    public static function getNewestClientInvoices(int $id, int $limit = 10) : array
    {

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query = self::getQuery();
        $query->where(self::TABLE . '_d1.billing_bill_client', '=', $id)
            ->limit($limit);

        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->execute($query);
    }

    /**
     * Placeholder
     */
    public static function getItemTopClients(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {

        $query = ClientMapper::getQuery();
        $query->selectAs('SUM(billing_bill_element_total_netsalesprice)', 'net_sales')
            ->leftJoin(self::TABLE, self::TABLE . '_d1')
                ->on(ClientMapper::TABLE . '_d1.clientmgmt_client_id', '=', self::TABLE . '_d1.billing_bill_client')
            ->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '_d1.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '_d1.billing_bill_performance_date', '<=', $end)
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->groupBy(ClientMapper::TABLE . '_d1.clientmgmt_client_id');

        $clients = ClientMapper::getAll()->execute($query);
        $data    = ClientMapper::getRaw()->execute();

        return [$clients, $data];
    }

    /**
     * Placeholder
     */
    public static function getItemBills(int $id, \DateTime $start, \DateTime $end) : array
    {

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query = self::getQuery();
        $query->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id)
            ->limit($limit = 10);

        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->execute($query);
    }

    /**
     * Placeholder
     */
    public static function getClientItem(int $client, \DateTime $start, \DateTime $end) : array
    {

        // @todo: limit is not working correctly... only returns / 2 or something like that?. Maybe because bills arent unique?

        $query = BillElementMapper::getQuery();
        $query->leftJoin(self::TABLE, self::TABLE . '_d1')
                ->on(BillElementMapper::TABLE . '_d1.billing_bill_element_bill', '=', self::TABLE . '_d1.billing_bill_id')
            ->where(self::TABLE . '_d1.billing_bill_client', '=', $client)
            ->limit($limit = 10);

        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return BillElementMapper::getAll()->execute($query);
    }

    /**
     * Placeholder
     */
    public static function getItemRegionSales(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->select(CountryMapper::TABLE . '.country_region')
            ->selectAs('SUM(billing_bill_element_total_netsalesprice)', 'net_sales')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_billCountry', '=', CountryMapper::TABLE . '.country_code2')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::TABLE . '.country_region')
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
        $result = $query->select(CountryMapper::TABLE . '.country_code2')
            ->selectAs('SUM(billing_bill_element_total_netsalesprice)', 'net_sales')
            ->from(self::TABLE)
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->leftJoin(CountryMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_billCountry', '=', CountryMapper::TABLE . '.country_code2')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy(CountryMapper::TABLE . '.country_code2')
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
        $result = $query->selectAs('SUM(billing_bill_element_total_netsalesprice)', 'net_sales')
            ->selectAs('SUM(billing_bill_element_total_netpurchaseprice)', 'net_costs')
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
            ->fetchAll();

        return $result;
    }

    /**
     * Placeholder
     */
    public static function getClientMonthlySalesCosts(int $id, \DateTime $start, \DateTime $end) : array
    {
        $query  = new Builder(self::$db);
        $result = $query->selectAs('SUM(billing_bill_netsales)', 'net_sales')
            ->selectAs('SUM(billing_bill_netcosts)', 'net_costs')
            ->selectAs('YEAR(billing_bill_performance_date)', 'year')
            ->selectAs('MONTH(billing_bill_performance_date)', 'month')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_client', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->groupBy('year', 'month')
            ->orderBy(['year', 'month'], ['ASC', 'ASC'])
            ->execute()
            ->fetchAll();

        return $result;
    }
}
