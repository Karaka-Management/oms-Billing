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

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\SettingsEnum as AdminSettingsEnum;
use Modules\Attribute\Models\NullAttribute;
use Modules\Attribute\Models\NullAttributeType;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\NullBill;
use Modules\Billing\Models\NullBillElement;
use Modules\Billing\Models\PermissionCategory;
use Modules\Billing\Models\SettingsEnum;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ItemManagement\Models\Attribute\ItemAttributeMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\NullContainer;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\Media;
use Modules\Media\Models\MediaClass;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\UploadStatus;
use Modules\Messages\Models\EmailMapper;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Autoloader;
use phpOMS\DataStorage\Database\Query\ColumnName;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Stdlib\Base\FloatInt;
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
    public function __construct(?ApplicationAbstract $app = null)
    {
        parent::__construct($app);

        if ($this->app->moduleManager->isActive('WarehouseManagement')) {
            $this->app->eventManager->importFromFile(__DIR__ . '/../../WarehouseManagement/Admin/Hooks/Manual.php');
        }

        if ($this->app->moduleManager->isActive('Accounting')) {
            $this->app->eventManager->importFromFile(__DIR__ . '/../../Accounting/Admin/Hooks/Manual.php');
        }
    }

    /**
     * Create email for/from bill
     *
     * @param RequestAbstract $request Request
     * @param array           $data    Data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiBillEmail(RequestAbstract $request, array $data = []) : void
    {
        $bill = $data['bill'] ?? BillMapper::get()
            ->with('type')
            ->with('files')
            ->with('files/types')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        $media = $data['media'] ?? $bill->getFileByTypeName('internal');

        if ($bill->status === BillStatus::ARCHIVED
            && $bill->type->email
        ) {
            $email           = $request->getDataString('email');
            $billingTemplate = null;

            if (!empty($email)) {
                /** @var \Model\Setting $billingTemplate */
                $billingTemplate = $this->app->appSettings->get(
                    names: SettingsEnum::BILLING_CUSTOMER_EMAIL_TEMPLATE,
                    module: 'Billing'
                );
            } elseif (($bill->client?->id ?? 0) !== 0) {
                $client = ClientMapper::get()
                    ->with('account')
                    ->with('attributes')
                    ->with('attributes/type')
                    ->with('attributes/value')
                    ->where('id', $bill->client?->id ?? 0)
                    ->where('attributes/type/name', ['bill_emails', 'bill_email_address'], 'IN')
                    ->execute();

                /** @var \Model\Setting $billingTemplate */
                $billingTemplate = $this->app->appSettings->get(
                    names: SettingsEnum::BILLING_CUSTOMER_EMAIL_TEMPLATE,
                    module: 'Billing'
                );

                if ($client->getAttribute('bill_emails')->value->getValue() === 1) {
                    // @todo should this really be a string or an ID for a contact element?
                    $email ??= empty($tmp = $client->getAttribute('bill_email_address')->value->valueStr)
                        ? $client->account->email
                        : (string) $tmp;
                }
            } elseif (($bill->supplier?->id ?? 0) !== 0) {
                $supplier = SupplierMapper::get()
                    ->with('account')
                    ->with('attributes')
                    ->with('attributes/type')
                    ->with('attributes/value')
                    ->where('id', $bill->supplier?->id ?? 0)
                    ->where('attributes/type/name', ['bill_emails', 'bill_email_address'], 'IN')
                    ->execute();

                /** @var \Model\Setting $billingTemplate */
                $billingTemplate = $this->app->appSettings->get(
                    names: SettingsEnum::BILLING_SUPPLIER_EMAIL_TEMPLATE,
                    module: 'Billing'
                );

                if ($supplier->getAttribute('bill_emails')->value->getValue() === 1) {
                    // @todo should this really be a string or an ID for a contact element?
                    $email ??= empty($tmp = $supplier->getAttribute('bill_email_address')->value->valueStr)
                        ? $supplier->account->email
                        : (string) $tmp;
                }
            }

            if (!empty($email) && $billingTemplate !== null) {
                $this->sendBillEmail($media, $email, (int) $billingTemplate->content, $bill->language);
            }
        }
    }

    /**
     * Api method to finalize a bill
     *
     * Finalization creates an archive and possibly sends the bill via email.
     * Additionally, it triggers the event Billing-bill-finalize event which also finalizes the stock changes and possibly accounting postings
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
    public function apiBillFinalize(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::SALES_INVOICE
            )
            && !$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::PURCHASE_INVOICE
            )
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        // Archive bill
        /** @var \Modules\Billing\Models\Bill $old */
        $old = BillMapper::get()
            ->with('type')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        $new         = clone $old;
        $new->status = BillStatus::ARCHIVED;

        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill', $request->getOrigin());

        // Create final pdf
        $this->apiBillPdfArchiveCreate($request, $response, $data);
        $media = $response->getDataArray($request->uri->__toString())['response'];

        $this->app->eventManager->triggerSimilar('PRE:Module:' . self::NAME . '-bill-finalize', '', [
            $request->header->account,
            null, $new,
            null, self::NAME . '-bill-finalize',
            self::NAME,
            (string) $new->id,
            null,
            $request->getOrigin(),
        ]);

        // Send bill via email
        $this->apiBillEmail($request, ['bill' => $new, 'media' => $media]);

        $this->createStandardUpdateResponse($request, $response, $new);
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

        // @feature Allow to update internal statistical fields
        //      Example: Referral account
        if ($old->status === BillStatus::ARCHIVED) {
            $response->header->status = RequestStatusCode::R_423;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

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

        // We need to get the bill again since the bill has a trigger which is executed on insert
        // Doing this manually would cause concurrency issues because there are times (up to 200ms)
        // when the previous element doesn't have a sequence defined.

        /** @var Bill $bill */
        $tmp = BillMapper::get()
            ->where('id', $bill->id)
            ->execute();

        $bill->sequence = $tmp->sequence;

        $old = clone $bill;
        $bill->buildNumber(); // The bill sequence number is part of the number
        $this->updateModel($request->header->account, $old, $bill, BillMapper::class, 'bill', $request->getOrigin());
    }

    /**
     * Create a base Bill object with default values
     *
     * Client attributes required:
     *      'segment', 'section', 'client_group', 'client_type',
     *      'sales_tax_code'
     *
     * Supplier attributes required:
     *      'purchase_tax_code'
     *
     * @param Client|Supplier $account The client or supplier object for whom the bill is being created
     * @param RequestAbstract $request The request object that contains the header account
     *
     * @return Bill The new Bill object with default values
     *
     * @todo Validate VAT before creation
     *      Maybe needs to add a status when last validated, we don't want to validate every time
     *      https://github.com/Karaka-Management/oms-Billing/issues/44
     *
     * @todo Add custom tax id for bill to manually overwrite the client_sales_tax_code
     *      https://github.com/Karaka-Management/oms-Billing/issues/65
     *
     * @since 1.0.0
     */
    public function createBaseBill(Client | Supplier $account, RequestAbstract $request) : Bill
    {
        $bill                  = new Bill();
        $bill->createdBy       = new NullAccount($request->header->account);
        $bill->unit            = $account->unit ?? $this->app->unitId;
        $bill->billDate        = $request->getDataDateTime('bill_date') ?? new \DateTime('now');
        $bill->performanceDate = $request->getDataDateTime('performancedate') ?? new \DateTime('now');
        $bill->accountNumber   = $account->number;
        $bill->external        = $request->getDataString('externalreferral') ?? '';
        $bill->status          = BillStatus::tryFromValue($request->getDataInt('status')) ?? BillStatus::DRAFT;

        $bill->shippingTerms = null;
        $bill->shippingText  = '';

        $bill->paymentTerms = null;
        $bill->paymentText  = '';

        // @todo Handle payment due
        //      Careful, there can be multiple due dates
        //      Example: payment plan or discounted and none-discounted date
        //      https://github.com/Karaka-Management/oms-Billing/issues/53

        if ($account instanceof Client) {
            $bill->client     = $account;
            $bill->accTaxCode = empty($temp = $bill->client->getAttribute('sales_tax_code')->value->id) ? null : $temp;
            $bill->accSegment = empty($temp = $bill->client->getAttribute('segment')->value->id) ? null : $temp;
            $bill->accSection = empty($temp = $bill->client->getAttribute('section')->value->id) ? null : $temp;
            $bill->accGroup   = empty($temp = $bill->client->getAttribute('client_group')->value->id) ? null : $temp;
            $bill->accType    = empty($temp = $bill->client->getAttribute('client_type')->value->id) ? null : $temp;
        } else {
            $bill->supplier   = $account;
            $bill->accTaxCode = empty($temp = $bill->supplier->getAttribute('purchase_tax_code')->value->id) ? null : $temp;
        }

        // @todo use bill and shipping address instead of main address if available
        //      https://github.com/Karaka-Management/oms-Billing/issues/45
        $bill->billTo      = $request->getDataString('billto') ?? $account->account->name1 . ' ' . $account->account->name2;
        $bill->billAddress = $request->getDataString('billaddress') ?? $account->mainAddress->address;
        $bill->billCity    = $request->getDataString('billtocity') ?? $account->mainAddress->city;
        $bill->billZip     = $request->getDataString('billtopostal') ?? $account->mainAddress->postal;
        $bill->billCountry = $request->getDataString('billtocountry') ?? $account->mainAddress->country;

        $bill->currency = ISO4217CharEnum::_EUR;

        $bill->language = $this->findBillLanguage($account);

        $typeMapper = BillTypeMapper::get()
            ->with('l11n')
            ->where('l11n/language', $bill->language)
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
     * Find bill language.
     *
     * @param Client|Supplier $account Account (with attributes!!!)
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function findBillLanguage(Client|Supplier $account) : string
    {
        /** @var \Model\Setting $settings */
        $settings = $this->app->appSettings->get(null,
            SettingsEnum::VALID_BILL_LANGUAGES,
            unit: $this->app->unitId,
            module: self::NAME
        );

        if (empty($settings)) {
            /** @var \Model\Setting $settings */
            $settings = $this->app->appSettings->get(null,
                SettingsEnum::VALID_BILL_LANGUAGES,
                unit: null,
                module: self::NAME
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
            $accountLanguages = ISO639x1Enum::languageFromCountry($account->mainAddress->country);
            $accountLanguage  = '';

            foreach ($accountLanguages as $accountLanguage) {
                if (\in_array($accountLanguage, $validLanguages)) {
                    $billLanguage = $accountLanguage;

                    break;
                }
            }
        }

        return $billLanguage;
    }

    /**
     * Create a base BillElement object with default values
     *
     * Item attributes required:
     *      'segment', 'section', 'sales_group', 'product_group', 'product_type',
     *      'sales_tax_code', 'purchase_tax_code', 'costcenter', 'costobject',
     *      'default_purchase_container', 'default_sales_container',
     *
     * @param Item            $item    The item object for which the bill element is being created
     * @param Bill            $bill    The bill object for which the bill element is being created
     * @param RequestAbstract $request The request object that contains the header account
     *
     * @return BillElement
     *
     * @since 1.0.0
     */
    public function createBaseBillElement(Item $item, Bill $bill, RequestAbstract $request) : BillElement
    {
        // Handle person tax code for finding tax combination below
        $attr           = new NullAttribute();
        $attrType       = new NullAttributeType();
        $attrType->name = $bill->client !== null ? 'sales_tax_code' : 'purchase_tax_code';
        $attrValue      = new NullAttributeValue($bill->accTaxCode ?? 0);
        $attr->type     = $attrType;
        $attr->value    = $attrValue;

        $container = $request->hasData('container')
            ? new NullContainer((int) $request->getData('container'))
            : null;

        $attr = new NullAttribute();

        if ($bill->type->transferType === BillTransferType::PURCHASE && $bill->supplier !== null) {
            $bill->supplier->attributes[] = $attr;

            if ($container === null) {
                $attr = $item->getAttribute('default_purchase_container');
                if ($attr->id === 0) {
                    /** @var \Modules\Attribute\Models\Attribute $attr */
                    $attr = ItemAttributeMapper::get()
                        ->with('type')
                        ->with('value')
                        ->where('ref', $item->id)
                        ->where('type/name', 'default_purchase_container')
                        ->execute();
                }
            }
        } elseif ($bill->client !== null) {
            $bill->client->attributes[] = $attr;

            if ($container === null) {
                $attr = $item->getAttribute('default_sales_container');
                if ($attr->id === 0) {
                    /** @var \Modules\Attribute\Models\Attribute $attr */
                    $attr = ItemAttributeMapper::get()
                        ->with('type')
                        ->with('value')
                        ->where('ref', $item->id)
                        ->where('type/name', 'default_sales_container')
                        ->execute();
                }
            }
        }

        $container = $container === null && $attr->id !== 0
            ? new NullContainer($attr->value->valueInt ?? 0)
            : $container;

        $taxCombination = $this->app->moduleManager->get('Billing', 'ApiTax')
            ->getTaxForPerson($item, $bill->client, $bill->supplier, $request->header->l11n->country);

        $element = BillElement::fromItem(
            $item,
            $taxCombination,
            $bill,
            FloatInt::toInt($request->getDataString('quantity') ?? '1'),
            $container
        );

        $element->itemSegment      = empty($temp = $item->getAttribute('segment')->value->id) ? null : $temp;
        $element->itemSection      = empty($temp = $item->getAttribute('section')->value->id) ? null : $temp;
        $element->itemSalesGroup   = empty($temp = $item->getAttribute('sales_group')->value->id) ? null : $temp;
        $element->itemProductGroup = empty($temp = $item->getAttribute('product_group')->value->id) ? null : $temp;
        $element->itemType         = empty($temp = $item->getAttribute('product_type')->value->id) ? null : $temp;

        $internalRequest                  = new HttpRequest($request->uri);
        $internalRequest->header->account = $request->header->account;

        $price = $this->app->moduleManager->get('Billing', 'ApiPrice')->findBestPrice($internalRequest, $item, $bill->client, $bill->supplier);

        $element->singleListPriceNet->value = $price['bestPrice']->value === 0
            ? $item->salesPrice->value
            : $price['bestPrice']->value;

        $element->singleSalesPriceNet->value = $price['bestActualPrice']->value === 0
            ? $item->salesPrice->value
            : $price['bestActualPrice']->value;

        $element->totalDiscountP  = new FloatInt($request->getDataString('discount_amount') ?? $price['discountAmount']->value);
        $element->singleDiscountR = new FloatInt($request->getDataString('discount_percentage') ?? $price['discountPercent']->value);
        $element->discountQ       = new FloatInt($request->getDataString('bonus') ?? $price['bonus']->value);

        $element->recalculatePrices();

        return $element;
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
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('client'))
                ->where('attributes/type/name', [
                    'segment', 'section', 'client_group', 'client_type',
                    'sales_tax_code',
                ], 'IN')
                ->execute();
        } elseif (($request->getDataInt('supplier') ?? -1) === 0) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = new NullSupplier();
        } elseif ($request->hasData('supplier')) {
            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = SupplierMapper::get()
                ->with('account')
                ->with('mainAddress')
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('supplier'))
                ->where('attributes/type/name', [
                    'purchase_tax_code',
                ], 'IN')
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

        $uploaded = new NullCollection();
        if (!empty($request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $request->files,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH,
                hasAccountRelation: false,
                readContent: $request->getDataBool('parse_content') ?? false,
                type: $request->getDataInt('type'),
                rel: $bill->id,
                mapper: BillMapper::class,
                field: 'files'
            );
        }

        if (!empty($media = $request->getDataJson('media'))) {
            $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                $request->header->account,
                $media,
                $bill->id,
                BillMapper::class,
                'files',
                $path
            );
        }

        // @todo media should be an array of NullMedia elements
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, '', $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SuccessfulAdd'), [
            'upload' => $uploaded->sources,
            'media'  => $media,
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
        if (!empty($val = $this->validateMediaRemoveFromBill($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Media\Models\Media $media */
        $media = MediaMapper::get()->where('id', (int) $request->getData('media'))->execute();

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()->where('id', (int) $request->getData('bill'))->execute();

        // Cannot delete system generated bill
        if (\stripos($media->name, $bill->number) !== false) {
            $response->header->status = RequestStatusCode::R_423;
            $this->createInvalidDeleteResponse($request, $response, $media);

            return;
        }

        $path = \dirname($this->createBillDir($bill));

        /** @var \Modules\Media\Models\Collection $collection */
        $collection = CollectionMapper::get()
            ->where('name', (string) $bill->id)
            ->where('virtual', $path)
            ->where('class', MediaClass::COLLECTION)
            ->limit(1)
            ->execute();

        if ($collection->id !== 0) {
            $this->deleteModelRelation(
                $request->header->account,
                $collection->id,
                $media->id,
                CollectionMapper::class,
                'sources',
                '',
                $request->getOrigin()
            );
        }

        $this->deleteModelRelation(
            $request->header->account,
            $bill->id,
            $media->id,
            BillMapper::class,
            'files',
            '',
            $request->getOrigin()
        );

        // Check if media referenced by other media except the parent collection
        $referenceCount = MediaMapper::countInternalReferences($media->id);
        if ($referenceCount === 1) {
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
     * Method to validate add Media to bill request
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
            ->with('supplier')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        $element = $this->createBillElementFromRequest($request, $response, $old, $data);
        $this->createModel($request->header->account, $element, BillElementMapper::class, 'bill_element', $request->getOrigin());

        // @todo Handle stock transaction here
        //      If the transaction fails don't perform the update below
        //      The same goes for BillElementUpdate

        $new = clone $old;
        $new->addElement($element);

        $this->updateModel($request->header->account, $old, $new, BillMapper::class, 'bill', $request->getOrigin());
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
        // @todo handle text element

        /** @var \Modules\ItemManagement\Models\Item $item */
        $item = ItemMapper::get()
            ->with('attributes')
            ->with('attributes/type')
            ->with('attributes/value')
            ->with('l11n')
            ->with('l11n/type')
            ->where('id', $request->getDataInt('item') ?? 0)
            ->where('attributes/type/name', [
                'segment', 'section', 'sales_group', 'product_group', 'product_type',
                'sales_tax_code', 'purchase_tax_code', 'costcenter', 'costobject',
                'default_purchase_container', 'default_sales_container',
            ], 'IN')
            ->where('l11n/type/title', ['name1', 'name2'], 'IN')
            ->where('l11n/language', $bill->language)
            ->execute();

        if ($bill->client === null) {
            return new NullBillElement();
        }

        $element       = $this->createBaseBillElement($item, $bill, $request);
        $element->bill = new NullBill($bill->id);

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
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::SALES_INVOICE
            )
            && !$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::PURCHASE_INVOICE
            )
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

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
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::SALES_INVOICE
            )
            && !$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::PURCHASE_INVOICE
            )
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('elements')
            ->with('elements/container')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->execute();

        // Load bill type
        $billTypeId = $request->getDataInt('bill_type') ?? $bill->type->id;
        if (empty($billTypeId)) {
            return;
        }

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()
            ->with('l11n')
            ->where('id', $billTypeId)
            ->where('l11n/language', $bill->language)
            ->execute();

        $templateId = $request->getDataInt('bill_template') ?? $billType->defaultTemplate?->id ?? 0;

        // Overriding actual bill type
        $bill->type = $billType;

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

        $template         = new NullCollection();
        $defaultTemplates = new NullCollection();
        $defaultAssets    = new NullCollection();

        /** @var \Modules\Media\Models\Collection[] $collections */
        $collections = CollectionMapper::get()
            ->with('sources')
            ->where('id', [
                (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content,
                (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content,
                $templateId,
            ], 'IN')
            ->execute();

        foreach ($collections as $collection) {
            if ($collection->id === $templateId) {
                $template = $collection;
            } elseif ($collection->id === (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content) {
                $defaultTemplates = $collection;
            } elseif ($collection->id === (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content) {
                $defaultAssets = $collection;
            }
        }

        require_once __DIR__ . '/../../../Resources/tcpdf/TCPDF.php';

        $response->header->set('Content-Type', MimeType::M_PDF, true);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

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

        // Unit specific settings
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
        if (!$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::SALES_INVOICE
            )
            && !$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                null,
                self::NAME,
                PermissionCategory::PURCHASE_INVOICE
            )
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('files')
            ->with('files/types')
            ->with('elements')
            ->with('elements/container')
            ->with('type')
            ->with('type/l11n')
            ->where('id', $request->getDataInt('bill') ?? 0)
            ->where('type/l11n/language', new ColumnName(BillMapper::getColumnByMember('language') ?? ''))
            ->execute();

        // Handle PDF generation
        $templateId = $request->getDataInt('bill_template') ?? $bill->type->defaultTemplate?->id ?? 0;

        // @todo It would be nice if we could somehow make the two settings calls below in one go.
        //          Maybe always make with unit if defined AND with null (maybe also with app?)
        //          Then return none-empty strictest match
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

        $template         = new NullCollection();
        $defaultTemplates = new NullCollection();
        $defaultAssets    = new NullCollection();

        /** @var \Modules\Media\Models\Collection[] $collections */
        $collections = CollectionMapper::get()
            ->with('sources')
            ->where('id', [
                (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content,
                (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content,
                $templateId,
            ], 'IN')
            ->execute();

        foreach ($collections as $collection) {
            if ($collection->id === $templateId) {
                $template = $collection;
            } elseif ($collection->id === (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content) {
                $defaultTemplates = $collection;
            } elseif ($collection->id === (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content) {
                $defaultAssets = $collection;
            }
        }

        require_once __DIR__ . '/../../../Resources/tcpdf/TCPDF.php';

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');

        $view->data['defaultTemplates'] = $defaultTemplates;
        $view->data['defaultAssets']    = $defaultAssets;
        $view->data['bill']             = $bill;

        // @todo add bill data such as company name bank information, ..., etc.

        $pdf = $view->render();

        $path   = $this->createBillDir($bill);
        $pdfDir = __DIR__ . '/../../../Modules/Media/Files' . $path;

        $status = \is_dir($pdfDir) ? true : \mkdir($pdfDir, 0755, true);
        if (!$status) {
            // @codeCoverageIgnoreStart
            $response->set($request->uri->__toString(), new FormValidation(['status' => $status]));
            $response->header->status = RequestStatusCode::R_400;

            \phpOMS\Log\FileLogger::getInstance()->error(
                \phpOMS\Log\FileLogger::MSG_FULL, [
                    'message' => 'Couldn\'t create bill path: ' . $bill->id,
                    'line'    => __LINE__,
                    'file'    => self::class,
                ]
            );

            return;
            // @codeCoverageIgnoreEnd
        }

        /** @var \Model\Setting $internalType */
        $internalType = $this->app->appSettings->get(
            names: SettingsEnum::INTERNAL_MEDIA_TYPE,
            module: self::NAME
        );

        // @todo Check if old file exists -> update media
        $oldFile = $bill->getFileByType((int) $internalType->content);

        $billFileName = ($bill->billDate?->format('Y-m-d') ?? '0') . '_' . $bill->number . '.pdf';

        \file_put_contents($pdfDir . '/' . $billFileName, $pdf);
        if (!\is_file($pdfDir . '/' . $billFileName)) {
            $response->header->status = RequestStatusCode::R_400;
            $response->set($request->uri->__toString(), []);

            \phpOMS\Log\FileLogger::getInstance()->error(
                \phpOMS\Log\FileLogger::MSG_FULL, [
                    'message' => 'Couldn\'t render bill pdf: ' . $bill->id,
                    'line'    => __LINE__,
                    'file'    => self::class,
                ]
            );

            return;
        }

        $media = null;
        if ($oldFile->id === 0) {
            // Creating new bill archive pdf
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

            // Add type to media
            $this->createModelRelation(
                $request->header->account,
                $media->id,
                (int) $internalType->content,
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
        } else {
            // Updating existing bill archive pdf
            $media = clone $oldFile;
            if (\realpath($pdfDir . '/' . $billFileName) !== \realpath($oldFile->getAbsolutePath())) {
                \unlink($oldFile->getAbsolutePath());
            }

            $media->setPath(\Modules\Media\Controller\ApiController::normalizeDbPath($pdfDir . '/' . $billFileName));
            $media->setVirtualPath($path);
            $media->size = (int) \filesize($media->getAbsolutePath());

            $this->updateModel($request->header->account, $oldFile, $media, MediaMapper::class, 'media', $request->getOrigin());
        }

        $this->createStandardCreateResponse($request, $response, $media);
    }

    /**
     * Send bill as email
     *
     * @param Media  $media    Media to send
     * @param string $email    Email address
     * @param int    $template Email template
     * @param string $language Message language
     *
     * @return void
     *
     * @question Maybe we should move this entire function to the Messages module
     *      There is nothing bill specific in here.
     *
     * @since 1.0.0
     */
    public function sendBillEmail(Media $media, string $email, int $template, string $language = 'en') : void
    {
        $handler = $this->app->moduleManager->get('Admin', 'Api')->setUpServerMailHandler();

        $mail = EmailMapper::get()
            ->with('l11n')
            ->where('id', $template)
            ->where('l11n/language', $language)
            ->execute();

        $status = false;
        if ($mail->id !== 0) {
            $status = $this->app->moduleManager->get('Admin', 'Api')->setupEmailDefaults($mail, $language);
        }

        $mail->addTo($email);
        $mail->addAttachment($media->getAbsolutePath(), $media->name);

        if ($status) {
            $status = $handler->send($mail);
        }

        if (!$status) {
            \phpOMS\Log\FileLogger::getInstance()->error(
                \phpOMS\Log\FileLogger::MSG_FULL, [
                    'message' => 'Couldn\'t send bill media: ' . $media->id,
                    'line'    => __LINE__,
                    'file'    => self::class,
                ]
            );
        }
    }

    /**
     * Api method to create Note
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
        $model = $response->getDataArray($request->uri->__toString())['response'];
        $this->createModelRelation($request->header->account, $request->getDataInt('id'), $model->id, BillMapper::class, 'notes', '', $request->getOrigin());
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

        // @todo check if bill can be deleted
        // @todo adjust stock transfer

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
        $old = BillElementMapper::get()
            ->with('bill')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        if ($old->bill->status === BillStatus::ARCHIVED) {
            $response->header->status = RequestStatusCode::R_423;
            $this->createInvalidUpdateResponse($request, $response, $old);

            return;
        }

        // @todo can be edited?
        // @todo adjust transfer protocols

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
     * @todo Implement API update function
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
     * @todo Implement API validation function
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

        // @todo check if can be deleted
        // @todo handle transactions and bill update

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
        $accountId = $request->header->account;
        if (!$this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::BILL_NOTE, $request->getDataInt('id'))
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $this->app->moduleManager->get('Editor', 'Api')->apiEditorUpdate($request, $response, $data);
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
        $accountId = $request->header->account;
        if (!$this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::DELETE, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::BILL_NOTE, $request->getDataInt('id'))
        ) {
            $this->fillJsonResponse($request, $response, NotificationLevel::HIDDEN, '', '', []);
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        $this->app->moduleManager->get('Editor', 'Api')->apiEditorDelete($request, $response, $data);
    }
}
