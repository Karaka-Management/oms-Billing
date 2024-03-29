<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Billing\Models\BillMapper;
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
     * apiSupplierBillUpload
     *   -> apiBillCreate
     *      -> [createBill]
     *   -> apiMediaAddToBill
     *   -> apiInvoiceParse
     *       -> cliParseSupplierBill
     *           -> [updateBill]
     *           -> apiBillPdfArchiveCreate
     *               -> eventBillArchive
     *
     * @since 1.0.0
     */
    public function apiSupplierBillUpload(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateSupplierBillUpload($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $bills = $this->createSupplierBillUploadFromRequest($request, $response, $data);
        if (empty($bills)) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);
        }

        $this->createStandardCreateResponse($request, $response, $bills);
    }

    /**
     * Method to create item attribute from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function createSupplierBillUploadFromRequest(RequestAbstract $request, ResponseAbstract $response, $data) : array
    {
        /** @var \Model\Setting $setting */
        $setting = $this->app->appSettings->get(
            names: SettingsEnum::EXTERNAL_MEDIA_TYPE,
            module: self::NAME
        );

        $internalType = $request->getDataInt('type') ?? ((int) $setting->content);

        /** @var \Modules\Billing\Models\BillType $purchaseTransferType */
        $purchaseTransferType = BillTypeMapper::get()
            ->where('transferType', BillTransferType::PURCHASE)
            ->limit(1)
            ->execute();

        $bills = [];

        $files = \array_merge($request->files, $request->getDataJson('media'));

        /** @var \Model\Setting[] $settings */
        $settings = $this->app->appSettings->get(null,
            [SettingsEnum::BILLING_DOCUMENT_SPACER_COLOR, SettingsEnum::BILLING_DOCUMENT_SPACER_TOLERANCE],
            unit: $this->app->unitId,
            module: self::NAME
        );

        if (empty($settings)) {
            /** @var \Model\Setting[] $settings */
            $settings = $this->app->appSettings->get(null,
                [SettingsEnum::BILLING_DOCUMENT_SPACER_COLOR, SettingsEnum::BILLING_DOCUMENT_SPACER_TOLERANCE],
                unit: null,
                module: self::NAME
            );
        }

        foreach ($files as $file) {
            // 1. convert to image pdftoppm
            // 2. search for color pages by using averageColorRandom (tolerance < 175 = color match)
            // 3. split pdf document if necessary
            //      sudo apt-get --yes install pdftk
            //      pdftk foo-bar.pdf cat 1-12 output foo.pdf
            //      pdftk foo-bar.pdf cat 13-end output bar.pdf
            //      alternatively, pdfseparate -f 1 -l 5 input.pdf output-page%d.pdf
            //      alternatively, pdfjam <input-file> <page-ranges> -o <output-file>
            //      alternatively, pdfly cat in.pdf 2:4 -o out.pdf
            // 4. add to documents array
        }

        $documents = $files;

        foreach ($documents as $file) {
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

            $billId  = $billResponse->getDataArray('')['response']->id;
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
            $mediaRequest->setData('type', $internalType);
            $mediaRequest->setData('parse_content', true, true);
            $this->app->moduleManager->get('Billing', 'ApiBill')->apiMediaAddToBill($mediaRequest, $mediaResponse, $data);

            if (\is_array($file)) {
                /** @var \Modules\Media\Models\Media[] $uploaded */
                $uploaded = $mediaResponse->getDataArray('')['response']['upload'];
                if (empty($uploaded)) {
                    return [];
                }

                $in = \reset($uploaded)->getAbsolutePath();
                if (!\is_file($in)) {
                    return [];
                }
            }

            $request->setData('id', $billId, true);
            $request->setData('bill', $billId, true);

            $this->apiInvoiceParse($request, $response, $data);
        }

        return $bills;
    }

    /**
     * Validate item attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateSupplierBillUpload(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['files'] = empty($request->files))) {
            return $val;
        }

        return [];
    }

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
    public function apiInvoiceParse(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateInvoiceParse($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $bill = BillMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        // After a bill is "closed" its values shouldn't change
        if ($bill->status !== BillStatus::DRAFT
            && $bill->status !== BillStatus::UNPARSED
            && $bill->status !== BillStatus::ACTIVE
        ) {
            $response->header->status = RequestStatusCode::R_423;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        // Offload bill parsing to cli
        $cliPath = \realpath(__DIR__ . '/../../../cli.php');
        if ($cliPath === false) {
            return;
        }

        try {
            SystemUtils::runProc(
                OperatingSystem::getSystem() === SystemType::WIN ? 'php.exe' : 'php',
                    '-dxdebug.remote_enable=1 -dxdebug.start_with_request=yes -dxdebug.mode=coverage,develop,debug ' .
                    \escapeshellarg($cliPath)
                    . ' /billing/bill/purchase/parse '
                    . '-i ' . \escapeshellarg((string) $bill->id),
                $request->getDataBool('async') ?? true
            );
        } catch (\Throwable $t) {
            $response->header->status = RequestStatusCode::R_400;
            $this->app->logger?->error($t->getMessage());
        }

        $this->createStandardUpdateResponse($request, $response, $bill);
    }

    /**
     * Validate item attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateInvoiceParse(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
