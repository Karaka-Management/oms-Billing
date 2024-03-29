<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Billing\Controller\ApiController;
use Modules\Billing\Models\SettingsEnum;

return [
    [
        'type'    => 'setting',
        'name'    => SettingsEnum::VALID_BILL_LANGUAGES,
        'content' => '["en","de"]',
        'pattern' => '',
        'module'  => ApiController::NAME,
    ],
];
