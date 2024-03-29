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
    '^.*/bill/render(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\ApiBillController:apiMediaRender',
            'verb'       => RouteVerb::GET,
            'csrf'       => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/bill/render/preview(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\ApiBillController:apiPreviewRender',
            'verb'       => RouteVerb::GET,
            'csrf'       => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::SALES_INVOICE,
            ],
        ],
    ],
    '^.*/bill/price(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\ApiPriceController:apiPriceCreate',
            'verb'       => RouteVerb::GET,
            'csrf'       => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::PRICE,
            ],
        ],
    ],
    '^.*/bill/parse(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Billing\Controller\ApiPurchaseController:apiInvoiceParse',
            'verb'       => RouteVerb::SET,
            'csrf'       => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::PURCHASE_INVOICE,
            ],
        ],
    ],
];
