<?php
declare(strict_types=1);

use phpOMS\Router\RouteVerb;

return [
    '^/billing/bill/purchase/parse.*$' => [
        [
            'dest' => '\Modules\Billing\Controller\CliController:cliParseSupplierBill',
            'verb' => RouteVerb::ANY,
        ],
    ],
];
