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

use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeL11nMapper;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Media\Models\NullCollection;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;

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
            $response->data['bill_type_create'] = new FormValidation($val);
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $billType = $this->createBillTypeFromRequest($request);
        $this->createModel($request->header->account, $billType, BillTypeMapper::class, 'bill_type', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Bill type', 'Bill type successfully created', $billType);
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
        $billType->numberFormat    = (string) ($request->getData('number_format') ?? '{id}');
        $billType->transferStock   = (bool) ($request->getData('transfer_stock') ?? false);
        $billType->isTemplate      = (bool) ($request->getData('is_template') ?? false);
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
            $response->data['bill_type_l11n_create'] = new FormValidation($val);
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $billTypeL11n = $this->createBillTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $billTypeL11n, BillTypeL11nMapper::class, 'bill_type_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Localization', 'Localization successfully created', $billTypeL11n);
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
            $request->getDataString('language') ?? $request->getLanguage()
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
}
