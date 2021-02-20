<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Admin\Install;

use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\DataStorage\Database\Schema\Builder;

/**
 * WarehouseManagement class.
 *
 * @package Modules\Billing\Admin\Install
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class WarehouseManagement
{
    /**
     * Install comment relation
     *
     * @param string       $path   Module path
     * @param DatabasePool $dbPool Database pool for database interaction
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(string $path, DatabasePool $dbPool) : void
    {
        $builder = new Builder($dbPool->get('schema'));
        $builder->alterTable('billing_bill')
            ->addConstraint('billing_bill_stock_from', 'warehousemgmt_stocklocation', 'warehousemgmt_stocklocation_id')
            ->execute();

        $builder = new Builder($dbPool->get('schema'));
        $builder->alterTable('billing_bill')
            ->addConstraint('billing_bill_stock_to', 'warehousemgmt_stocklocation', 'warehousemgmt_stocklocation_id')
            ->execute();

        $mapper = \file_get_contents(__DIR__ . '/../../Models/BillMapper.php');
        if ($mapper === false) {
            throw new \Exception('Couldn\'t parse mapper');
        }

        $mapper = \str_replace([
            '// @Module WarehouseManagement ',
            '/* @Module WarehouseManagement ',
            ' @Module WarehouseManagement */',
            ], '', $mapper);
        \file_put_contents(__DIR__ . '/../../Models/BillMapper.php', $mapper);

        Autoloader::invalidate(__DIR__ . '/../../Models/BillMapper.php');
    }
}
