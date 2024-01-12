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

use Modules\Billing\Models\PaymentTermL11nMapper;
use Modules\Billing\Models\PaymentTermMapper;
use Modules\Billing\Models\ShippingTermL11nMapper;
use Modules\Billing\Models\ShippingTermMapper;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\BaseStringL11nType;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillUpdate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillCreate($request, $response, $data);
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
    public function apiMediaAddToBill(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiMediaAddToBill($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillElementCreate($request, $response, $data);
    }

    /**
     * Api method to create a bill preview
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiPreviewRender($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfArchiveCreate($request, $response, $data);
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
    public function apiBillPdfCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfCreate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiNoteCreate($request, $response, $data);
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
    public function apiSupplierBillUpload(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiPurchase')->apiSupplierBillUpload($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiBillTypeCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBillType')->apiBillTypeCreate($request, $response, $data);
    }

    /**
     * Api method to create item attribute l11n
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
    public function apiBillTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBillType')->apiBillTypeL11nCreate($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiTaxCombinationCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiTax')->apiTaxCombinationCreate($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiPriceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiPrice')->apiPriceCreate($request, $response, $data);
    }

    /**
     * Api method to create item attribute
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
    public function apiBillAttributeCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute l11n
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
    public function apiBillAttributeTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeTypeL11nCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute type
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
    public function apiBillAttributeTypeCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeTypeCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute value
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
    public function apiBillAttributeValueCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeValueCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute l11n
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
    public function apiBillAttributeValueL11nCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeValueL11nCreate($request, $response, $data);
    }

    /**
     * Api method to find subscriptions
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
    public function apiSubscriptionFind(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
    }

    /**
     * Api method to create item payment type
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
    public function apiPaymentTermCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validatePaymentTermCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $paymentTerm = $this->createPaymentTermFromRequest($request);
        $this->createModel($request->header->account, $paymentTerm, PaymentTermMapper::class, 'payment_term', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $paymentTerm);
    }

    /**
     * Validate payment create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validatePaymentTermCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['name'] = !$request->hasData('name'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create payment from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11nType
     *
     * @since 1.0.0
     */
    private function createPaymentTermFromRequest(RequestAbstract $request) : BaseStringL11nType
    {
        $paymentTerm                    = new BaseStringL11nType($request->getDataString('name') ?? '');
        $paymentTerm->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);

        return $paymentTerm;
    }

    /**
     * Api method to create item payment l11n
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
    public function apiPaymentTermL11nCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validatePaymentTermL11nCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $paymentL11n = $this->createPaymentTermL11nFromRequest($request);
        $this->createModel($request->header->account, $paymentL11n, PaymentTermL11nMapper::class, 'payment_term_l11n', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $paymentL11n);
    }

    /**
     * Validate payment l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validatePaymentTermL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['type'] = !$request->hasData('type'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create payment l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createPaymentTermL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $paymentL11n      = new BaseStringL11n();
        $paymentL11n->ref = $request->getDataInt('type') ?? 0;
        $paymentL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $paymentL11n->content = $request->getDataString('title') ?? '';

        return $paymentL11n;
    }

    /**
     * Api method to create shipping term type
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
    public function apiShippingTermCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateShippingTermCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $shippingTerm = $this->createShippingTermFromRequest($request);
        $this->createModel($request->header->account, $shippingTerm, ShippingTermMapper::class, 'shipping_term', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $shippingTerm);
    }

    /**
     * Validate shipping create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateShippingTermCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['name'] = !$request->hasData('name'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create shipping from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11nType
     *
     * @since 1.0.0
     */
    private function createShippingTermFromRequest(RequestAbstract $request) : BaseStringL11nType
    {
        $shippingTerm                    = new BaseStringL11nType($request->getDataString('name') ?? '');
        $shippingTerm->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);

        return $shippingTerm;
    }

    /**
     * Api method to create shipping term l11n
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
    public function apiShippingTermL11nCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateShippingTermL11nCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $shippingL11n = $this->createShippingTermL11nFromRequest($request);
        $this->createModel($request->header->account, $shippingL11n, ShippingTermL11nMapper::class, 'shipping_term_l11n', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $shippingL11n);
    }

    /**
     * Validate shipping l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateShippingTermL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['type'] = !$request->hasData('type'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create shipping l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createShippingTermL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $shippingL11n      = new BaseStringL11n();
        $shippingL11n->ref = $request->getDataInt('type') ?? 0;
        $shippingL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $shippingL11n->content = $request->getDataString('title') ?? '';

        return $shippingL11n;
    }
}
