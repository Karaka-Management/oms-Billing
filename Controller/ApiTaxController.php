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

use Modules\Admin\Models\Address;
use Modules\Attribute\Models\AttributeValue;
use Modules\Attribute\Models\NullAttributeValue;
use Modules\Billing\Models\Tax\TaxCombination;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientAttributeTypeMapper;
use Modules\Finance\Models\TaxCode;
use Modules\Finance\Models\TaxCodeMapper;
use Modules\ItemManagement\Models\Item;
use Modules\Organization\Models\UnitMapper;
use phpOMS\Localization\ISO3166CharEnum;
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

        // If now tax code could be found, the local tax code should be used.
        if ($taxCode->id === 0) {
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
        }

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
            $response->data['tax_combination_create'] = new FormValidation($val);
            $response->header->status                 = RequestStatusCode::R_400;

            return;
        }

        $tax = $this->createTaxCombinationFromRequest($request);
        $this->createModel($request->header->account, $tax, TaxCombinationMapper::class, 'tax_combination', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Tax combination', 'Tax combination successfully created', $tax);
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
}
