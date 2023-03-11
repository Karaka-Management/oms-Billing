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
use Modules\Admin\Models\SettingsEnum as AdminSettingsEnum;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTypeMapper;
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
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\Money;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\System\MimeType;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiBillController extends Controller
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
                ->with('account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('client'))
                ->execute();
        } elseif (((int) ($request->getData('supplier') ?? -1)) === 0) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = new NullSupplier();
        } elseif ($request->getData('supplier') !== null) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = SupplierMapper::get()
                ->with('account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('supplier'))
                ->execute();
        }

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()
            ->where('id', (int) ($request->getData('type') ?? 1))
            ->execute();

        /* @var \Modules\Account\Models\Account $account */
        $bill               = new Bill();
        $bill->createdBy    = new NullAccount($request->header->account);
        $bill->type         = $billType;
        $bill->numberFormat = $billType->numberFormat;
        // @todo: use defaultInvoiceAddress or mainAddress. also consider to use billto1, billto2, billto3 (for multiple lines e.g. name2, fao etc.)
        $bill->billTo          = (string) ($request->getData('billto')
            ?? ($account->account->name1 . (!empty($account->account->name2)
                ? ', ' . $account->account->name2
                : ''
            )));
        $bill->billAddress     = (string) ($request->getData('billaddress') ?? $account->mainAddress->address);
        $bill->billZip         = (string) ($request->getData('billtopostal') ?? $account->mainAddress->postal);
        $bill->billCity        = (string) ($request->getData('billtocity') ?? $account->mainAddress->city);
        $bill->billCountry     = (string) (
            $request->getData('billtocountry') ?? (
                ($country = $account->mainAddress->getCountry()) ===  ISO3166TwoEnum::_XXX ? '' : $country)
            );
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
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH,
                hasAccountRelation: false,
                readContent: (bool) ($request->getData('parse_content') ?? false)
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

                if ($request->hasData('type')) {
                    $this->createModelRelation(
                        $request->header->account,
                        $media->getId(),
                        $request->getData('type', 'int'),
                        MediaMapper::class,
                        'types',
                        '',
                        $request->getOrigin()
                    );
                }

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

                $this->createModelRelation(
                    $request->header->account,
                    $collection->getId(),
                    $media->getId(),
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            foreach ($mediaFiles as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $bill->getId(),
                    (int) $media,
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
            . $this->app->unitId . '/'
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
        $item = ItemMapper::get()
            ->with('l11n')
            ->with('l11n/type')
            ->where('id', $element->item)
            ->where('l11n/type/title', ['name1', 'name2', 'name3'], 'IN')
            ->where('l11n/language', $response->getLanguage())
            ->execute();

        $element->itemNumber = $item->number;
        $element->itemName   = $item->getL11n('name1')->description;
        $element->quantity   = (int) ($request->getData('quantity') ?? 0);

        $element->singleSalesPriceNet = new Money($request->getData('singlesalespricenet', 'int') ?? $item->salesPrice->getInt());
        $element->totalSalesPriceNet  = clone $element->singleSalesPriceNet;
        $element->totalSalesPriceNet->mult($element->quantity);

        // discounts
        if ($request->getData('discount_percentage') !== null) {
            $discount = (int) $request->getData('discount_percentage');

            $element->singleSalesPriceNet
                ->sub((int) ($element->singleSalesPriceNet->getInt() / 100 * $discount));

            $element->totalSalesPriceNet
                ->sub((int) ($element->totalSalesPriceNet->getInt() / 100 * $discount));
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

    public function apiPreviewRender(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        $templateId = $request->getData('bill_template', 'int');
        if ($templateId === null) {
            $billTypeId = $request->getData('bill_type', 'int');
            $billType = BillTypeMapper::get()
                ->where('id', $billTypeId)
                ->execute();

            $templateId = $billType->defaultTemplate->getId();
        }

        $template = CollectionMapper::get()
            ->with('sources')
            ->where('id', $templateId)
            ->execute();

        require_once __DIR__ . '/../../../Resources/tcpdf/tcpdf.php';

        $response->header->set('Content-Type', MimeType::M_PDF, true);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

        $settings = $this->app->appSettings->get(null,
            [
                AdminSettingsEnum::DEFAULT_TEMPLATES,
                AdminSettingsEnum::DEFAULT_ASSETS,
            ],
            unit: $this->app->unitId,
            module: 'Admin'
        );

        $postKey = '::' . $this->app->unitId . ':Admin';

        if ($settings === false) {
            $settings = $this->app->appSettings->get(null,
                [
                    AdminSettingsEnum::DEFAULT_TEMPLATES,
                    AdminSettingsEnum::DEFAULT_ASSETS,
                ],
                unit: null,
                module: 'Admin'
            );

            $postKey = ':::Admin';
        }

        $defaultTemplates = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES . $postKey]->content)
            ->execute();

        $defaultAssets = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS . $postKey]->content)
            ->execute();

        $view->setData('defaultTemplates', $defaultTemplates);
        $view->setData('defaultAssets', $defaultAssets);

        $view->setData('bill', $bill);
        $view->setData('path', $pdfDir . '/' . $request->getData('bill') . '.pdf');

        $view->setData('bill_creator', $request->getData('bill_creator'));
        $view->setData('bill_title', $request->getData('bill_title'));
        $view->setData('bill_subtitle', $request->getData('bill_subtitle'));
        $view->setData('keywords', $request->getData('keywords'));
        $view->setData('bill_logo_name', $request->getData('bill_logo_name'));
        $view->setData('bill_slogan', $request->getData('bill_slogan'));

        $view->setData('legal_company_name', $request->getData('legal_company_name'));
        $view->setData('bill_company_address', $request->getData('bill_company_address'));
        $view->setData('bill_company_city', $request->getData('bill_company_city'));
        $view->setData('bill_company_ceo', $request->getData('bill_company_ceo'));
        $view->setData('bill_company_website', $request->getData('bill_company_website'));
        $view->setData('bill_company_email', $request->getData('bill_company_email'));
        $view->setData('bill_company_phone', $request->getData('bill_company_phone'));

        $view->setData('bill_company_tax_office', $request->getData('bill_company_tax_office'));
        $view->setData('bill_company_tax_id', $request->getData('bill_company_tax_id'));
        $view->setData('bill_company_vat_id', $request->getData('bill_company_vat_id'));

        $view->setData('bill_company_bank_name', $request->getData('bill_company_bank_name'));
        $view->setData('bill_company_bic', $request->getData('bill_company_bic'));
        $view->setData('bill_company_iban', $request->getData('bill_company_iban'));

        $view->setData('bill_type_name', $request->getData('bill_type_name'));

        $view->setData('bill_invoice_no', $request->getData('bill_invoice_no'));
        $view->setData('bill_invoice_date', $request->getData('bill_invoice_date'));
        $view->setData('bill_service_date', $request->getData('bill_service_date'));
        $view->setData('bill_customer_no', $request->getData('bill_customer_no'));
        $view->setData('bill_po', $request->getData('bill_po'));
        $view->setData('bill_due_date', $request->getData('bill_due_date'));

        $view->setData('bill_start_text', $request->getData('bill_start_text'));
        $view->setData('bill_lines', $request->getData('bill_lines'));
        $view->setData('bill_end_text', $request->getData('bill_end_text'));

        $view->setData('bill_payment_terms', $request->getData('bill_payment_terms'));
        $view->setData('bill_terms', $request->getData('bill_terms'));
        $view->setData('bill_taxes', $request->getData('bill_taxes'));
        $view->setData('bill_currency', $request->getData('bill_currency'));

        $pdf = $view->render();

        $response->set('', $pdf);
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
            ->with('elements')
            ->where('id', $request->getData('bill') ?? 0)
            ->execute();

        $templateId = $request->getData('bill_template', 'int');
        if ($templateId === null) {
            $billTypeId = $bill->type->getId();
            $billType = BillTypeMapper::get()
                ->where('id', $billTypeId)
                ->execute();

            $templateId = $billType->defaultTemplate->getId();
        }

        $template = CollectionMapper::get()
            ->with('sources')
            ->where('id', $templateId)
            ->execute();

        require_once __DIR__ . '/../../../Resources/tcpdf/tcpdf.php';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

        $settings = $this->app->appSettings->get(null,
            [
                AdminSettingsEnum::DEFAULT_TEMPLATES,
                AdminSettingsEnum::DEFAULT_ASSETS,
            ],
            unit: $this->app->unitId,
            module: 'Admin'
        );

        $postKey = '::' . $this->app->unitId . ':Admin';

        if ($settings === false) {
            $settings = $this->app->appSettings->get(null,
                [
                    AdminSettingsEnum::DEFAULT_TEMPLATES,
                    AdminSettingsEnum::DEFAULT_ASSETS,
                ],
                unit: null,
                module: 'Admin'
            );

            $postKey = ':::Admin';
        }

        $defaultTemplates = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES . $postKey]->content)
            ->execute();

        $defaultAssets = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS . $postKey]->content)
            ->execute();

        $view->setData('defaultTemplates', $defaultTemplates);
        $view->setData('defaultAssets', $defaultAssets);

        /**
            @todo: pass data to bill
        */

        $pdf = $view->render();

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

        \file_put_contents($pdfDir . '/' . $request->getData('bill') . '.pdf', $pdf);
        if (!\is_file($pdfDir . '/' . $request->getData('bill') . '.pdf')) {
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $media = $this->app->moduleManager->get('Media')->createDbEntry(
            status: [
                'status'    => UploadStatus::OK,
                'name'      => $request->getData('bill') . '.pdf',
                'path'      => $pdfDir,
                'filename'  => $request->getData('bill') . '.pdf',
                'size'      => \filesize($pdfDir . '/' . $request->getData('bill') . '.pdf'),
                'extension' => 'pdf',
            ],
            account: $request->header->account,
            virtualPath: $path,
            ip: $request->getOrigin(),
            app: $this->app,
            readContent: true,
            unit: $this->app->unitId
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
        $this->createModelRelation($request->header->account, $request->getData('id'), $model->getId(), BillMapper::class, 'bill_note', '', $request->getOrigin());
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
}
