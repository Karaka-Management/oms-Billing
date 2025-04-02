<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

return [
    "POST:Billing:bill-create" => [
        'callback' => ['\Modules\Workflow\Controller\ApiController:hookWorkflowChangeState'],
    ],
    "POST:Billing:bill-update" => [
        'callback' => ['\Modules\Workflow\Controller\ApiController:hookWorkflowChangeState'],
    ],

    "POST:Billing:bill_element-create" => [
        'callback' => ['\Modules\Workflow\Controller\ApiController:hookWorkflowChangeState'],
    ],
    "POST:Billing:bill_element-update" => [
        'callback' => ['\Modules\Workflow\Controller\ApiController:hookWorkflowChangeState'],
    ],
];
