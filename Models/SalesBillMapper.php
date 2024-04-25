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

use Modules\ClientManagement\Models\ClientMapper;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * SalesBill mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class SalesBillMapper extends BillMapper
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
     * @todo Implement
     */
    public static function getSalesBeforePivot(
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
            ->where('type/transferType', BillTransferType::SALES)
            ->limit($limit)
            ->executeGetArray();
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSalesAfterPivot(
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
            ->where('type/transferType', BillTransferType::SALES)
            ->limit($limit)
            ->executeGetArray();
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getSalesByItemId(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        $query = new Builder(self::$db);

        /** @var array $result */
        $result = $query->select('SUM(billing_bill_element_total_netsalesprice)')
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
     * @todo Implement
     */
    public static function getSalesByClientId(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        $query = new Builder(self::$db);

        /** @var array $result */
        $result = $query->select('SUM(billing_bill_netsales)')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_client', '=', $id)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->execute()
            ?->fetch() ?? [];

        return new FloatInt((int) ($result[0] ?? 0));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemAvgSalesPrice(int $item, \DateTime $start, \DateTime $end) : FloatInt
    {
        $sql = <<<SQL
        SELECT
            SUM(billing_bill_element_single_netsalesprice) as net_sales,
            COUNT(billing_bill_element_total_netsalesprice) as net_count
        FROM billing_bill_element
        JOIN billing_bill ON billing_bill_element.billing_bill_element_bill = billing_bill.billing_bill_id
        WHERE
            billing_bill_element_item = {$item}
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}';
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return isset($result[0]['net_count'])
            ? new FloatInt((int) ($result[0]['net_sales'] ?? 0) / ($result[0]['net_count']))
            : new FloatInt(0);
    }

    /**
     * Placeholder
     * @todo Implement
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
     * @todo Implement
     */
    public static function getLastOrderDateByClientId(int $id) : ?\DateTimeImmutable
    {
        // @todo only delivers/invoice/production (no offers ...)
        $query = new Builder(self::$db);

        /** @var false|array $result */
        $result = $query->select('billing_bill_performance_date')
            ->from(self::TABLE)
            ->where(self::TABLE . '.billing_bill_client', '=', $id)
            ->orderBy('billing_bill_id', 'DESC')
            ->limit(1)
            ->execute()
            ?->fetch() ?? false;

        return $result === false ? null : new \DateTimeImmutable($result[0]);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemRetentionRate(int $id, \DateTime $start, \DateTime $end) : float
    {
        return 0.0;
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemLivetimeValue(int $id, \DateTime $start, \DateTime $end) : FloatInt
    {
        return new FloatInt();
    }

    /**
     * Placeholder
     * @todo Implement
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

        return self::getAll()->execute($query);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getNewestClientInvoices(int $id, int $limit = 10) : array
    {
        $query = self::getQuery();
        $query->where(self::TABLE . '_d1.billing_bill_client', '=', $id)
            ->limit($limit);

        /** @phpstan-ignore-next-line */
        if (!empty(self::CREATED_AT)) {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::CREATED_AT]['name'], 'DESC');
        } else {
            $query->orderBy(self::TABLE  . '_d1.' . self::COLUMNS[self::PRIMARYFIELD]['name'], 'DESC');
        }

        return self::getAll()->execute($query);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemTopClients(int $id, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $query = new Builder(self::$db);
        $query->selectAs(ClientMapper::TABLE . '.clientmgmt_client_id', 'client')
            ->selectAs('SUM(' . BillElementMapper::TABLE . '.billing_bill_element_total_netsalesprice * billing_type_transfer_sign)', 'net_sales')
            ->from(ClientMapper::TABLE)
            ->leftJoin(self::TABLE)
                ->on(ClientMapper::TABLE . '.clientmgmt_client_id', '=', self::TABLE . '.billing_bill_client')
            ->leftJoin(BillElementMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_id', '=', BillElementMapper::TABLE . '.billing_bill_element_bill')
            ->leftJoin(BillTypeMapper::TABLE)
                ->on(self::TABLE . '.billing_bill_type', '=', BillTypeMapper::TABLE . '.billing_type_id')
            ->where(BillElementMapper::TABLE . '.billing_bill_element_item', '=', $id)
            ->andWhere(BillTypeMapper::TABLE . '.billing_type_accounting', '=', true)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '>=', $start)
            ->andWhere(self::TABLE . '.billing_bill_performance_date', '<=', $end)
            ->orderBy('net_sales', 'DESC')
            ->limit($limit)
            ->groupBy('client');

        $stmt = $query->execute();
        $data = $stmt?->fetchAll() ?? [];

        $clientIds = [];
        foreach ($data as $client) {
            $clientIds[] = $client['client'];
        }

        $clients = [];
        if (!empty($clientIds)) {
            $clients = ClientMapper::getAll()
                ->with('account')
                ->with('mainAddress')
                ->where('id', $clientIds, 'IN')
                ->executeGetArray();
        }

        return [$clients, $data];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemBills(int $id, \DateTime $start, \DateTime $end, int $limit = 10000) : array
    {
        $query = self::reader()
            ->with('type')
            ->with('type/l11n')
            ->where('type/l11n/language', 'en')
            ->where('createdAt', $start, '>=')
            ->where('createdAt', $end, '<=')
            ->sort('id', OrderType::DESC)
            ->limit($limit)
            ->getQuery();

        $query->leftJoin(BillElementMapper::TABLE, BillElementMapper::TABLE . '_d1')
                ->on(self::TABLE . '_d1.billing_bill_id', '=', BillElementMapper::TABLE . '_d1.billing_bill_element_bill')
            ->where(BillElementMapper::TABLE . '_d1.billing_bill_element_item', '=', $id);

        return self::getAll()
            ->with('type')
            ->with('type/l11n')
            ->sort('id', OrderType::DESC)
            ->limit($limit)
            ->execute($query);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientBills(int $id, string $language, \DateTime $start, \DateTime $end) : array
    {
        return self::getAll()
            ->with('type')
            ->with('type/l11n')
            ->where('client', $id)
            ->where('type/l11n/language', $language)
            ->where('billDate', $start, '>=')
            ->where('billDate', $end, '<=')
            ->executeGetArray();
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientItem(int $client, \DateTime $start, \DateTime $end) : array
    {
        return BillElementMapper::getAll()
            ->with('bill')
            ->with('bill/type')
            ->where('bill/client', $client)
            ->where('bill/type/transferStock', true)
            ->executeGetArray();
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemCountrySales(int $item, \DateTime $start, \DateTime $end, int $limit = 10) : array
    {
        $sql = <<<SQL
        SELECT
            billing_bill_billCountry as country,
            SUM(billing_bill_element_total_netlistprice * billing_type_transfer_sign) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        LEFT JOIN billing_bill_element
            ON billing_bill_id = billing_bill_element_bill
        WHERE
            billing_bill_element_item = {$item}
            AND billing_type_accounting = 1
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}'
        GROUP BY
            billing_bill_billCountry
        LIMIT {$limit};
        SQL;

        $query   = new Builder(self::$db);
        $results = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        if ($results === false) {
            return [];
        }

        $hasData = false;
        foreach ($results as $result) {
            if (!empty($result)) {
                $hasData = true;
                break;
            }
        }

        return $hasData ? $results : [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemMonthlySalesCosts(array $items, \DateTime $start, \DateTime $end) : array
    {
        if (empty($items)) {
            return [];
        }

        $item = \implode(',', $items);

        $sql = <<<SQL
        SELECT
            billing_bill_element_item,
            SUM(billing_bill_element_total_netsalesprice * billing_type_transfer_sign) as net_sales,
            SUM(billing_bill_element_total_netpurchaseprice * billing_type_transfer_sign) as net_costs,
            YEAR(billing_bill_performance_date) as year,
            MONTH(billing_bill_performance_date) as month
        FROM billing_bill_element
        JOIN billing_bill ON billing_bill_element.billing_bill_element_bill = billing_bill.billing_bill_id
        LEFT JOIN billing_type ON billing_bill_type = billing_type_id
        WHERE
            billing_bill_element_item IN ({$item})
            AND billing_type_accounting = 1
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}'
        GROUP BY billing_bill_element_item, year, month
        ORDER BY billing_bill_element_item, year ASC, month ASC;
        SQL;

        $query   = new Builder(self::$db);
        $results = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        if ($results === false) {
            return [];
        }

        $hasData = false;
        foreach ($results as $result) {
            if (!empty($result['net_sales']) || !empty($result['net_costs'])) {
                $hasData = true;
                break;
            }
        }

        return $hasData ? $results : [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemMonthlySalesQuantity(array $items, \DateTime $start, \DateTime $end) : array
    {
        if (empty($items)) {
            return [];
        }

        $item = \implode(',', $items);

        $sql = <<<SQL
        SELECT
            billing_bill_element_item as item,
            SUM(billing_bill_element_quantity) as quantity,
            YEAR(billing_bill_performance_date) as year,
            MONTH(billing_bill_performance_date) as month
        FROM billing_bill_element
        JOIN billing_bill ON billing_bill_element.billing_bill_element_bill = billing_bill.billing_bill_id
        WHERE
            billing_bill_element_item IN ({$item})
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}'
        GROUP BY item, year, month
        ORDER BY item, year ASC, month ASC;
        SQL;

        $query = new Builder(self::$db);

        return $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientMonthlySalesCosts(int $client, \DateTime $start, \DateTime $end) : array
    {
        $sql = <<<SQL
        SELECT
            SUM(billing_bill_netsales * billing_type_transfer_sign) as net_sales,
            SUM(billing_bill_netcosts * billing_type_transfer_sign) as net_costs,
            YEAR(billing_bill_performance_date) as year,
            MONTH(billing_bill_performance_date) as month
        FROM billing_bill
        LEFT JOIN billing_type ON billing_bill_type = billing_type_id
        WHERE
            billing_bill_client = {$client}
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
    public static function getItemNetSales(int $item, \DateTime $start, \DateTime $end) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_element_single_netsalesprice) as net_sales
        FROM billing_bill_element
        JOIN billing_bill ON billing_bill_element.billing_bill_element_bill = billing_bill.billing_bill_id
        WHERE
            billing_bill_element_item = {$item}
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}';
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt((int) ($result[0]['net_sales'] ?? 0));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientAttributeNetSales(
        int $client,
        string $attribute,
        string $language,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $sql = <<<SQL
        SELECT
            itemmgmt_attr_value_l11n_title as title,
            SUM(billing_bill_element_total_netlistprice * billing_type_transfer_sign) as net_sales
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
            billing_bill_client = {$client}
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
    public static function getILVHistoric(int $item) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_element_single_netsalesprice) as net_sales
        FROM billing_bill_element
        JOIN billing_bill ON billing_bill_element.billing_bill_element_bill = billing_bill.billing_bill_id
        WHERE billing_bill_element_item = {$item}
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt((int) ($result[0]['net_sales'] ?? 0));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemMRR() : FloatInt
    {
        return new FloatInt(0);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getItemLastOrder(int $item) : ?\DateTime
    {
        $sql = <<<SQL
        SELECT billing_bill_created_at
        FROM billing_bill
        JOIN billing_bill_element ON billing_bill.billing_bill_id = billing_bill_element.billing_bill_element_id
        WHERE billing_bill_element_item = {$item}
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
    public static function getClientNetSales(int $client, \DateTime $start, \DateTime $end) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_netsales * billing_type_transfer_sign) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        WHERE
            billing_bill_client = {$client}
            AND billing_bill_performance_date >= '{$start->format('Y-m-d H:i:s')}'
            AND billing_bill_performance_date <= '{$end->format('Y-m-d H:i:s')}';
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt((int) ($result[0]['net_sales'] ?? 0));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getCLVHistoric(int $client) : FloatInt
    {
        $sql = <<<SQL
        SELECT SUM(billing_bill_netsales * billing_type_transfer_sign) as net_sales
        FROM billing_bill
        LEFT JOIN billing_type
            ON billing_bill_type = billing_type_id
        WHERE billing_bill_client = {$client};
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return new FloatInt((int) ($result[0]['net_sales'] ?? 0));
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientMRR() : FloatInt
    {
        return new FloatInt(0);
    }

    /**
     * Placeholder
     * @todo Implement
     */
    public static function getClientLastOrder(int $client) : ?\DateTime
    {
        $sql = <<<SQL
        SELECT billing_bill_created_at
        FROM billing_bill
        WHERE billing_bill_client = {$client}
        ORDER BY billing_bill_created_at DESC
        LIMIT 1;
        SQL;

        $query  = new Builder(self::$db);
        $result = $query->raw($sql)->execute()?->fetchAll(\PDO::FETCH_ASSOC) ?? [];

        return isset($result[0]['billing_bill_created_at'])
            ? new \DateTime(($result[0]['billing_bill_created_at']))
            : null;
    }
}
