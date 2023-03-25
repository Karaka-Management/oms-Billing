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
use Modules\Billing\Models\Tax\TaxCombination;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientAttributeTypeMapper;
use Modules\ClientManagement\Models\ClientAttributeValue;
use Modules\ClientManagement\Models\NullClientAttributeValue;
use Modules\ItemManagement\Models\NullItemAttributeValue;
use Modules\SupplierManagement\Models\NullSupplierAttributeValue;
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
    public function apiTaxCombinationCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaxCombinationCreate($request))) {
            $response->set('tax_combination_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

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
        $tax->itemCode = new NullItemAttributeValue((int) $request->getData('item_code'));

        if ($tax->taxType === 1) {
            $tax->clientCode = new NullClientAttributeValue((int) $request->getData('account_code'));
        } else {
            $tax->supplierCode = new NullSupplierAttributeValue((int) $request->getData('account_code'));
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
        if (($val['tax_type'] = empty($request->getData('tax_type')))
            || ($val['tax_code'] = empty($request->getData('tax_code')))
            || ($val['item_code'] = empty($request->getData('item_code')))
            || ($val['account_code'] = empty($request->getData('account_code')))
        ) {
            return $val;
        }

        return [];
    }

    public function getClientTaxCode(Client $client, Address $taxOfficeAddress) : ClientAttributeValue
    {
        /** @var \Modules\ClientManagement\Models\ClientAttributeType $codes */
        $codes = ClientAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'sales_tax_code')
            ->execute();

        $taxCode = new NullClientAttributeValue();

        if ($taxOfficeAddress->getCountry() === $client->mainAddress->getCountry()) {
            $taxCode = $codes->getDefaultByValue($client->mainAddress->getCountry());
        } elseif (\in_array($taxOfficeAddress->getCountry(), ISO3166CharEnum::getRegion('eu'))
            && \in_array($client->mainAddress->getCountry(), ISO3166CharEnum::getRegion('eu'))
        ) {
            if (!empty($client->getAttribute('vat_id')?->value->getValue())) {
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
