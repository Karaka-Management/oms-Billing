<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin\Install;

use phpOMS\Application\ApplicationAbstract;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Schema\Builder;

/**
 * WarehouseManagement class.
 *
 * @package Modules\Billing\Admin\Install
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class WarehouseManagement
{
    /**
     * Install comment relation
     *
     * @param ApplicationAbstract $app  Application
     * @param string              $path Module path
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public static function install(ApplicationAbstract $app, string $path) : void
    {
        $builder = new Builder($app->dbPool->get('schema'));
        $builder->alterTable('billing_bill')
            ->addConstraint('billing_bill_stock_from', 'warehousemgmt_stocklocation', 'warehousemgmt_stocklocation_id')
            ->execute();

        $builder = new Builder($app->dbPool->get('schema'));
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
