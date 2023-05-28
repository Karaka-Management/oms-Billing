<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin\Install;

use Modules\Billing\Models\SettingsEnum;
use phpOMS\Application\ApplicationAbstract;

/**
 * Media class.
 *
 * @package Modules\Billing\Admin\Install
 * @license OMS License 2.0
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

        \Modules\Admin\Admin\Installer::installExternal(
            $app,
            [
                'data' => [
                    [
                        'type'    => 'setting',
                        'name'    => SettingsEnum::PREVIEW_MEDIA_TYPE,
                        'content' => (string) $media['type'][0]['id'],
                        'pattern' => '\\d+',
                        'module'  => 'Billing',
                    ],
                    [
                        'type'    => 'setting',
                        'name'    => SettingsEnum::ORIGINAL_MEDIA_TYPE,
                        'content' => (string) $media['type'][1]['id'],
                        'pattern' => '\\d+',
                        'module'  => 'Billing',
                    ],
                ],
            ]
        );
    }
}
