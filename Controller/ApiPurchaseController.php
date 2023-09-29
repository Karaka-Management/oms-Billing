<?php

/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\SettingsEnum;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\OperatingSystem;
use phpOMS\System\SystemType;
use phpOMS\System\SystemUtils;
use phpOMS\Uri\HttpUri;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiPurchaseController extends Controller
{
    /**
     * Api method to create bill files
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public function apiSupplierBillUpload(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Model\Setting $setting */
        $setting = $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        );

        $originalType = $request->getDataInt('type') ?? ((int) $setting->content);

        /** @var \Modules\Billing\Models\BillType $purchaseTransferType */
        $purchaseTransferType = BillTypeMapper::get()
            ->where('transferType', BillTransferType::PURCHASE)
            ->limit(1)
            ->execute();

        $files = $request->files;
        foreach ($files as $file) {
            // Create default bill
            $billRequest                  = new HttpRequest(new HttpUri(''));
            $billRequest->header->account = $request->header->account;
            $billRequest->header->l11n    = $request->header->l11n;
            $billRequest->setData('supplier', 0);
            $billRequest->setData('status', BillStatus::UNPARSED);
            $billRequest->setData('type', $purchaseTransferType->id);

            $billResponse               = new HttpResponse();
            $billResponse->header->l11n = $response->header->l11n;

            $this->app->moduleManager->get('Billing', 'Api')->apiBillCreate($billRequest, $billResponse, $data);

            $billId = $billResponse->getDataArray('')['response']->id;

            // Upload and assign document to bill
            $mediaRequest                  = new HttpRequest();
            $mediaRequest->header->account = $request->header->account;
            $mediaRequest->header->l11n    = $request->header->l11n;
            $mediaRequest->addFile($file);

            $mediaResponse               = new HttpResponse();
            $mediaResponse->header->l11n = $response->header->l11n;

            $mediaRequest->setData('bill', $billId);
            $mediaRequest->setData('type', $originalType);
            $mediaRequest->setData('parse_content', true, true);
            $this->app->moduleManager->get('Billing', 'Api')->apiMediaAddToBill($mediaRequest, $mediaResponse, $data);

            /** @var \Modules\Media\Models\Media[] $uploaded */
            $uploaded = $mediaResponse->getDataArray('')['response']['upload'];
            if (empty($uploaded)) {
                throw new \Exception();
            }

            $in = \reset($uploaded)->getAbsolutePath(); // pdf parsed content is available in $in->content
            if (!\is_file($in)) {
                throw new \Exception();
            }

            // Create internal document
            $billResponse = new HttpResponse();
            $billRequest  = new HttpRequest(new HttpUri(''));

            $billRequest->header->account = $request->header->account;
            $billRequest->setData('bill', $billId);

            $this->app->moduleManager->get('Billing', 'Api')->apiBillPdfArchiveCreate($billRequest, $billResponse);

            // Offload bill parsing to cli
            $cliPath = \realpath(__DIR__ . '/../../../cli.php');
            if ($cliPath === false) {
                return;
            }

            try {
                SystemUtils::runProc(
                    OperatingSystem::getSystem() === SystemType::WIN ? 'php.exe' : 'php',
                    \escapeshellarg($cliPath)
                        . ' /billing/bill/purchase/parse '
                        . '-i ' . \escapeshellarg((string) $billId),
                    true
                );
            } catch (\Throwable $t) {
                $this->app->logger->error($t->getMessage());
            }
        }
    }
}
