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

use Modules\Attribute\Models\AttributeValue;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\Billing\Models\Tax\TaxCombination;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\Attribute\ClientAttributeTypeMapper;
use Modules\ClientManagement\Models\Client;
use Modules\Finance\Models\TaxCode;
use Modules\Finance\Models\TaxCodeMapper;
use Modules\ItemManagement\Models\Item;
use Modules\Organization\Models\UnitMapper;
use Modules\SupplierManagement\Models\Attribute\SupplierAttributeTypeMapper;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Security\Guard;
use phpOMS\Stdlib\Base\Address;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiTaxController extends Controller
{
    /**
     * Get tax code from client and item.
     *
     * @param Item   $item           Item to get tax code from
     * @param Client $client         Client to get tax code from
     * @param string $defaultCountry default country to use if no valid tax code could be found and if the unit country code shouldn't be used
     *
     * @return TaxCombination
     *
     * @since 1.0.0
     */
    public function getTaxForPerson(Item $item, ?Client $client = null, ?Supplier $supplier = null, string $defaultCountry = '') : TaxCombination
    {
        // @todo define default sales tax code if none available?!
        $itemCode        = 0;
        $accountCode     = 0;
        $combinationType = 'clientCode';

        if ($client !== null) {
            $itemCode    = $item->getAttribute('sales_tax_code')->value->id;
            $accountCode = $client->getAttribute('sales_tax_code')->value->id;
        } else {
            $itemCode        = $item->getAttribute('purchase_tax_code')->value->id;
            $accountCode     = $supplier->getAttribute('purchase_tax_code')->value->id;
            $combinationType = 'supplierCode';
        }

        /** @var \Modules\Billing\Models\Tax\TaxCombination $taxCombination */
        $taxCombination = TaxCombinationMapper::get()
            ->with('taxCode')
            ->where('itemCode', $itemCode)
            ->where($combinationType, $accountCode)
            ->execute();

        if ($taxCombination->taxCode->id !== 0) {
            return $taxCombination;
        }

        /** @var \Modules\Organization\Models\Unit $unit */
        $unit = UnitMapper::get()
            ->with('mainAddress')
            ->where('id', $this->app->unitId)
            ->execute();

        // Create dummy
        $account              = $client !== null ? new Client() : new Supplier();
        $account->mainAddress = $unit->mainAddress;

        if (!empty($defaultCountry)) {
            $account->mainAddress->setCountry($defaultCountry);
        }

        $taxCodeAttribute = $client !== null
            ? $this->getClientTaxCode($account,  $unit->mainAddress)
            : $this->getSupplierTaxCode($account,  $unit->mainAddress);

        return TaxCombinationMapper::get()
            ->with('taxCode')
            ->where('itemCode', $itemCode)
            ->where($combinationType, $taxCodeAttribute->id)
            ->execute();
    }

    /**
     * Create a tax combination for a client and item
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function apiTaxCombinationCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaxCombinationCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $tax = $this->createTaxCombinationFromRequest($request);
        $this->createModel($request->header->account, $tax, TaxCombinationMapper::class, 'tax_combination', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $tax);
    }

    /**
     * Method to create item attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaxCombination
     *
     * @since 1.0.0
     */
    private function createTaxCombinationFromRequest(RequestAbstract $request) : TaxCombination
    {
        $tax                = new TaxCombination();
        $tax->taxType       = $request->getDataInt('tax_type') ?? 1;
        $tax->taxCode->abbr = (string) $request->getData('tax_code');
        $tax->itemCode      = new NullAttributeValue((int) $request->getData('item_code'));

        if ($tax->taxType === 1) {
            $tax->clientCode = new NullAttributeValue((int) $request->getData('account_code'));
        } else {
            $tax->supplierCode = new NullAttributeValue((int) $request->getData('account_code'));
        }

        return $tax;
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
    private function validateTaxCombinationCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['tax_type'] = !$request->hasData('tax_type'))
            || ($val['tax_code'] = !$request->hasData('tax_code'))
            || ($val['item_code'] = !$request->hasData('item_code'))
            || ($val['account_code'] = !$request->hasData('account_code'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Get the client's tax code based on their country and tax office address
     *
     * @param Client  $client           The client to get the tax code for
     * @param Address $taxOfficeAddress The tax office address used to determine the tax code
     *
     * @return AttributeValue The client's tax code
     *
     * @since 1.0.0
     */
    public function getClientTaxCode(Client $client, Address $taxOfficeAddress) : AttributeValue
    {
        /** @var \Modules\Attribute\Models\AttributeType $codes */
        $codes = ClientAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'sales_tax_code')
            ->execute();

        $taxCode = new NullAttributeValue();

        // @todo need to consider own tax id as well
        // @todo consider delivery & invoice location (Reihengeschaeft)
        if ($taxOfficeAddress->country === $client->mainAddress->country) {
            // Same country as we (= local tax code)
            return $codes->getDefaultByValue($client->mainAddress->country);
        } elseif (\in_array($taxOfficeAddress->country, ISO3166TwoEnum::getRegion('eu'))
            && \in_array($client->mainAddress->country, ISO3166TwoEnum::getRegion('eu'))
        ) {
            if (!empty($client->getAttribute('vat_id')->value->getValue())) {
                // Is EU company and we are EU company
                return $codes->getDefaultByValue('EU');
            } else {
                // Is EU private customer and we are EU company
                return $codes->getDefaultByValue($client->mainAddress->country);
            }
        } elseif (\in_array($taxOfficeAddress->country, ISO3166TwoEnum::getRegion('eu'))) {
            // None EU company but we are EU company
            return $codes->getDefaultByValue('INT');
        } else {
            // None EU company and we are also none EU company
            return $codes->getDefaultByValue('INT');
        }

        return $taxCode;
    }

    /**
     * Get the client's tax code based on their country and tax office address
     *
     * @param Supplier $client           The client to get the tax code for
     * @param Address  $taxOfficeAddress The tax office address used to determine the tax code
     *
     * @return AttributeValue The client's tax code
     *
     * @since 1.0.0
     */
    public function getSupplierTaxCode(Supplier $client, Address $taxOfficeAddress) : AttributeValue
    {
        /** @var \Modules\Attribute\Models\AttributeType $codes */
        $codes = SupplierAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'purchase_tax_code')
            ->execute();

        $taxCode = new NullAttributeValue();

        // @todo need to consider own tax id as well
        // @todo consider delivery & invoice location (Reihengeschaeft)
        if ($taxOfficeAddress->country === $client->mainAddress->country) {
            // Same country as we (= local tax code)
            return $codes->getDefaultByValue($client->mainAddress->country);
        } elseif (\in_array($taxOfficeAddress->country, ISO3166TwoEnum::getRegion('eu'))
            && \in_array($client->mainAddress->country, ISO3166TwoEnum::getRegion('eu'))
        ) {
            if (!empty($client->getAttribute('vat_id')->value->getValue())) {
                // Is EU company and we are EU company
                return $codes->getDefaultByValue('EU');
            } else {
                // Is EU private customer and we are EU company
                return $codes->getDefaultByValue($client->mainAddress->country);
            }
        } elseif (\in_array($taxOfficeAddress->country, ISO3166TwoEnum::getRegion('eu'))) {
            // None EU company but we are EU company
            return $codes->getDefaultByValue('INT');
        } else {
            // None EU company and we are also none EU company
            return $codes->getDefaultByValue('INT');
        }

        return $taxCode;
    }

    /**
     * Api method to update TaxCombination
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
    public function apiTaxCombinationUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaxCombinationUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Tax\TaxCombination $old */
        $old = TaxCombinationMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateTaxCombinationFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, TaxCombinationMapper::class, 'tax_combination', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Api method to update TaxCombination
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
    public function apiChangeDefaultTaxCombinations(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateDefaultTaxCombinationChange($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        if (!Guard::isSafePath(
                $path = __DIR__ . '/../Admin/Install/Taxes/' . $request->getDataString('type') . '.json',
                __DIR__ . '/../Admin/Install/Taxes'
            )
        ) {
            $this->createInvalidUpdateResponse($request, $response, []);

            return;
        }

        $content = \file_get_contents($path);
        if ($content === false) {
            $this->createInvalidUpdateResponse($request, $response, []);

            return;
        }

        /** @var array $combinations */
        $combinations = \json_decode($content, true);

        foreach ($combinations as $combination) {
            /** @var TaxCombination[] $old */
            $old = TaxCombinationMapper::getAll()
                ->with('clientCode')
                ->with('itemCode')
                ->where('clientCode/valueStr', $combination['account_code'] ?? '')
                ->where('itemCode/valueStr', $combination['item_code'] ?? '')
                ->execute();

            if (\count($old) !== 1) {
                continue;
            }

            $old = \reset($old);

            $new          = clone $old;
            $new->taxCode = TaxCodeMapper::get()->where('abbr', $combination['tax_code'] ?? '')->execute();

            $this->updateModel($request->header->account, $old, $new, TaxCombinationMapper::class, 'tax_combination', $request->getOrigin());
        }

        $this->createStandardUpdateResponse($request, $response, []);
    }

    /**
     * Validate TaxCombination update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateDefaultTaxCombinationChange(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = !$request->hasData('type'))) {
            return $val;
        }

        return [];
    }

    /**
     * Method to update TaxCombination from request.
     *
     * @param RequestAbstract $request Request
     * @param TaxCombination  $new     Model to modify
     *
     * @return TaxCombination
     *
     * @since 1.0.0
     */
    public function updateTaxCombinationFromRequest(RequestAbstract $request, TaxCombination $new) : TaxCombination
    {
        $new->taxType  = $request->getDataInt('tax_type') ?? $new->taxType;
        $new->taxCode  = $request->getDataString('tax_code') ?? $new->taxCode;
        $new->itemCode = $request->hasData('item_code') ? new NullAttributeValue((int) $request->getData('item_code')) : $new->itemCode;

        if ($new->taxType === 1) {
            $new->clientCode = $request->hasData('account_code') ? new NullAttributeValue((int) $request->getData('account_code')) : $new->clientCode;
        } else {
            $new->supplierCode = $request->hasData('account_code') ? new NullAttributeValue((int) $request->getData('account_code')) : $new->supplierCode;
        }

        return $new;
    }

    /**
     * Validate TaxCombination update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaxCombinationUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete TaxCombination
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
    public function apiTaxCombinationDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaxCombinationDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Tax\TaxCombination $taxCombination */
        $taxCombination = TaxCombinationMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $taxCombination, TaxCombinationMapper::class, 'tax_combination', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $taxCombination);
    }

    /**
     * Validate TaxCombination delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaxCombinationDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
