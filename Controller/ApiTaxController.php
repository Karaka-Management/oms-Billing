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

use Modules\Admin\Models\Address;
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
use phpOMS\Localization\ISO3166CharEnum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Security\Guard;

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
     * @param Client $client         Client to get tax code from
     * @param Item   $item           Item toget tax code from
     * @param string $defaultCountry default country to use if no valid tax code could be found and if the unit country code shouldn't be used
     *
     * @return TaxCode
     *
     * @since 1.0.0
     */
    public function getTaxCodeFromClientItem(Client $client, Item $item, string $defaultCountry = '') : TaxCode
    {
        // @todo: define default sales tax code if none available?!
        // @todo: consider to actually use a ownsOne reference instead of only a string, this way the next line with the TaxCodeMapper can be removed

        /** @var \Modules\Billing\Models\Tax\TaxCombination $taxCombination */
        $taxCombination = TaxCombinationMapper::get()
            ->where('itemCode', $item->getAttribute('sales_tax_code')->value->id)
            ->where('clientCode', $client->getAttribute('sales_tax_code')->value->id)
            ->execute();

        /** @var \Modules\Finance\Models\TaxCode $taxCode */
        $taxCode = TaxCodeMapper::get()
            ->where('abbr', $taxCombination->taxCode)
            ->execute();

        if ($taxCode->id !== 0) {
            return $taxCode;
        }

        /** @var \Modules\Organization\Models\Unit $unit */
        $unit = UnitMapper::get()
            ->with('mainAddress')
            ->where('id', $this->app->unitId)
            ->execute();

        // Create dummy client
        $client              = new Client();
        $client->mainAddress =  $unit->mainAddress;

        if (!empty($defaultCountry)) {
            $client->mainAddress->setCountry($defaultCountry);
        }

        $taxCodeAttribute = $this->getClientTaxCode($client,  $unit->mainAddress);

        /** @var \Modules\Billing\Models\Tax\TaxCombination $taxCombination */
        $taxCombination = TaxCombinationMapper::get()
            ->where('itemCode', $item->getAttribute('sales_tax_code')->value->id)
            ->where('clientCode', $taxCodeAttribute->id)
            ->execute();

        /** @var \Modules\Finance\Models\TaxCode $taxCode */
        $taxCode = TaxCodeMapper::get()
            ->where('abbr', $taxCombination->taxCode)
            ->execute();

        return $taxCode;
    }

    /**
     * Create a tax combination for a client and item
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function apiTaxCombinationCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
        $tax           = new TaxCombination();
        $tax->taxType  = $request->getDataInt('tax_type') ?? 1;
        $tax->taxCode  = (string) $request->getData('tax_code');
        $tax->itemCode = new NullAttributeValue((int) $request->getData('item_code'));

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

        // @todo: need to consider own tax id as well
        if ($taxOfficeAddress->getCountry() === $client->mainAddress->getCountry()) {
            $taxCode = $codes->getDefaultByValue($client->mainAddress->getCountry());
        } elseif (\in_array($taxOfficeAddress->getCountry(), ISO3166CharEnum::getRegion('eu'))
            && \in_array($client->mainAddress->getCountry(), ISO3166CharEnum::getRegion('eu'))
        ) {
            if (!empty($client->getAttribute('vat_id')->value->getValue())) {
                // Is EU company
                $taxCode = $codes->getDefaultByValue('EU');
            } else {
                // Is EU private customer
                $taxCode = $codes->getDefaultByValue($client->mainAddress->getCountry());
            }
        } elseif (\in_array($taxOfficeAddress->getCountry(), ISO3166CharEnum::getRegion('eu'))) {
            // None EU company but we are EU company
            $taxCode = $codes->getDefaultByValue('INT');
        } else {
            // None EU company and we are also none EU company
            $taxCode = $codes->getDefaultByValue('INT');
        }

        return $taxCode;
    }

    /**
     * Api method to update TaxCombination
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
    public function apiTaxCombinationUpdate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiChangeDefaultTaxCombinations(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
            return;
        }

        /** @var array $combinations */
        $combinations = \json_decode(\file_get_contents($path), true);

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
            $new->taxCode = $combination['tax_code'] ?? '';

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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaxCombinationDelete(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @todo: implement
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
