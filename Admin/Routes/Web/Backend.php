<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
    '^/sales/bill/create(\?.*$|$)' => [
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
    '^/sales/bill/list(\?.*$|$)' => [
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
    '^/sales/bill/archive(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingSalesArchive',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^/sales/bill/view(\?.*$|$)' => [
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

    '^/purchase/bill/create(\?.*$|$)' => [
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
    '^/purchase/bill/list(\?.*$|$)' => [
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
    '^/purchase/bill/archive(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseArchive',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^/purchase/bill(\?.*$|$)' => [
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
    '^/purchase/bill/upload(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingPurchaseInvoiceUpload',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],

    '^/warehouse/bill/create(\?.*$|$)' => [
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
    '^/warehouse/bill/list(\?.*$|$)' => [
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
    '^/warehouse/bill/archive(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewBillingStockArchive',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
    '^/warehouse/bill(\?.*$|$)' => [
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

    '^/private/purchase/recognition/dashboard(\?.*$|$)' => [
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
    '^/private/purchase/recognition/upload(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPrivatePurchaseBillUpload',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::PRIVATE_BILL_UPLOAD,
            ],
        ],
    ],
    '^/private/purchase/recognition/bill(\?.*$|$)' => [
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
    '^/purchase/recognition/dashboard(\?.*$|$)' => [
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
    '^/purchase/recognition/upload(\?.*$|$)' => [
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
    '^/purchase/recognition/bill(\?.*$|$)' => [
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

    '^/bill/payment/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPaymentList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PAYMENT_TERM,
            ],
        ],
    ],
    '^/bill/payment/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewPaymentView',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::PAYMENT_TERM,
            ],
        ],
    ],
    '^/bill/shipping/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewShippingList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SHIPPING_TERM,
            ],
        ],
    ],
    '^/bill/shipping/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewShippingView',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SHIPPING_TERM,
            ],
        ],
    ],

    '^/finance/tax/combination/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewTaxCombinationList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TAX,
            ],
        ],
    ],
    '^/finance/tax/combination/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\BackendController:viewTaxCombinationCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TAX,
            ],
        ],
    ],
];
