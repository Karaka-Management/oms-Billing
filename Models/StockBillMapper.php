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

use phpOMS\DataStorage\Database\Query\Builder;

/**
 * StockBill mapper class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class StockBillMapper extends BillMapper
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
    public static function getStockBeforePivot(
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
            ->where('transferType', BillTransferType::SALES)
            ->limit($limit)
            ->executeGetArray();
    }

    /**
     * Placeholder
     */
    public static function getStockAfterPivot(
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
            ->where('transferType', BillTransferType::SALES)
            ->limit($limit)
            ->executeGetArray();
    }
}
