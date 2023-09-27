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

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\SettingsEnum as AdminSettingsEnum;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\NullBill;
use Modules\Billing\Models\NullBillElement;
use Modules\Billing\Models\SettingsEnum;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\Media;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\UploadStatus;
use Modules\Messages\Models\EmailMapper;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Autoloader;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\Mail\Email;
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
     * Constructor.
     *
     * @param null|ApplicationAbstract $app Application instance
     *
     * @since 1.0.0
     */
    public function __construct(ApplicationAbstract $app = null)
    {
        parent::__construct($app);

        if ($this->app->moduleManager->isActive('WarehouseManagement')) {
            $this->app->eventManager->importFromFile(__DIR__ . '/../../WarehouseManagement/Admin/Hooks/Manual.php');
        }
    }

    /**
     * Api method to update a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();
        $new = $this->updateBillFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
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
     * @param RequestAbstract $request Request
     * @param Bill            $old     Bill
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function updateBillFromRequest(RequestAbstract $request, Bill $old) : Bill
    {
        return $old;
    }

    /**
     * Api method to create a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $bill = $this->createBillFromRequest($request, $response, $data);
        $this->createBillDatabaseEntry($bill, $request);
        $this->createStandardCreateResponse($request, $response, $bill);
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

        // We ned to get the bill again since the bill has a trigger which is executed on insert
        // @todo: consider to remove the trigger and select the latest bill here and add + 1 to the new sequence since we have to tdo an update anyways
        /** @var Bill $bill */
        $tmp = BillMapper::get()
            ->where('id', $bill->id)
            ->execute();

        $bill->sequence = $tmp->sequence;

        $old = clone $bill;
        $bill->buildNumber(); // The bill id is part of the number
        $this->updateModel($request->header->account, $old, $bill, BillMapper::class, 'bill', $request->getOrigin());
    }

    /**
     * Create a base Bill object with default values
     *
     * @param Client|Supplier $account The client or supplier object for whom the bill is being created
     * @param RequestAbstract $request The request object that contains the header account
     *
     * @return Bill The new Bill object with default values
     *
     * @todo Validate VAT before creation (maybe need to add a status when last validated, we don't want to validate every time)
     * @todo Set the correct date of payment
     * @todo Use bill and shipping address instead of main address if available
     * @todo Implement allowed invoice languages and a default invoice language if none match
     * @todo Implement client invoice language (allowing for different invoice languages than invoice address)
     *
     * @since 1.0.0
     */
    public function createBaseBill(Client | Supplier $account, RequestAbstract $request) : Bill
    {
        // @todo: validate vat before creation for clients
        $bill                  = new Bill();
        $bill->createdBy       = new NullAccount($request->header->account);
        $bill->unit            = $account->unit ?? $this->app->unitId;
        $bill->billDate        = new \DateTime('now'); // @todo: Date of payment
        $bill->performanceDate = $request->getDataDateTime('performancedate') ?? new \DateTime('now'); // @todo: Date of payment
        $bill->accountNumber   = $account->number;
        $bill->setStatus($request->getDataInt('status') ?? BillStatus::DRAFT);

        $bill->shipping     = 0;
        $bill->shippingText = '';

        $bill->payment     = 0;
        $bill->paymentText = '';

        if ($account instanceof Client) {
            $bill->client = $account;
        } else {
            $bill->supplier = $account;
        }

        // @todo: use bill and shipping address instead of main address if available
        $bill->billTo      = $request->getDataString('billto') ?? $account->account->name1;
        $bill->billAddress = $request->getDataString('billaddress') ?? $account->mainAddress->address;
        $bill->billCity    = $request->getDataString('billtocity') ?? $account->mainAddress->city;
        $bill->billZip     = $request->getDataString('billtopostal') ?? $account->mainAddress->postal;
        $bill->billCountry = $request->getDataString('billtocountry') ?? $account->mainAddress->getCountry();

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
        if (!empty($settings) && !empty($settings->content)) {
            $validLanguages = \json_decode($settings->content, true);
        }

        if (empty($validLanguages) || !\is_array($validLanguages)) {
            $validLanguages = [
                ISO639x1Enum::_EN,
            ];
        }

        $billLanguage = $validLanguages[0] ?? ISO639x1Enum::_EN;

        $accountBillLanguage = $account->getAttribute('bill_language')->value->valueStr;
        if (!empty($accountBillLanguage) && \in_array($accountBillLanguage, $validLanguages)) {
            $billLanguage = $accountBillLanguage;
        } else {
            $accountLanguages = ISO639x1Enum::languageFromCountry($account->mainAddress->getCountry());
            $accountLanguage  = empty($accountLanguages) ? '' : $accountLanguages[0];

            if (\in_array($accountLanguage, $validLanguages)) {
                $billLanguage = $accountLanguage;
            }
        }

        $bill->language = $billLanguage;

        $typeMapper = BillTypeMapper::get()
            ->with('l11n')
            ->where('l11n/langauge', $billLanguage)
            ->limit(1);

        if ($request->hasData('type')) {
            $typeMapper->where('id', $request->getDataInt('type'));
        } else {
            $typeMapper->where('name', 'sales_invoice');
        }

        $bill->type = $typeMapper->execute();

        return $bill;
    }

    /**
     * Create a base BillElement object with default values
     *
     * @param Client          $client  The client object for whom the bill is being created
     * @param Item            $item    The item object for which the bill element is being created
     * @param Bill            $bill    The bill object for which the bill element is being created
     * @param RequestAbstract $request The request object that contains the header account
     *
     * @return BillElement
     *
     * @since 1.0.0
     */
    public function createBaseBillElement(Client $client, Item $item, Bill $bill, RequestAbstract $request) : BillElement
    {
        $taxCode = $this->app->moduleManager->get('Billing', 'ApiTax')
            ->getTaxCodeFromClientItem($client, $item, $request->header->l11n->country);

        return BillElement::fromItem(
            $item,
            $taxCode,
            $request->getDataInt('quantity') ?? 1,
            $bill->id
        );
    }

    /**
     * Method to create a bill from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
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

        return $this->createBaseBill($account, $request);
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
     * Api method to add Media to a Bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiMediaAddToBill(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateMediaAddToBill($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();
        $path = $this->createBillDir($bill);

        $uploaded = [];
        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH,
                hasAccountRelation: false,
                readContent: $request->getDataBool('parse_content') ?? false
            );

            $collection = null;
            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $bill->id,
                    $media->id,
                    BillMapper::class,
                    'files',
                    '',
                    $request->getOrigin()
                );

                if ($request->hasData('type')) {
                    $this->createModelRelation(
                        $request->header->account,
                        $media->id,
                        $request->getDataInt('type'),
                        MediaMapper::class,
                        'types',
                        '',
                        $request->getOrigin()
                    );
                }

                if ($collection === null) {
                    /** @var \Modules\Media\Models\Collection $collection */
                    $collection = MediaMapper::getParentCollection($path)
                        ->limit(1)
                        ->execute();

                    if ($collection->id === 0) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $path,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files' . $path,
                        );
                    }
                }

                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $media->id,
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
                    $bill->id,
                    (int) $media,
                    BillMapper::class,
                    'files',
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
     * Api method to remove Media from Bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiMediaRemoveFromBill(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // @todo: check that it is not system generated media!
        if (!empty($val = $this->validateMediaRemoveFromBill($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Media\Models\Media $media */
        $media = MediaMapper::get()->where('id', (int) $request->getData('media'))->execute();

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();

        $path = $this->createBillDir($bill);

        /** @var \Modules\Media\Models\Collection[] */
        $billCollection = CollectionMapper::getAll()
            ->where('virtual', $path)
            ->execute();

        if (\count($billCollection) !== 1) {
            // For some reason there are multiple collections with the same virtual path?
            // @todo: check if this is the correct way to handle it or if we need to make sure that it is a collection
            return;
        }

        $collection = \reset($billCollection);

        $this->deleteModelRelation(
            $request->header->account,
            $bill->id,
            $media->id,
            BillMapper::class,
            'files',
            '',
            $request->getOrigin()
        );

        $this->deleteModelRelation(
            $request->header->account,
            $collection->id,
            $media->id,
            CollectionMapper::class,
            'sources',
            '',
            $request->getOrigin()
        );

        $referenceCount = MediaMapper::countInternalReferences($media->id);

        if ($referenceCount === 0) {
            // Is not used anywhere else -> remove from db and file system

            // @todo: remove media types from media

            $this->deleteModel($request->header->account, $media, MediaMapper::class, 'bill_media', $request->getOrigin());

            if (\is_dir($media->getAbsolutePath())) {
                \phpOMS\System\File\Local\Directory::delete($media->getAbsolutePath());
            } else {
                \phpOMS\System\File\Local\File::delete($media->getAbsolutePath());
            }
        }

        $this->createStandardDeleteResponse($request, $response, $media);
    }

    /**
     * Validate Media remove from Bill request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateMediaRemoveFromBill(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['media'] = !$request->hasData('media'))
            || ($val['bill'] = !$request->hasData('bill'))
        ) {
            return $val;
        }

        return [];
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
            . $bill->createdAt->format('Y/m/d') . '/'
            . $bill->id;
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
        if (($val['media'] = (!$request->hasData('media') && empty($request->files)))
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
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillElementCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillElementCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

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

        // @todo: handle stock transaction here
        // @todo: if transaction fails don't update below and send warning to user
        // @todo: however mark transaction as reserved and only update when bill is finalized!!!

        // @todo: in BillElementUpdate do the same

        $new = clone $old;
        $new->addElement($element);

        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill_element', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $element);
    }

    /**
     * Method to create a bill element from request.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param Bill             $bill     Bill to create element for
     * @param array            $data     Generic data
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
            ->where('l11n/language', $bill->language)
            ->execute();

        if ($bill->client === null) {
            return new NullBillElement();
        }

        $element       = $this->createBaseBillElement($bill->client, $item, $bill, $request);
        $element->bill = new NullBill($bill->id);

        // discounts
        // @todo: implement a addDiscount function
        /*
        if ($request->getData('discount_percentage') !== null) {
        }
        */

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

    /**
     * Render bill media
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiMediaRender(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // @todo: check if has permission
        $this->app->moduleManager->get('Media', 'Api')->apiMediaExport($request, $response, ['ignorePermission' => true]);
    }

    /**
     * Api method  to create a bill preview
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiPreviewRender(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
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

            if (empty($billTypeId)) {
                $billTypeId = $bill->type->id;
            }

            if (empty($billTypeId)) {
                return;
            }

            /** @var \Modules\Billing\Models\BillType $billType */
            $billType = BillTypeMapper::get()
                ->with('defaultTemplate')
                ->where('id', $billTypeId)
                ->execute();

            $templateId = $billType->defaultTemplate?->id;
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

        $view->data['defaultTemplates'] = $defaultTemplates;
        $view->data['defaultAssets']    = $defaultAssets;

        $path   = $this->createBillDir($bill);
        $pdfDir = __DIR__ . '/../../../Modules/Media/Files' . $path;

        $view->data['bill'] = $bill;
        $view->data['path'] = $pdfDir . '/' . ($bill->billDate?->format('Y-m-d') ?? '0') . '_' . $bill->number . '.pdf';

        $view->data['bill_creator']  = $request->getDataString('bill_creator');
        $view->data['bill_title']    = $request->getDataString('bill_title');
        $view->data['bill_subtitle'] = $request->getDataString('bill_subtitle');
        $view->data['keywords']      = $request->getDataString('keywords');

        $view->data['bill_type_name'] = $request->getDataString('bill_type_name');

        $view->data['bill_start_text'] = $request->getDataString('bill_start_text');
        $view->data['bill_lines']      = $request->getDataString('bill_lines');
        $view->data['bill_end_text']   = $request->getDataString('bill_end_text');

        $view->data['bill_payment_terms'] = $request->getDataString('bill_payment_terms');
        $view->data['bill_terms']         = $request->getDataString('bill_terms');
        $view->data['bill_taxes']         = $request->getDataString('bill_taxes');
        $view->data['bill_currency']      = $request->getDataString('bill_currency');

        // Unit specifc settings
        $view->data['bill_logo_name']       = $request->getDataString('bill_logo_name');
        $view->data['bill_slogan']          = $request->getDataString('bill_slogan');
        $view->data['legal_company_name']   = $request->getDataString('legal_company_name');
        $view->data['bill_company_address'] = $request->getDataString('bill_company_address');
        $view->data['bill_company_city']    = $request->getDataString('bill_company_city');
        $view->data['bill_company_ceo']     = $request->getDataString('bill_company_ceo');
        $view->data['bill_company_website'] = $request->getDataString('bill_company_website');
        $view->data['bill_company_email']   = $request->getDataString('bill_company_email');
        $view->data['bill_company_phone']   = $request->getDataString('bill_company_phone');
        $view->data['bill_company_terms']   = $request->getDataString('bill_company_terms');

        $view->data['bill_company_tax_office'] = $request->getDataString('bill_company_tax_office');
        $view->data['bill_company_tax_id']     = $request->getDataString('bill_company_tax_id');
        $view->data['bill_company_vat_id']     = $request->getDataString('bill_company_vat_id');

        $view->data['bill_company_bank_name']    = $request->getDataString('bill_company_bank_name');
        $view->data['bill_company_swift']        = $request->getDataString('bill_company_swift');
        $view->data['bill_company_bank_account'] = $request->getDataString('bill_company_bank_account');

        $pdf = $view->render();

        $response->set('', $pdf);
    }

    /**
     * Api method to create and archive a bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillPdfArchiveCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        // @todo: This is stupid to do twice but I need to get the langauge.
        // For the future it should just be a join on the bill langauge!!!
        // The problem is the where here is a model where and not a query
        // builder where meaning it is always considered a value and not a column.

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('type')
            ->with('type/l11n')
            ->with('type/defaultTemplate')
            ->with('elements')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->where('type/l11n/language', $bill->language)
            ->execute();

        $templateId = $request->getDataInt('bill_template');
        if ($templateId === null) {
            $templateId = $bill->type->defaultTemplate?->id;
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

        $view->data['defaultTemplates'] = $defaultTemplates;
        $view->data['defaultAssets']    = $defaultAssets;
        $view->data['bill']             = $bill;

        // @todo: add bill data such as company name bank information, ..., etc.

        $pdf = $view->render();

        $path   = $this->createBillDir($bill);
        $pdfDir = __DIR__ . '/../../../Modules/Media/Files' . $path;

        $status = \is_dir($pdfDir) ? true : \mkdir($pdfDir, 0755, true);
        if (!$status) {
            // @codeCoverageIgnoreStart
            $response->set($request->uri->__toString(), new FormValidation(['status' => $status]));
            $response->header->status = RequestStatusCode::R_400;

            return;
            // @codeCoverageIgnoreEnd
        }

        $billFileName = ($bill->billDate?->format('Y-m-d') ?? '0') . '_' . $bill->number . '.pdf';

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

        // Send bill via email
        // @todo: maybe not all bill types, and bill status (e.g. deleted should not be sent)
        $client = ClientMapper::get()
            ->with('account')
            ->with('attributes')
            ->with('attributes/type')
            ->with('attributes/value')
            ->where('id', $bill->client?->id ?? 0)
            ->where('attributes/type/name', ['bill_emails', 'bill_email_address'], 'IN')
            ->execute();

        if ($client->getAttribute('bill_emails')->value->getValue() === 1) {
            $email = empty($tmp = $client->getAttribute('bill_email_address')->value->getValue())
                ? (string) $tmp
                : $client->account->getEmail();

            $this->sendBillEmail($media, $email, $response->header->l11n->language);
        }

        // Add type to media
        /** @var \Model\Setting $originalType */
        $originalType = $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        );

        $this->createModelRelation(
            $request->header->account,
            $media->id,
            (int) $originalType->content,
            MediaMapper::class,
            'types',
            '',
            $request->getOrigin()
        );

        // Add media to bill
        $this->createModelRelation(
            $request->header->account,
            $bill->id,
            $media->id,
            BillMapper::class,
            'files',
            '',
            $request->getOrigin()
        );

        $this->createStandardCreateResponse($request, $response, $media);
    }

    /**
     * Send bill as email
     *
     * @param Media  $media    Media to send
     * @param string $email    Email address
     * @param string $language Message language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function sendBillEmail(Media $media, string $email, string $language = 'en') : void
    {
        $handler = $this->app->moduleManager->get('Admin', 'Api')->setUpServerMailHandler();

        /** @var \Model\Setting $emailFrom */
        $emailFrom = $this->app->appSettings->get(
            names: AdminSettingsEnum::MAIL_SERVER_ADDR,
            module: 'Admin'
        );

        /** @var \Model\Setting $billingTemplate */
        $billingTemplate = $this->app->appSettings->get(
            names: SettingsEnum::BILLING_CUSTOMER_EMAIL_TEMPLATE,
            module: 'Billing'
        );

        $mail = EmailMapper::get()
            ->with('l11n')
            ->where('id', (int) $billingTemplate->content)
            ->where('l11n/language', $language)
            ->execute();

        $mail = new Email();
        $mail->setFrom($emailFrom->content);
        $mail->addTo($email);
        $mail->addAttachment($media->getAbsolutePath(), $media->name);

        $handler->send($mail);

        $this->app->moduleManager->get('Billing', 'Api')->sendMail($mail);
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
     * @since 1.0.0
     */
    public function apiNoteCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateNoteCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('id'))->execute();

        $request->setData('virtualpath', $this->createBillDir($bill), true);
        $this->app->moduleManager->get('Editor')->apiEditorCreate($request, $response, $data);

        if ($response->header->status !== RequestStatusCode::R_200) {
            return;
        }

        /** @var \Modules\Editor\Models\EditorDoc $model */
        $model = $response->get($request->uri->__toString())['response'];
        $this->createModelRelation($request->header->account, $request->getDataInt('id'), $model->id, BillMapper::class, 'bill_note', '', $request->getOrigin());
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

    /**
     * Api method to delete Bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()->where('id', (int) $request->getData('id'))->execute();

        // @todo: check if bill can be deleted
        // @todo: adjust stock transfer

        $new = $this->deleteBillFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $old);
    }

    /**
     * Method to create a bill from request.
     *
     * @param RequestAbstract $request Request
     * @param Bill            $new     Bill
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function deleteBillFromRequest(RequestAbstract $request, Bill $new) : Bill
    {
        $new->status = BillStatus::DELETED;

        return $new;
    }

    /**
     * Validate Bill delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillElement
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillElementUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillElementUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var BillElement $old */
        $old = BillElementMapper::get()->where('id', (int) $request->getData('id'))->execute();

        // @todo: can be edited?
        // @todo: adjust transfer protocolls

        $new = $this->updateBillElementFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillElementMapper::class, 'bill_element', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillElement from request.
     *
     * @param RequestAbstract $request Request
     * @param BillElement     $new     Model to modify
     *
     * @return BillElement
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function updateBillElementFromRequest(RequestAbstract $request, BillElement $new) : BillElement
    {
        return $new;
    }

    /**
     * Validate BillElement update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillElementUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillElement
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillElementDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateBillElementDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        // @todo: check if can be deleted
        // @todo: handle transactions and bill update

        /** @var \Modules\Billing\Models\BillElement $billElement */
        $billElement = BillElementMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billElement, BillElementMapper::class, 'bill_element', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billElement);
    }

    /**
     * Validate BillElement delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillElementDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update Note
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNoteUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // @todo: check permissions
        $this->app->moduleManager->get('Editor', 'Api')->apiEditorDocUpdate($request, $response, $data);
    }

    /**
     * Api method to delete Note
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiNoteDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // @todo: check permissions
        $this->app->moduleManager->get('Editor', 'Api')->apiEditorDocDelete($request, $response, $data);
    }
}
