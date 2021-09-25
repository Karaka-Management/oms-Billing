<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\NullBillType;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\UploadStatus;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Autoloader;
use phpOMS\Localization\Money;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class ApiController extends Controller
{
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
    public function apiBillCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateBillCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $bill = $this->createBillFromRequest($request, $response, $data);
        $this->createModel($request->header->account, $bill, BillMapper::class, 'bill', $request->getOrigin());
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
        $account = null;
        if ($request->getData('client') !== null) {
            $account = ClientMapper::get((int) $request->getData('client'));
        } elseif ($request->getData('supplier') !== null) {
            $account = SupplierMapper::get((int) $request->getData('supplier'));
        }

        /* @var \Modules\Account\Models\Account $account */
        $bill                  = new Bill();
        $bill->createdBy       = new NullAccount($request->header->account);
        $bill->number          = '{y}-{id}'; // @todo: use admin defined format
        $bill->billTo          = $request->getData('billto')
            ?? ($account->profile->account->name1 . (!empty($account->profile->account->name2) ? ', ' . $account->profile->account->name2 : '')); // @todo: use defaultInvoiceAddress or mainAddress. also consider to use billto1, billto2, billto3 (for multiple lines e.g. name2, fao etc.)
        $bill->billZip         = $request->getData('billtopostal') ?? $account->mainAddress->postal;
        $bill->billCity        = $request->getData('billtocity') ?? $account->mainAddress->city;
        $bill->billCountry     = $request->getData('billtocountry') ?? $account->mainAddress->getCountry();
        $bill->type            = new NullBillType((int) $request->getData('type'));
        $bill->client          = $request->getData('client') === null ? null : $account;
        $bill->supplier        = $request->getData('supplier') === null ? null : $account;
        $bill->performanceDate = new \DateTime($request->getData('performancedate') ?? 'now');

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
        /*if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
            || ($val['status'] = (
                $request->getData('status') !== null
                && !WikiStatus::isValidValue((int) $request->getData('status'))
            ))
        ) {
            return $val;
        }*/

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
    public function apiBillElementCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateBillElementCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $element = $this->createBillElementFromRequest($request, $response, $data);
        $this->createModel($request->header->account, $element, BillElementMapper::class, 'bill_element', $request->getOrigin());

        $old = BillMapper::get($element->bill);
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
     */
    public function createBillElementFromRequest(RequestAbstract $request, ResponseAbstract $response, $data = null) : BillElement
    {
        $element       = new BillElement();
        $element->bill = (int) $request->getData('bill');
        $element->item = $request->getData('item', 'int');

        if ($element->item === null) {
            return $element;
        }

        $item = ItemMapper::with('language', $response->getLanguage())::get($element->item);
        // @todo: which item name should be stored in the database? server language (problem for international company with subsidiaries)? customer default language/customer invoice language?
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
            $bill->net->add($element->singleSalesPriceNet);
            $bill->costs->add($element->singlePurchasePriceNet);
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
        if (($val['bill'] = empty($request->getData('bill')))
        ) {
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
    public function apiBillPdfArchiveCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        Autoloader::addPath(__DIR__ . '/../../../Resources/');

        $bill = BillMapper::get($request->getData('bill'));

        $defaultTemplate = $this->app->appSettings->get(null, 'default_template', null, self::MODULE_NAME);
        $template        = CollectionMapper::get((int) $defaultTemplate['content']);

        $pdfDir = __DIR__ . '/../../../Modules/Media/Files/Modules/Billing/Bills/'
            . $bill->createdAt->format('Y') . '/'
            . $bill->createdAt->format('m') . '/'
            . $bill->createdAt->format('d') . '/';

        $status = !\is_dir($pdfDir) ? \mkdir($pdfDir, 0755, true) : true;
        if ($status === false) {
            $response->set($request->uri->__toString(), new FormValidation(['status' => $status]));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/' . \substr($template->getSourceByName('bill.pdf.php')->getPath(), 0, -8), 'pdf.php');
        $view->setData('bill', $bill);
        $view->setData('path', $pdfDir . $request->getData('bill') . '.pdf');

        $pdf = $view->build();

        $media = $this->app->moduleManager->get('Media')->createDbEntry(
            [
                'status'    => UploadStatus::OK,
                'name'      => $request->getData('bill') . '.pdf',
                'path'      => $pdfDir,
                'filename'  => $request->getData('bill') . '.pdf',
                'size'      => \filesize($pdfDir . $request->getData('bill') . '.pdf'),
                'extension' => 'pdf',
            ],
            $request->header->account,
            '/Modules/Billing/Bills/'
                . $bill->createdAt->format('Y') . '/'
                . $bill->createdAt->format('m') . '/'
                . $bill->createdAt->format('d'),
            null // @todo: get bill MediaType
        );

        $this->createModelRelation(
            $request->header->account,
            $bill->getId(),
            $media->getId(),
            BillMapper::class, 'media', '', $request->getOrigin()
        );
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
    public function apiBillPdfCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
    }
}
