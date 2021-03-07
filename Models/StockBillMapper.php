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

use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\RelationType;

/**
 * Mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class StockBillMapper extends BillMapper
{
    /**
     * Model to use by the mapper.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $model = Bill::class;

    public static function getStockBeforePivot(
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
        $query->where(BillTypeMapper::getTable() . '_' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::STOCK);

        return self::getBeforePivot($pivot, $column, $limit, $order, $relations, $depth, $query);
    }

    public static function getStockAfterPivot(
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
        $query->where(BillTypeMapper::getTable() . '_' . ($depth - 1) . '.billing_type_transfer_type', '=', BillTransferType::STOCK);

        return self::getAfterPivot($pivot, $column, $limit, $order, $relations, $depth, $query);
    }
}
