<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
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
 * @link    https://karaka.app
 * @since   1.0.0
 */
class Media
{
    /**
     * Install media providing
     *
     * @param string              $path Module path
     * @param ApplicationAbstract $app  Application
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(string $path, ApplicationAbstract $app) : void
    {
        $media = \Modules\Media\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Media.install.json']);

        $preivewType = (int) \reset($media['type'][0]);

        $setting = new Setting();
        SettingMapper::create()->execute(
            $setting->with(
                0,
                SettingsEnum::PREVIEW_MEDIA_TYPE,
                (string) $preivewType,
                '\\d+',
                null,
                'Billing'
            )
        );
    }
}
