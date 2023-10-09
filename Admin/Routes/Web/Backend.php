<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Billing\Controller\BackendController;
use Modules\Billing\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/sales/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/sales/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],

    '^.*/purchase/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/purchase/bill/upload\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoiceUpload',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],

    '^.*/warehouse/bill/create.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockInvoiceCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/warehouse/bill/list.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^.*/warehouse/bill\?.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],

    '^.*/private/purchase/billing/dashboard.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPrivatePurchaseBillDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PRIVATE_DASHBOARD,
            ],
        ],
    ],
    '^.*/private/purchase/billing/upload.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPrivatePurchaseBillUpload',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PRIVATE_BILL_UPLOAD,
            ],
        ],
    ],
    '^.*/private/purchase/billing/bill.*$' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPrivateBillingPurchaseInvoice',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PRIVATE_DASHBOARD,
            ],
        ],
    ],
];
