<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin;

use Modules\Billing\Models\BillTransferType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;
use phpOMS\Uri\HttpUri;

/**
 * Installer class.
 *
 * @package Modules\Billing\Admin
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;

    /**
     * {@inheritdoc}
     */
    public static function install(ApplicationAbstract $app, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($app, $info, $cfgHandler);

        // Install bill type templates
        $media = \Modules\Media\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Install/Media2.install.json']);

        /** @var int $defaultTemplate */
        $defaultTemplate = (int) \reset($media['upload'][0]);

        /* Bill types */
        $fileContent = \file_get_contents(__DIR__ . '/Install/types.json');
        if ($fileContent === false) {
            return;
        }

        $types = \json_decode($fileContent, true);
        self::createBillTypes($app, $types, $defaultTemplate);
    }

    /**
     * Install default bill types
     *
     * @param ApplicationAbstract $app      Application
     * @param array               $types    Bill types
     * @param int                 $template Default template
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createBillTypes(ApplicationAbstract $app, array $types, int $template) : array
    {
        $billTypes = [];

        /** @var \Modules\Billing\Controller\ApiController $module */
        $module = $app->moduleManager->getModuleInstance('Billing');

        // @todo: allow multiple alternative bill templates
        // @todo: implement ordering of templates

        foreach ($types as $type) {
            $response = new HttpResponse();
            $request  = new HttpRequest(new HttpUri(''));

            $request->header->account = 1;
            $request->setData('name', $type['name'] ?? '');
            $request->setData('title', \reset($type['l11n']));
            $request->setData('language', \array_keys($type['l11n'])[0] ?? 'en');
            $request->setData('number_format', $type['numberFormat'] ?? '{id}');
            $request->setData('transfer_stock', $type['transferStock'] ?? false);
            $request->setData('transfer_type', $type['transferType'] ?? BillTransferType::SALES);
            $request->setData('template', $template);

            $module->apiBillTypeCreate($request, $response);

            $responseData = $response->get('');
            if (!\is_array($responseData)) {
                continue;
            }

            $billType = !\is_array($responseData['response'])
                ? $responseData['response']->toArray()
                : $responseData['response'];

            $billTypes[] = $billType;

            $isFirst = true;
            foreach ($type['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest(new HttpUri(''));

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $billType['id']);

                $module->apiBillTypeL11nCreate($request, $response);
            }
        }

        return $billTypes;
    }
}
