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

use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeL11nMapper;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Media\Models\NullCollection;
use phpOMS\Localization\BaseStringL11n;
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
final class ApiBillTypeController extends Controller
{
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
        if (!empty($val = $this->validateBillTypeCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $billType = $this->createBillTypeFromRequest($request);
        $this->createModel($request->header->account, $billType, BillTypeMapper::class, 'bill_type', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $billType);
    }

    /**
     * Method to create item attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BillType
     *
     * @since 1.0.0
     */
    private function createBillTypeFromRequest(RequestAbstract $request) : BillType
    {
        $billType = new BillType($request->getDataString('name') ?? '');
        $billType->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        $billType->numberFormat    = $request->getDataString('number_format') ?? '{id}';
        $billType->transferStock   = $request->getDataBool('transfer_stock') ?? false;
        $billType->isTemplate      = $request->getDataBool('is_template') ?? false;
        $billType->transferType    = $request->getDataInt('transfer_type') ?? BillTransferType::SALES;
        $billType->defaultTemplate = $request->hasData('template')
            ? new NullCollection((int) $request->getData('template'))
            : null;

        if ($request->hasData('template')) {
            $billType->addTemplate(new NullCollection((int) $request->getData('template')));
        }

        return $billType;
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
    private function validateBillTypeCreate(RequestAbstract $request) : array
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
        if (!empty($val = $this->validateBillTypeL11nCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $billTypeL11n = $this->createBillTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $billTypeL11n, BillTypeL11nMapper::class, 'bill_type_l11n', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $billTypeL11n);
    }

    /**
     * Method to create item attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createBillTypeL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $billTypeL11n      = new BaseStringL11n();
        $billTypeL11n->ref = $request->getDataInt('type') ?? 0;
        $billTypeL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $billTypeL11n->content = $request->getDataString('title') ?? '';

        return $billTypeL11n;
    }

    /**
     * Validate item attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillTypeL11nCreate(RequestAbstract $request) : array
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
     * Api method to update BillType
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
    public function apiBillTypeUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillTypeUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var BillType $old */
        $old = BillTypeMapper::get()->where('id', (int) $request->getData('id'));
        $new = $this->updateBillTypeFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillTypeMapper::class, 'bill_type', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillType from request.
     *
     * @param RequestAbstract $request Request
     * @param BillType        $new     Model to modify
     *
     * @return BillType
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function updateBillTypeFromRequest(RequestAbstract $request, BillType $new) : BillType
    {
        $new->numberFormat    = $request->getDataString('number_format') ?? $new->numberFormat;
        $new->transferStock   = $request->getDataBool('transfer_stock') ?? $new->transferStock;
        $new->isTemplate      = $request->getDataBool('is_template') ?? $new->isTemplate;
        $new->transferType    = $request->getDataInt('transfer_type') ?? $new->transferType;
        $new->defaultTemplate = $request->hasData('template')
            ? new NullCollection((int) $request->getData('template'))
            : $new->defaultTemplate;

        return $new;
    }

    /**
     * Validate BillType update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillTypeUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillType
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
    public function apiBillTypeDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillTypeDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billType, BillTypeMapper::class, 'bill_type', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billType);
    }

    /**
     * Validate BillType delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillTypeDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillTypeL11n
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
    public function apiBillTypeL11nUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillTypeL11nUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var BaseStringL11n $old */
        $old = BillTypeL11nMapper::get()->where('id', (int) $request->getData('id'));
        $new = $this->updateBillTypeL11nFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillTypeL11nMapper::class, 'bill_type_l11n', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillTypeL11n from request.
     *
     * @param RequestAbstract $request Request
     * @param BaseStringL11n  $new     Model to modify
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    public function updateBillTypeL11nFromRequest(RequestAbstract $request, BaseStringL11n $new) : BaseStringL11n
    {
        $new->ref = $request->getDataInt('type') ?? $new->ref;
        $new->setLanguage(
            $request->getDataString('language') ?? $new->language
        );
        $new->content = $request->getDataString('title') ?? $new->content;

        return $new;
    }

    /**
     * Validate BillTypeL11n update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillTypeL11nUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillTypeL11n
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
    public function apiBillTypeL11nDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillTypeL11nDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var BaseStringL11n $billTypeL11n */
        $billTypeL11n = BillTypeL11nMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billTypeL11n, BillTypeL11nMapper::class, 'bill_type_l11n', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billTypeL11n);
    }

    /**
     * Validate BillTypeL11n delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillTypeL11nDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
