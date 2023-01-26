<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin\Install;

use Model\Setting;
use Model\SettingMapper;
use Modules\Billing\Models\SettingsEnum;
use phpOMS\Application\ApplicationAbstract;

/**
 * Media class.
 *
 * @package Modules\Billing\Admin\Install
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Media
{
    /**
     * Install media providing
     *
     * @param ApplicationAbstract $app  Application
     * @param string              $path Module path
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(ApplicationAbstract $app, string $path) : void
    {
        $media = \Modules\Media\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Media.install.json']);

        $preivewType  = (int) \reset($media['type'][0]);
        $originalType = (int) \reset($media['type'][1]);

        $setting = new Setting();
        SettingMapper::create()->execute(
            $setting->with(
                0,
                SettingsEnum::PREVIEW_MEDIA_TYPE,
                (string) $preivewType,
                '\\d+',
                module: 'Billing'
            )
        );

        $setting = new Setting();
        SettingMapper::create()->execute(
            $setting->with(
                0,
                SettingsEnum::ORIGINAL_MEDIA_TYPE,
                (string) $originalType,
                '\\d+',
                module: 'Billing'
            )
        );
    }
}
