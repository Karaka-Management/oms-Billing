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
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\OperatingSystem;
use phpOMS\System\SystemType;
use phpOMS\System\SystemUtils;

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

        $bills = [];

        $files = \array_merge($request->files, $request->getDataJson('media'));
        foreach ($files as $file) {
            // Create default bill
            $billRequest                  = new HttpRequest();
            $billRequest->header->account = $request->header->account;
            $billRequest->header->l11n    = $request->header->l11n;
            $billRequest->setData('supplier', 0);
            $billRequest->setData('status', BillStatus::UNPARSED);
            $billRequest->setData('type', $purchaseTransferType->id);

            $billResponse               = new HttpResponse();
            $billResponse->header->l11n = $response->header->l11n;

            $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillCreate($billRequest, $billResponse, $data);

            $billId = $billResponse->getDataArray('')['response']->id;
            $bills[] = $billId;

            // Upload and assign document to bill
            $mediaResponse = new HttpResponse();
            $mediaRequest  = new HttpRequest();

            $mediaResponse->header->l11n = $response->header->l11n;

            $mediaRequest->header->account = $request->header->account;
            $mediaRequest->header->l11n    = $request->header->l11n;

            if (\is_array($file)) {
                $mediaRequest->addFile($file);
            } else {
                $mediaRequest->setData('media', \json_encode($file));
            }

            $mediaRequest->setData('bill', $billId);
            $mediaRequest->setData('type', $originalType);
            $mediaRequest->setData('parse_content', true, true);
            $this->app->moduleManager->get('Billing', 'ApiBill')->apiMediaAddToBill($mediaRequest, $mediaResponse, $data);

            if (\is_array($file)) {
                /** @var \Modules\Media\Models\Media[] $uploaded */
                $uploaded = $mediaResponse->getDataArray('')['response']['upload'];
                if (empty($uploaded)) {
                    $response->header->status = RequestStatusCode::R_400;
                    throw new \Exception();
                }

                $in = \reset($uploaded)->getAbsolutePath();
                if (!\is_file($in)) {
                    $response->header->status = RequestStatusCode::R_400;
                    throw new \Exception();
                }
            }

            // Create internal document
            $billResponse = new HttpResponse();
            $billRequest  = new HttpRequest();

            $billRequest->header->account = $request->header->account;
            $billRequest->setData('bill', $billId);

            $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfArchiveCreate($billRequest, $billResponse);

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
                    $request->getDataBool('async') ?? true
                );
            } catch (\Throwable $t) {
                $response->header->status = RequestStatusCode::R_400;
                $this->app->logger->error($t->getMessage());
            }

            $this->createStandardCreateResponse($request, $response, $bills);
        }
    }
}
