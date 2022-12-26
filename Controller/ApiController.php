<?php

/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */

declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\SettingsEnum;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\UploadStatus;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Autoloader;
use phpOMS\Localization\Money;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Uri\HttpUri;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to update a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillUpdate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()->where('id', (int) $request->getData('bill'));
        $new = $this->updateBillFromRequest($request, $response, $data);
        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Bill', 'Bill successfully created.', $new);
    }

    /**
     * Method to validate bill creation from request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['bill'] = empty($request->getData('bill')))) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create a bill from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function updateBillFromRequest(RequestAbstract $request, ResponseAbstract $response, $data = null) : Bill
    {
        /** @var Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();

        return $bill;
    }

    /**
     * Api method to create a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $bill = $this->createBillFromRequest($request, $response, $data);
        $this->createModel($request->header->account, $bill, BillMapper::class, 'bill', $request->getOrigin());

        $new = clone $bill;
        $new->buildNumber(); // The bill id is part of the number
        $this->updateModel($request->header->account, $bill, $new, BillMapper::class, 'bill', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Bill', 'Bill successfully created.', $bill);
    }

    /**
     * Method to create a bill from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function createBillFromRequest(RequestAbstract $request, ResponseAbstract $response, $data = null) : Bill
    {
        /** @var \Modules\ClientManagement\Models\Client|\Modules\SupplierManagement\Models\Supplier $account */
        $account = null;
        if ($request->getData('client') !== null) {
            /** @var \Modules\ClientManagement\Models\Client $account */
            $account = ClientMapper::get()
                ->with('profile')
                ->with('profile/account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('client'))
                ->execute();
        } elseif (((int) ($request->getData('supplier') ?? -1)) === 0) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = new NullSupplier();
        } elseif ($request->getData('supplier') !== null) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = SupplierMapper::get()
                ->with('profile')
                ->with('profile/account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('supplier'))
                ->execute();
        }

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()->where('id', (int) ($request->getData('type') ?? 1))->execute();

        /* @var \Modules\Account\Models\Account $account */
        $bill               = new Bill();
        $bill->createdBy    = new NullAccount($request->header->account);
        $bill->type         = $billType;
        $bill->numberFormat = $billType->numberFormat;
        // @todo: use defaultInvoiceAddress or mainAddress. also consider to use billto1, billto2, billto3 (for multiple lines e.g. name2, fao etc.)
        $bill->billTo          = $request->getData('billto')
            ?? ($account->profile->account->name1 . (!empty($account->profile->account->name2)
                ? ', ' . $account->profile->account->name2
                : ''
            ));
        $bill->billAddress     = $request->getData('billaddress') ?? $account->mainAddress->address;
        $bill->billZip         = $request->getData('billtopostal') ?? $account->mainAddress->postal;
        $bill->billCity        = $request->getData('billtocity') ?? $account->mainAddress->city;
        $bill->billCountry     = $request->getData('billtocountry') ?? $account->mainAddress->getCountry();
        $bill->client          = !$request->hasData('client') ? null : $account;
        $bill->supplier        = !$request->hasData('supplier') ? null : $account;
        $bill->performanceDate = new \DateTime($request->getData('performancedate') ?? 'now');
        $bill->setStatus((int) ($request->getData('status') ?? BillStatus::ACTIVE));

        return $bill;
    }

    /**
     * Method to validate bill creation from request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['client/supplier'] = (empty($request->getData('client'))
                && (empty($request->getData('supplier'))
                    && ((int) ($request->getData('supplier') ?? -1) !== 0)
                )))
            || ($val['type'] = (empty($request->getData('type'))))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiMediaAddToBill(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateMediaAddToBill($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();
        $path = $this->createBillDir($bill);

        $uploaded = [];
        if (!empty($uploadedFiles = $request->getFiles())) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [],
                [],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path,
                $path,
                type: $request->getData('type') ?? null,
                pathSettings: PathSettings::FILE_PATH,
                hasAccountRelation: false
            );

            $collection = null;
            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $bill->getId(),
                    $media->getId(),
                    BillMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );

                if ($collection === null) {
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection instanceof NullCollection) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $path,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files' . $path,
                        );
                    }
                }

                CollectionMapper::writer()->createRelationTable('sources', [$media->getId()], $collection->getId());
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            foreach ($mediaFiles as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $bill->getId(),
                    $media,
                    BillMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );
            }
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Media', 'Media added to bill.', [
            'upload' => $uploaded,
            'media'  => $mediaFiles,
        ]);
    }

    /**
     * Create media directory path
     *
     * @param Bill $bill Bill
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createBillDir(Bill $bill) : string
    {
        return '/Modules/Billing/Bills/'
            . $bill->createdAt->format('Y') . '/'
            . $bill->createdAt->format('m') . '/'
            . $bill->createdAt->format('d') . '/'
            . $bill->getId();
    }

    /**
     * Method to validate bill creation from request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateMediaAddToBill(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['media'] = (empty($request->getData('media')) && empty($request->getFiles())))
            || ($val['bill'] = empty($request->getData('bill')))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a bill element
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillElementCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillElementCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $element = $this->createBillElementFromRequest($request, $response, $data);
        $this->createModel($request->header->account, $element, BillElementMapper::class, 'bill_element', $request->getOrigin());

        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()->where('id', $element->bill)->execute();
        $new = $this->updateBillWithBillElement(clone $old, $element, 1);
        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill_element', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Bill element', 'Bill element successfully created.', $element);
    }

    /**
     * Method to create a bill element from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return BillElement
     *
     * @since 1.0.0
     * @todo in the database the customer localized version should be stored because this is the version which went out
     */
    public function createBillElementFromRequest(RequestAbstract $request, ResponseAbstract $response, $data = null) : BillElement
    {
        $element       = new BillElement();
        $element->bill = (int) $request->getData('bill');
        $element->item = (int) ($request->getData('item') ?? 0);

        if ($element->item === null) {
            return $element;
        }

        /** @var \Modules\ItemManagement\Models\Item $item */
        $item                = ItemMapper::get()->with('l11n')->where('id', $element->item)->where('l11n/language', $response->getLanguage())->execute();
        $element->itemNumber = $item->number;
        $element->itemName   = $item->getL11n('name1')->description;
        $element->quantity   = $request->getData('quantity', 'int');

        $element->singleSalesPriceNet = new Money($request->getData('singlesalespricenet', 'int') ?? $item->salesPrice->getInt());
        $element->totalSalesPriceNet  = clone $element->singleSalesPriceNet;
        $element->totalSalesPriceNet->mult($element->quantity);

        // discounts
        if ($request->getData('discount_percentage') !== null) {
            $element->singleSalesPriceNet->sub((int) ($element->singleSalesPriceNet->getInt() / 100 * $request->getData('discount_percentage', 'int')));
            $element->totalSalesPriceNet->sub((int) ($element->totalSalesPriceNet->getInt() / 100 * $request->getData('discount_percentage', 'int')));
        }

        $element->singlePurchasePriceNet = new Money($item->purchasePrice->getInt());
        $element->totalPurchasePriceNet  = clone $element->singlePurchasePriceNet;
        $element->totalPurchasePriceNet->mult($element->quantity);

        return $element;
    }

    /**
     * Method to update a bill because of a changed bill element (add, remove, change) from request.
     *
     * @param Bill        $bill    Bill
     * @param BillElement $element Bill element
     * @param int         $type    Change type (0 = update, -1 = remove, +1 = add)
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function updateBillWithBillElement(Bill $bill, BillElement $element, int $type = 1) : Bill
    {
        if ($type === 1) {
            $bill->netSales->add($element->totalSalesPriceNet);
            $bill->netCosts->add($element->totalPurchasePriceNet);
        }

        return $bill;
    }

    /**
     * Method to validate bill element creation from request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillElementCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['bill'] = empty($request->getData('bill')))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create and archive a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillPdfArchiveCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('type')
            ->with('type/template')
            ->with('type/template/sources')
            ->where('id', $request->getData('bill') ?? 0)
            ->execute();

        $previewType = (int) $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $template = $bill->type->template;

        $path   = $this->createBillDir($bill);
        $pdfDir = __DIR__ . '/../../../Modules/Media/Files' . $path;

        $status = !\is_dir($pdfDir) ? \mkdir($pdfDir, 0755, true) : true;
        if ($status === false) {
            // @codeCoverageIgnoreStart
            $response->set($request->uri->__toString(), new FormValidation(['status' => $status]));
            $response->header->status = RequestStatusCode::R_400;

            return;
            // @codeCoverageIgnoreEnd
        }

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');
        $view->setData('bill', $bill);
        $view->setData('path', $pdfDir . '/' . $request->getData('bill') . '.pdf');

        $pdf = $view->build();

        if (!\is_file($pdfDir . '/' . $request->getData('bill') . '.pdf')) {
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $media = $this->app->moduleManager->get('Media')->createDbEntry(
            [
                'status'    => UploadStatus::OK,
                'name'      => $request->getData('bill') . '.pdf',
                'path'      => $pdfDir,
                'filename'  => $request->getData('bill') . '.pdf',
                'size'      => \filesize($pdfDir . '/' . $request->getData('bill') . '.pdf'),
                'extension' => 'pdf',
            ],
            $request->header->account,
            $path,
            $previewType
        );

        $this->createModelRelation(
            $request->header->account,
            $bill->getId(),
            $media->getId(),
            BillMapper::class,
            'media',
            '',
            $request->getOrigin()
        );

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'PDF', 'Bill Pdf successfully created.', $media);
    }

    /**
     * Api method to create a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillPdfCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
    }

    /**
     * Api method to create bill files
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNoteCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateNoteCreate($request))) {
            $response->set('bill_note_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('id'))->execute();

        $request->setData('virtualpath', $this->createBillDir($bill), true);
        $this->app->moduleManager->get('Editor')->apiEditorCreate($request, $response, $data);

        if ($response->header->status !== RequestStatusCode::R_200) {
            return;
        }

        $model = $response->get($request->uri->__toString())['response'];
        $this->createModelRelation($request->header->account, $request->getData('id'), $model->getId(), BillMapper::class, 'notes', '', $request->getOrigin());
    }

    /**
     * Validate bill note create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateNoteCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = empty($request->getData('id')))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create bill files
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiSupplierBillUpload(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $originalType = (int) ($request->getData('type') ?? $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        )->content);

        /** @var \Modules\Billing\Models\BillType $purchaseTransferType */
        $purchaseTransferType = BillTypeMapper::get()
            ->where('transferType', BillTransferType::PURCHASE)
            ->limit(1)
            ->execute();

        $files = $request->getFiles();
        foreach ($files as $file) {
            // Create default bill
            $billRequest                  = new HttpRequest(new HttpUri(''));
            $billRequest->header->account = $request->header->account;
            $billRequest->header->l11n    = $request->header->l11n;
            $billRequest->setData('supplier', 0);
            $billRequest->setData('status', BillStatus::UNPARSED);
            $billRequest->setData('type', $purchaseTransferType->getId());

            $billResponse               = new HttpResponse();
            $billResponse->header->l11n = $response->header->l11n;

            $this->apiBillCreate($billRequest, $billResponse, $data);

            $billId = $billResponse->get('')['response']->getId();

            // Upload and assign document to bill
            $mediaRequest                  = new HttpRequest();
            $mediaRequest->header->account = $request->header->account;
            $mediaRequest->header->l11n    = $request->header->l11n;
            $mediaRequest->addFile($file);

            $mediaResponse               = new HttpResponse();
            $mediaResponse->header->l11n = $response->header->l11n;

            $mediaRequest->setData('bill', $billId);
            $mediaRequest->setData('type', $originalType);
            $this->apiMediaAddToBill($mediaRequest, $mediaResponse, $data);

            $uploaded = $mediaResponse->get('')['response']['upload'];
            $in       = \reset($uploaded)->getAbsolutePath(); // pdf is parsed in $in->content

            if (!\is_file($in)) {
                throw new \Exception();
            }

            // @todo: Parse text and analyze text structure

            // Update bill with parsed text
            $billRequest                  = new HttpRequest();
            $billRequest->header->account = $request->header->account;
            $billRequest->header->l11n    = $request->header->l11n;

            $billRequest->setData('bill', $billId);
            $billRequest->setData('supplier', 1);

            $billResponse               = new HttpResponse();
            $billResponse->header->l11n = $response->header->l11n;

            $this->apiBillUpdate($billRequest, $billResponse, $data);

            // Create internal document
            $billResponse = new HttpResponse();
            $billRequest  = new HttpRequest(new HttpUri(''));

            $billRequest->header->account = $request->header->account;
            $billRequest->setData('bill', $billId);

            $this->apiBillPdfArchiveCreate($billRequest, $billResponse);

            // @todo: Start workflow for bill, if a workflow is defined for bill uploading
        }
    }
}
