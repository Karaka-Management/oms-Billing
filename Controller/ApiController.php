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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillUpdate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillCreate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiMediaAddToBill($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillElementCreate($request, $response, $data);
    }

    /**
     * Api method to create a bill preview
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
    public function apiPreviewRender(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiPreviewRender($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfArchiveCreate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfCreate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiNoteCreate($request, $response, $data);
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
        $this->app->moduleManager->get('Billing', 'ApiPurchase')->apiSupplierBillUpload($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiBillTypeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBillType')->apiBillTypeCreate($request, $response, $data);
    }

    /**
     * Api method to create item attribute l11n
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
    public function apiBillTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiBillType')->apiBillTypeL11nCreate($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiTaxCombinationCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiTax')->apiTaxCombinationCreate($request, $response, $data);
    }

    /**
     * Api method to create item bill type
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
    public function apiPriceCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiPrice')->apiPriceCreate($request, $response, $data);
    }

    /**
     * Api method to create item attribute
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
    public function apiBillAttributeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute l11n
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
    public function apiBillAttributeTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeTypeL11nCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute type
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
    public function apiBillAttributeTypeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeTypeCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute value
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
    public function apiBillAttributeValueCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeValueCreate($request, $response, $data);
    }

    /**
     * Api method to create bill attribute l11n
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
    public function apiBillAttributeValueL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeValueL11nCreate($request, $response, $data);
    }

    /**
     * Api method to find subscriptions
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
    public function apiSubscriptionFind(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
    }
}
