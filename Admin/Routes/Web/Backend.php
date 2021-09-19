<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Billing\Controller\BackendController;
use Modules\Billing\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/sales/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/warehouse/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/warehouse/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/warehouse/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/analysis/bill(\?.*|$)$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/analysis/rep(\?.*|$)$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewSalesRepAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_ANALYSIS,
            ],
        ],
    ],
    '^.*/sales/analysis/region(\?.*|$)$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewRegionAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::SALES_ANALYSIS,
            ],
        ],
    ],
];
