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

use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeType;
use Modules\Attribute\Models\AttributeValue;
use Modules\Attribute\Models\NullAttribute;
use Modules\Attribute\Models\NullAttributeType;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\Billing\Models\Attribute\BillAttributeMapper;
use Modules\Billing\Models\Attribute\BillAttributeTypeL11nMapper;
use Modules\Billing\Models\Attribute\BillAttributeTypeMapper;
use Modules\Billing\Models\Attribute\BillAttributeValueL11nMapper;
use Modules\Billing\Models\Attribute\BillAttributeValueMapper;
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
            $response->data['attribute_create'] = new FormValidation($val);
            $response->header->status           = RequestStatusCode::R_400;

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
     * @return Attribute
     *
     * @since 1.0.0
     */
    private function createBillAttributeFromRequest(RequestAbstract $request) : Attribute
    {
        $attribute       = new Attribute();
        $attribute->ref  = (int) $request->getData('ref');
        $attribute->type = new NullAttributeType((int) $request->getData('type'));

        if ($request->hasData('value')) {
            $attribute->value = new NullAttributeValue((int) $request->getData('value'));
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
        if (($val['type'] = !$request->hasData('type'))
            || ($val['value'] = (!$request->hasData('value') && !$request->hasData('custom')))
            || ($val['bill'] = !$request->hasData('bill'))
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
            $response->data['attr_type_l11n_create'] = new FormValidation($val);
            $response->header->status                = RequestStatusCode::R_400;

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
        $attrL11n->ref = $request->getDataInt('type') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

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
        if (($val['title'] = !$request->hasData('title'))
            || ($val['type'] = !$request->hasData('type'))
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
            $response->data['attr_type_create'] = new FormValidation($val);
            $response->header->status           = RequestStatusCode::R_400;

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
     * @return AttributeType
     *
     * @since 1.0.0
     */
    private function createBillAttributeTypeFromRequest(RequestAbstract $request) : AttributeType
    {
        $attrType                    = new AttributeType($request->getDataString('name') ?? '');
        $attrType->datatype          = $request->getDataInt('datatype') ?? 0;
        $attrType->custom            = $request->getDataBool('custom') ?? false;
        $attrType->isRequired        = $request->getDataBool('is_required') ?? false;
        $attrType->validationPattern = $request->getDataString('validation_pattern') ?? '';
        $attrType->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        $attrType->setFields($request->getDataInt('fields') ?? 0);

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
        if (($val['title'] = !$request->hasData('title'))
            || ($val['name'] = !$request->hasData('name'))
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
            $response->data['attr_value_create'] = new FormValidation($val);
            $response->header->status            = RequestStatusCode::R_400;

            return;
        }

        $attrValue = $this->createBillAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, BillAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('type'),
                $attrValue->id,
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
     * @return AttributeValue
     *
     * @since 1.0.0
     */
    private function createBillAttributeValueFromRequest(RequestAbstract $request) : AttributeValue
    {
        /** @var \Modules\Attribute\Models\AttributeType $type */
        $type = BillAttributeTypeMapper::get()
            ->where('id', $request->getDataInt('type') ?? 0)
            ->execute();

        $attrValue            = new AttributeValue();
        $attrValue->isDefault = $request->getDataBool('default') ?? false;
        $attrValue->setValue($request->getDataString('value'), $type->datatype);

        if ($request->hasData('title')) {
            $attrValue->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
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
        if (($val['type'] = !$request->hasData('type'))
            || ($val['value'] = !$request->hasData('value'))
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
            $response->data['attr_value_l11n_create'] = new FormValidation($val);
            $response->header->status                 = RequestStatusCode::R_400;

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
        $attrL11n->ref = $request->getDataInt('value') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

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
        if (($val['title'] = !$request->hasData('title'))
            || ($val['value'] = !$request->hasData('value'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillAttribute
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
    public function apiBillAttributeUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeUpdate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var Attribute $old */
        $old = BillAttributeMapper::get()
            ->with('type')
            ->with('type/defaults')
            ->with('value')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $new = $this->updateBillAttributeFromRequest($request, clone $old);

        if ($new->id === 0) {
            // Set response header to invalid request because of invalid data
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $new);

            return;
        }

        $this->updateModel($request->header->account, $old, $new, BillAttributeMapper::class, 'bill_attribute', $request->getOrigin());

        if ($new->value->getValue() !== $old->value->getValue()) {
            $this->updateModel($request->header->account, $old->value, $new->value, BillAttributeValueMapper::class, 'attribute_value', $request->getOrigin());
        }

        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillAttribute from request.
     *
     * @param RequestAbstract  $request Request
     * @param Attribute     $new     Model to modify
     *
     * @return Attribute
     *
     * @since 1.0.0
     */
    public function updateBillAttributeFromRequest(RequestAbstract $request, Attribute $new) : Attribute
    {
        if ($request->hasData('value')) {
            $new->value = $request->hasData('value') ? new NullAttributeValue((int) $request->getData('value')) : $new->value;
        } else {
            // @todo: fix by only accepting the value id to be used
            // this is a workaround for now because the front end doesn't allow to dynamically show default values.
            $value = $new->type->getDefaultByValue($request->getData('value'));

            // Couldn't find matching default value
            if ($value->id === 0) {
                return new NullAttribute();
            }

            $new->value = $value;
        }

        return $new;
    }

    /**
     * Validate BillAttribute update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))
            || ($val['value'] = (!$request->hasData('value') && !$request->hasData('custom')))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillAttribute
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
    public function apiBillAttributeDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeDelete($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        $billAttribute = BillAttributeMapper::get()
            ->with('type')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        if ($billAttribute->type->isRequired) {
            $this->createInvalidDeleteResponse($request, $response, []);

            return;
        }

        $this->deleteModel($request->header->account, $billAttribute, BillAttributeMapper::class, 'bill_attribute', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billAttribute);
    }

    /**
     * Validate BillAttribute delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillAttributeTypeL11n
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
    public function apiBillAttributeTypeL11nUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeTypeL11nUpdate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var BaseStringL11n $old */
        $old = BillAttributeTypeL11nMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateBillAttributeTypeL11nFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillAttributeTypeL11nMapper::class, 'bill_attribute_type_l11n', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillAttributeTypeL11n from request.
     *
     * @param RequestAbstract  $request Request
     * @param BaseStringL11n     $new     Model to modify
     *
     * @return BaseStringL11n
     *
     * @todo: consider to move all these FromRequest functions to the attribute module since they are the same in every module!
     *
     * @since 1.0.0
     */
    public function updateBillAttributeTypeL11nFromRequest(RequestAbstract $request, BaseStringL11n $new) : BaseStringL11n
    {
        $new->setLanguage(
            $request->getDataString('language') ?? $new->language
        );
        $new->content = $request->getDataString('title') ?? $new->content;

        return $new;
    }

    /**
     * Validate BillAttributeTypeL11n update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeL11nUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillAttributeTypeL11n
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
    public function apiBillAttributeTypeL11nDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeTypeL11nDelete($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\BillAttributeTypeL11n $billAttributeTypeL11n */
        $billAttributeTypeL11n = BillAttributeTypeL11nMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billAttributeTypeL11n, BillAttributeTypeL11nMapper::class, 'bill_attribute_type_l11n', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billAttributeTypeL11n);
    }

    /**
     * Validate BillAttributeTypeL11n delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeL11nDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillAttributeType
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
    public function apiBillAttributeTypeUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeTypeUpdate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var AttributeType $old */
        $old = BillAttributeTypeMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateBillAttributeTypeFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillAttributeTypeMapper::class, 'bill_attribute_type', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillAttributeType from request.
     *
     * @param RequestAbstract  $request Request
     * @param AttributeType     $new     Model to modify
     *
     * @return AttributeType
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function updateBillAttributeTypeFromRequest(RequestAbstract $request, AttributeType $new) : AttributeType
    {
        $new->datatype          = $request->getDataInt('datatype') ?? $new->datatype;
        $new->custom            = $request->getDataBool('custom') ?? $new->custom;
        $new->isRequired        = $request->getDataBool('is_required') ?? $new->isRequired;
        $new->validationPattern = $request->getDataString('validation_pattern') ?? $new->validationPattern;

        return $new;
    }

    /**
     * Validate BillAttributeType update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillAttributeType
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function apiBillAttributeTypeDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeTypeDelete($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\BillAttributeType $billAttributeType */
        $billAttributeType = BillAttributeTypeMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billAttributeType, BillAttributeTypeMapper::class, 'bill_attribute_type', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billAttributeType);
    }

    /**
     * Validate BillAttributeType delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateBillAttributeTypeDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillAttributeValue
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
    public function apiBillAttributeValueUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeValueUpdate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var AttributeValue $old */
        $old = BillAttributeValueMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateBillAttributeValueFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillAttributeValueMapper::class, 'bill_attribute_value', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillAttributeValue from request.
     *
     * @param RequestAbstract  $request Request
     * @param AttributeValue     $new     Model to modify
     *
     * @return AttributeValue
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function updateBillAttributeValueFromRequest(RequestAbstract $request, AttributeValue $new) : AttributeValue
    {
        /** @var \Modules\Attribute\Models\Attribute $type */
        $attr = BillAttributeMapper::get()
            ->with('type')
            ->where('id', $request->getDataInt('attribute') ?? 0)
            ->execute();

        $new->isDefault = $request->getDataBool('default') ?? $new->isDefault;
        $new->setValue($request->getDataString('value'), $attr->type->datatype);

        return $new;
    }

    /**
     * Validate BillAttributeValue update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))
            || ($val['attribute'] = !$request->hasData('attribute'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillAttributeValue
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
    public function apiBillAttributeValueDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        return;

        // @todo: I don't think values can be deleted? Only Attributes
        // However, It should be possible to remove UNUSED default values
        // either here or other function?
        if (!empty($val = $this->validateBillAttributeValueDelete($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\BillAttributeValue $billAttributeValue */
        $billAttributeValue = BillAttributeValueMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billAttributeValue, BillAttributeValueMapper::class, 'bill_attribute_value', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billAttributeValue);
    }

    /**
     * Validate BillAttributeValue delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update BillAttributeValueL11n
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
    public function apiBillAttributeValueL11nUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeValueL11nUpdate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var BaseStringL11n $old */
        $old = BillAttributeValueL11nMapper::get()->where('id', (int) $request->getData('id'));
        $new = $this->updateBillAttributeValueL11nFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, BillAttributeValueL11nMapper::class, 'bill_attribute_value_l11n', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update BillAttributeValueL11n from request.
     *
     * @param RequestAbstract  $request Request
     * @param BaseStringL11n     $new     Model to modify
     *
     * @return BaseStringL11n
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    public function updateBillAttributeValueL11nFromRequest(RequestAbstract $request, BaseStringL11n $new) : BaseStringL11n
    {
        $new->setLanguage(
            $request->getDataString('language') ?? $new->language
        );
        $new->content = $request->getDataString('title') ?? $new->content;

        return $new;
    }

    /**
     * Validate BillAttributeValueL11n update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueL11nUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete BillAttributeValueL11n
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
    public function apiBillAttributeValueL11nDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateBillAttributeValueL11nDelete($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                     = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Billing\Models\BillAttributeValueL11n $billAttributeValueL11n */
        $billAttributeValueL11n = BillAttributeValueL11nMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $billAttributeValueL11n, BillAttributeValueL11nMapper::class, 'bill_attribute_value_l11n', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $billAttributeValueL11n);
    }

    /**
     * Validate BillAttributeValueL11n delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     *
     * @since 1.0.0
     */
    private function validateBillAttributeValueL11nDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
