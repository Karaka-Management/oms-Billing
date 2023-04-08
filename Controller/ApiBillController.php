<?php

/**
 * Karaka
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

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\SettingsEnum as AdminSettingsEnum;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\SettingsEnum;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\Finance\Models\TaxCode;
use Modules\Finance\Models\TaxCodeMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemL11nMapper;
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
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO639x1Enum;
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
 * @license OMS License 2.0
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
        if (($val['bill'] = !$request->hasData('bill'))) {
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
        $this->createBillDatabaseEntry($bill, $request);

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Bill', 'Bill successfully created.', $bill);
    }

    /**
     * Create a new database entry for a Bill object and update its bill number
     *
     * @param Bill            $bill    The Bill object to create a database entry for and update its bill number
     * @param RequestAbstract $request The request object that contains the header account and origin
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function createBillDatabaseEntry(Bill $bill, RequestAbstract $request) : void
    {
        $this->createModel($request->header->account, $bill, BillMapper::class, 'bill', $request->getOrigin());

        $old = clone $bill;
        $bill->buildNumber(); // The bill id is part of the number
        $this->updateModel($request->header->account, $old, $bill, BillMapper::class, 'bill', $request->getOrigin());
    }

    /**
     * Create a base Bill object with default values
     *
     * @param Client           $client  The client object for whom the bill is being created
     * @param RequestAbstract  $request The request object that contains the header account
     *
     * @return Bill                     The new Bill object with default values
     *
     * @todo Validate VAT before creation (maybe need to add a status when last validated, we don't want to validate every time)
     * @todo Set the correct date of payment
     * @todo Use bill and shipping address instead of main address if available
     * @todo Implement allowed invoice languages and a default invoice language if none match
     * @todo Implement client invoice language (allowing for different invoice languages than invoice address)
     *
     * @since 1.0.0
     */
    public function createBaseBill(Client $client, RequestAbstract $request) : Bill
    {
        // @todo: validate vat before creation
        $bill = new Bill();
        $bill->setStatus(BillStatus::DRAFT);
        $bill->createdBy = new NullAccount($request->header->account);
        $bill->billDate  = new \DateTime('now'); // @todo: Date of payment
        $bill->performanceDate  = new \DateTime('now'); // @todo: Date of payment
        $bill->accountNumber = $client->number;

        $bill->shipping = 0;
        $bill->shippingText = '';

        $bill->payment = 0;
        $bill->paymentText = '';

        $bill->type = BillTypeMapper::get()
            ->where('name', 'sales_invoice')
            ->execute();

        // @todo: use bill and shipping address instead of main address if available
        $bill->client      = $client;
        $bill->billTo      = $client->account->name1;
        $bill->billAddress = $client->mainAddress->address;
        $bill->billCity    = $client->mainAddress->city;
        $bill->billZip     = $client->mainAddress->postal;
        $bill->billCountry = $client->mainAddress->getCountry();

        $bill->setCurrency(ISO4217CharEnum::_EUR);

        /** @var \Model\Setting $settings */
        $settings = $this->app->appSettings->get(null,
            SettingsEnum::VALID_BILL_LANGUAGES,
            unit: $this->app->unitId,
            module: 'Admin'
        );

        if (empty($settings)) {
            /** @var \Model\Setting $settings */
            $settings = $this->app->appSettings->get(null,
            SettingsEnum::VALID_BILL_LANGUAGES,
                unit: null,
                module: 'Admin'
            );
        }

        $validLanguages = [];
        if (!empty($settings)) {
            $validLanguages = \json_decode($settings->content, true);
        } else {
            $validLanguages = [
                ISO639x1Enum::_EN,
            ];
        }

        $billLanguage = $validLanguages[0];

        $clientBillLanguage = $client->getAttribute('bill_language')?->value->getValue();
        if (!empty($clientBillLanguage) && \in_array($clientBillLanguage, $validLanguages)) {
            $billLanguage = $clientBillLanguage;
        } else {
            $clientLanguages = ISO639x1Enum::languageFromCountry($client->mainAddress->getCountry());
            $clientLanguage  = !empty($clientLanguages) ? $clientLanguages[0] : '';

            if (\in_array($clientLanguage, $validLanguages)) {
                $billLanguage = $clientLanguage;
            }
        }

        $bill->setLanguage($billLanguage);

        return $bill;
    }

    public function createBaseBillElement(Client $client, Item $item, Bill $bill, RequestAbstract $request) : BillElement
    {
        $taxCode = $this->app->moduleManager->get('Billing', 'ApiTax')->getTaxCodeFromClientItem($client, $item, $request->getCountry());

        $element       = BillElement::fromItem($item, $taxCode, $request->getDataInt('quantity') ?? 1);
        $element->bill = $request->getDataInt('bill') ?? 0;

        return $element;
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
        if ($request->hasData('client')) {
            /** @var \Modules\ClientManagement\Models\Client $account */
            $account = ClientMapper::get()
                ->with('account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('client'))
                ->execute();
        } elseif (($request->getDataInt('supplier') ?? -1) === 0) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = new NullSupplier();
        } elseif ($request->hasData('supplier')) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = SupplierMapper::get()
                ->with('account')
                ->with('mainAddress')
                ->where('id', (int) $request->getData('supplier'))
                ->execute();
        }

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()
            ->where('id', $request->getDataInt('type') ?? 1)
            ->execute();

        /* @var \Modules\Account\Models\Account $account */
        $bill               = new Bill();
        $bill->createdBy    = new NullAccount($request->header->account);
        $bill->type         = $billType;
        // @todo: use defaultInvoiceAddress or mainAddress. also consider to use billto1, billto2, billto3 (for multiple lines e.g. name2, fao etc.)
        $bill->billTo          = (string) ($request->getDataString('billto')
            ?? ($account->account->name1 . (!empty($account->account->name2)
                ? ', ' . $account->account->name2
                : ''
            )));
        $bill->billAddress     = (string) ($request->getDataString('billaddress') ?? $account->mainAddress->address);
        $bill->billZip         = (string) ($request->getDataString('billtopostal') ?? $account->mainAddress->postal);
        $bill->billCity        = (string) ($request->getDataString('billtocity') ?? $account->mainAddress->city);
        $bill->billCountry     = (string) (
            $request->getDataString('billtocountry') ?? (
                ($country = $account->mainAddress->getCountry()) ===  ISO3166TwoEnum::_XXX ? '' : $country)
            );
        $bill->client          = !$request->hasData('client') ? null : $account;
        $bill->supplier        = !$request->hasData('supplier') ? null : $account;
        $bill->performanceDate = new \DateTime($request->getDataString('performancedate') ?? 'now');
        $bill->setStatus($request->getDataInt('status') ?? BillStatus::ACTIVE);

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
        if (($val['client/supplier'] = (!$request->hasData('client')
                && (!$request->hasData('supplier')
                    && ($request->getDataInt('supplier') ?? -1) !== 0)
                ))
            || ($val['type'] = (!$request->hasData('type')))
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
                        $request->getDataInt('type'),
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
        if (($val['media'] = (!$request->hasData('media') && empty($request->getFiles())))
            || ($val['bill'] = !$request->hasData('bill'))
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

        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()
            ->with('client')
            ->with('client/attributes')
            ->with('client/attributes/type')
            ->with('client/attributes/value')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        $element = $this->createBillElementFromRequest($request, $response, $old, $data);
        $this->createModel($request->header->account, $element, BillElementMapper::class, 'bill_element', $request->getOrigin());

        $new = clone $old;
        $new->addElement($element);
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
     */
    private function createBillElementFromRequest(RequestAbstract $request, ResponseAbstract $response, Bill $bill, $data = null) : BillElement
    {
        /** @var \Modules\ItemManagement\Models\Item $item */
        $item = ItemMapper::get()
            ->with('attributes')
            ->with('attributes/type')
            ->with('attributes/value')
            ->with('l11n')
            ->with('l11n/type')
            ->where('id', $request->getDataInt('item') ?? 0)
            ->where('l11n/type/title', ['name1', 'name2', 'name3'], 'IN')
            ->where('l11n/language', $bill->getLanguage())
            ->execute();

        $element       = $this->createBaseBillElement($bill->client, $item, $bill, $request);
        $element->bill = $bill->getId();

        // discounts
        if ($request->getData('discount_percentage') !== null) {
            // @todo: implement a addDiscount function
        }

        return $element;
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
        if (($val['bill'] = !$request->hasData('bill'))) {
            return $val;
        }

        return [];
    }

    public function apiPreviewRender(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('type')
            ->with('type/l11n')
            ->with('elements')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        $templateId = $request->getData('bill_template', 'int');
        if ($templateId === null) {
            $billTypeId = $request->getData('bill_type', 'int');

            /** @var \Modules\Billing\Models\BillType $billType */
            $billType = BillTypeMapper::get()
                ->where('id', $billTypeId)
                ->execute();

            $templateId = $billType->defaultTemplate->getId();
        }

        /** @var \Modules\Media\Models\Collection $template */
        $template = CollectionMapper::get()
            ->with('sources')
            ->where('id', $templateId)
            ->execute();

        require_once __DIR__ . '/../../../Resources/tcpdf/tcpdf.php';

        $response->header->set('Content-Type', MimeType::M_PDF, true);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

        /** @var \Model\Setting[] $settings */
        $settings = $this->app->appSettings->get(null,
            [
                AdminSettingsEnum::DEFAULT_TEMPLATES,
                AdminSettingsEnum::DEFAULT_ASSETS,
            ],
            unit: $this->app->unitId,
            module: 'Admin'
        );

        if (empty($settings)) {
            /** @var \Model\Setting[] $settings */
            $settings = $this->app->appSettings->get(null,
                [
                    AdminSettingsEnum::DEFAULT_TEMPLATES,
                    AdminSettingsEnum::DEFAULT_ASSETS,
                ],
                unit: null,
                module: 'Admin'
            );
        }

        /** @var \Modules\Media\Models\Collection $defaultTemplates */
        $defaultTemplates = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content)
            ->execute();

        /** @var \Modules\Media\Models\Collection $defaultAssets */
        $defaultAssets = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content)
            ->execute();

        $view->setData('defaultTemplates', $defaultTemplates);
        $view->setData('defaultAssets', $defaultAssets);

        $path   = $this->createBillDir($bill);
        $pdfDir = __DIR__ . '/../../../Modules/Media/Files' . $path;

        $view->setData('bill', $bill);
        $view->setData('path', $pdfDir . '/' .$bill->billDate->format('Y-m-d') . '_' . $bill->number . '.pdf');

        $view->setData('bill_creator', $request->getDataString('bill_creator'));
        $view->setData('bill_title', $request->getDataString('bill_title'));
        $view->setData('bill_subtitle', $request->getDataString('bill_subtitle'));
        $view->setData('keywords', $request->getDataString('keywords'));
        $view->setData('bill_logo_name', $request->getDataString('bill_logo_name'));
        $view->setData('bill_slogan', $request->getDataString('bill_slogan'));

        $view->setData('legal_company_name', $request->getDataString('legal_company_name'));
        $view->setData('bill_company_address', $request->getDataString('bill_company_address'));
        $view->setData('bill_company_city', $request->getDataString('bill_company_city'));
        $view->setData('bill_company_ceo', $request->getDataString('bill_company_ceo'));
        $view->setData('bill_company_website', $request->getDataString('bill_company_website'));
        $view->setData('bill_company_email', $request->getDataString('bill_company_email'));
        $view->setData('bill_company_phone', $request->getDataString('bill_company_phone'));

        $view->setData('bill_company_tax_office', $request->getDataString('bill_company_tax_office'));
        $view->setData('bill_company_tax_id', $request->getDataString('bill_company_tax_id'));
        $view->setData('bill_company_vat_id', $request->getDataString('bill_company_vat_id'));

        $view->setData('bill_company_bank_name', $request->getDataString('bill_company_bank_name'));
        $view->setData('bill_company_bic', $request->getDataString('bill_company_bic'));
        $view->setData('bill_company_iban', $request->getDataString('bill_company_iban'));

        $view->setData('bill_type_name', $request->getDataString('bill_type_name'));

        $view->setData('bill_start_text', $request->getDataString('bill_start_text'));
        $view->setData('bill_lines', $request->getDataString('bill_lines'));
        $view->setData('bill_end_text', $request->getDataString('bill_end_text'));

        $view->setData('bill_payment_terms', $request->getDataString('bill_payment_terms'));
        $view->setData('bill_terms', $request->getDataString('bill_terms'));
        $view->setData('bill_taxes', $request->getDataString('bill_taxes'));
        $view->setData('bill_currency', $request->getDataString('bill_currency'));

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
            ->with('type')
            ->with('type/l11n')
            ->with('elements')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        $templateId = $request->getDataInt('bill_template');
        if ($templateId === null) {
            $billTypeId = $bill->type->getId();

            /** @var \Modules\Billing\Models\BillType $billType */
            $billType = BillTypeMapper::get()
                ->where('id', $billTypeId)
                ->execute();

            $templateId = $billType->defaultTemplate->getId();
        }

        /** @var \Modules\Media\Models\Collection $template */
        $template = CollectionMapper::get()
            ->with('sources')
            ->where('id', $templateId)
            ->execute();

        require_once __DIR__ . '/../../../Resources/tcpdf/tcpdf.php';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

        /** @var \Model\Setting[] $settings */
        $settings = $this->app->appSettings->get(null,
            [
                AdminSettingsEnum::DEFAULT_TEMPLATES,
                AdminSettingsEnum::DEFAULT_ASSETS,
            ],
            unit: $this->app->unitId,
            module: 'Admin'
        );

        if (empty($settings)) {
            /** @var \Model\Setting[] $settings */
            $settings = $this->app->appSettings->get(null,
                [
                    AdminSettingsEnum::DEFAULT_TEMPLATES,
                    AdminSettingsEnum::DEFAULT_ASSETS,
                ],
                unit: null,
                module: 'Admin'
            );
        }

        /** @var \Modules\Media\Models\Collection $defaultTemplates */
        $defaultTemplates = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content)
            ->execute();

        /** @var \Modules\Media\Models\Collection $defaultAssets */
        $defaultAssets = CollectionMapper::get()
            ->with('sources')
            ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content)
            ->execute();

        $view->setData('defaultTemplates', $defaultTemplates);
        $view->setData('defaultAssets', $defaultAssets);
        $view->setData('bill', $bill);

        // @todo: add bill data such as company name bank information, ..., etc.

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

        $billFileName = $bill->billDate->format('Y-m-d') . '_' . $bill->number . '.pdf';

        \file_put_contents($pdfDir . '/' . $billFileName, $pdf);
        if (!\is_file($pdfDir . '/' . $billFileName)) {
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $media = $this->app->moduleManager->get('Media', 'Api')->createDbEntry(
            status: [
                'status'    => UploadStatus::OK,
                'name'      => $billFileName,
                'path'      => $pdfDir,
                'filename'  => $billFileName,
                'size'      => \filesize($pdfDir . '/' . $billFileName),
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

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            'PDF',
            'Bill Pdf successfully created.',
            $media
        );
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
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
