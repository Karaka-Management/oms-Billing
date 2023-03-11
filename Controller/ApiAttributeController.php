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

use Modules\Billing\Models\Attribute\BillAttribute;
use Modules\Billing\Models\Attribute\BillAttributeMapper;
use Modules\Billing\Models\Attribute\BillAttributeType;
use Modules\Billing\Models\Attribute\BillAttributeTypeL11nMapper;
use Modules\Billing\Models\Attribute\BillAttributeTypeMapper;
use Modules\Billing\Models\Attribute\BillAttributeValue;
use Modules\Billing\Models\Attribute\BillAttributeValueL11nMapper;
use Modules\Billing\Models\Attribute\BillAttributeValueMapper;
use Modules\Billing\Models\Attribute\NullBillAttributeType;
use Modules\Billing\Models\Attribute\NullBillAttributeValue;
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
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiAttributeController extends Controller
{
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
        if (!empty($val = $this->validateBillAttributeCreate($request))) {
            $response->set('attribute_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attribute = $this->createBillAttributeFromRequest($request);
        $this->createModel($request->header->account, $attribute, BillAttributeMapper::class, 'attribute', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute', 'Attribute successfully created', $attribute);
    }

    /**
     * Method to create item attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BillAttribute
     *
     * @since 1.0.0
     */
    private function createBillAttributeFromRequest(RequestAbstract $request) : BillAttribute
    {
        $attribute       = new BillAttribute();
        $attribute->bill = (int) $request->getData('bill');
        $attribute->type = new NullBillAttributeType((int) $request->getData('type'));

        if ($request->getData('value') !== null) {
            $attribute->value = new NullBillAttributeValue((int) $request->getData('value'));
        } else {
            $newRequest = clone $request;
            $newRequest->setData('value', $request->getData('custom'), true);

            $value = $this->createBillAttributeValueFromRequest($newRequest);

            $attribute->value = $value;
        }

        return $attribute;
    }

    /**
     * Validate bill attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = empty($request->getData('type')))
            || ($val['value'] = (empty($request->getData('value')) && empty($request->getData('custom'))))
            || ($val['bill'] = empty($request->getData('bill')))
        ) {
            return $val;
        }

        return [];
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
        if (!empty($val = $this->validateBillAttributeTypeL11nCreate($request))) {
            $response->set('attr_type_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createBillAttributeTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, BillAttributeTypeL11nMapper::class, 'attr_type_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Localization', 'Localization successfully created', $attrL11n);
    }

    /**
     * Method to create bill attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createBillAttributeTypeL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = (int) ($request->getData('type') ?? 0);
        $attrL11n->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $attrL11n->content = (string) ($request->getData('title') ?? '');

        return $attrL11n;
    }

    /**
     * Validate bill attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = empty($request->getData('title')))
            || ($val['type'] = empty($request->getData('type')))
        ) {
            return $val;
        }

        return [];
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
        if (!empty($val = $this->validateBillAttributeTypeCreate($request))) {
            $response->set('attr_type_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrType = $this->createBillAttributeTypeFromRequest($request);
        $this->createModel($request->header->account, $attrType, BillAttributeTypeMapper::class, 'attr_type', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type', 'Attribute type successfully created', $attrType);
    }

    /**
     * Method to create bill attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BillAttributeType
     *
     * @since 1.0.0
     */
    private function createBillAttributeTypeFromRequest(RequestAbstract $request) : BillAttributeType
    {
        $attrType                    = new BillAttributeType($request->getData('name') ?? '');
        $attrType->datatype          = (int) ($request->getData('datatype') ?? 0);
        $attrType->custom            = (bool) ($request->getData('custom') ?? false);
        $attrType->isRequired        = (bool) ($request->getData('is_required') ?? false);
        $attrType->validationPattern = (string) ($request->getData('validation_pattern') ?? '');
        $attrType->setL11n((string) ($request->getData('title') ?? ''), $request->getData('language') ?? ISO639x1Enum::_EN);
        $attrType->setFields((int) ($request->getData('fields') ?? 0));

        return $attrType;
    }

    /**
     * Validate bill attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = empty($request->getData('title')))
            || ($val['name'] = empty($request->getData('name')))
        ) {
            return $val;
        }

        return [];
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
        if (!empty($val = $this->validateBillAttributeValueCreate($request))) {
            $response->set('attr_value_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrValue = $this->createBillAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, BillAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('type'),
                $attrValue->getId(),
                BillAttributeTypeMapper::class, 'defaults', '', $request->getOrigin()
            );
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute value', 'Attribute value successfully created', $attrValue);
    }

    /**
     * Method to create bill attribute value from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BillAttributeValue
     *
     * @since 1.0.0
     */
    private function createBillAttributeValueFromRequest(RequestAbstract $request) : BillAttributeValue
    {
        /** @var BillAttributeType $type */
        $type = BillAttributeTypeMapper::get()
            ->where('id', (int) ($request->getData('type') ?? 0))
            ->execute();

        $attrValue            = new BillAttributeValue();
        $attrValue->isDefault = (bool) ($request->getData('default') ?? false);
        $attrValue->setValue($request->getData('value'), $type->datatype);

        if ($request->getData('title') !== null) {
            $attrValue->setL11n($request->getData('title'), $request->getData('language') ?? ISO639x1Enum::_EN);
        }

        return $attrValue;
    }

    /**
     * Validate bill attribute value create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = empty($request->getData('type')))
            || ($val['value'] = empty($request->getData('value')))
        ) {
            return $val;
        }

        return [];
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
        if (!empty($val = $this->validateBillAttributeValueL11nCreate($request))) {
            $response->set('attr_value_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createBillAttributeValueL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, BillAttributeValueL11nMapper::class, 'attr_value_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Localization', 'Localization successfully created', $attrL11n);
    }

    /**
     * Method to create bill attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createBillAttributeValueL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = (int) ($request->getData('value') ?? 0);
        $attrL11n->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $attrL11n->content = (string) ($request->getData('title') ?? '');

        return $attrL11n;
    }

    /**
     * Validate bill attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = empty($request->getData('title')))
            || ($val['value'] = empty($request->getData('value')))
        ) {
            return $val;
        }

        return [];
    }
}
