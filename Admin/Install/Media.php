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

use Model\Setting;
use Model\SettingMapper;
use phpOMS\DataStorage\Database\DatabasePool;

/**
 * Media class.
 *
 * @package Modules\Billing\Admin\Install
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class Media
{
    /**
     * Install media providing
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
        $media = \Modules\Media\Admin\Installer::installExternal($dbPool, ['path' => __DIR__ . '/Media.install.json']);

        $defaultTemplate = \reset($media['upload'][0]);

        $setting = new Setting();
        SettingMapper::create($setting->with(0, 'default_template', (string) $defaultTemplate->getId(), 'Billing'));
    }
}
