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

use Modules\Billing\Models\Price\Price;
use Modules\Billing\Models\Price\PriceMapper;
use Modules\Billing\Models\Price\PriceType;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ClientManagement\Models\NullClient;
use Modules\ClientManagement\Models\NullClientAttributeValue;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\NullItem;
use Modules\ItemManagement\Models\NullItemAttributeValue;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\System\MimeType;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiPriceController extends Controller
{
    /**
     * Api method to find items
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
    public function apiPricingFind(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        // Get item
        $item = null;
        if ($request->hasData('price_item')) {
            $item = ItemMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_item'))
                ->execute();
        }

        // Get account
        $account = null;

        /** @var null|\Modules\ClientManagement\Models\Client $client */
        $client = null;

        /** @var null|\Modules\SupplierManagement\Models\Supplier $supplier */
        $supplier = null;

        if ($request->hasData('price_client')) {
            $client = ClientMapper::get()
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_client'))
                ->execute();

            $account = $client;
        } else {
            $supplier = SupplierMapper::get()
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_supplier'))
                ->execute();

            $account = $supplier;
        }

        // Get all relevant prices
        // @todo: allow to define NOT IN somehow (e.g. not in France -> simple solution to define export prices etc.)
        $queryMapper = PriceMapper::getAll();

        if ($request->hasData('price_name')) {
            $queryMapper->where('name', $request->getData('price_name'));
        }

        $queryMapper->where('promocode', \array_unique([$request->getData('price_promocode'), null]), 'IN');

        $queryMapper->where('item', \array_unique([$request->getData('price_item', 'int'), null]), 'IN');
        $queryMapper->where('itemgroup', \array_unique([$request->getData('price_itemgroup', 'int'), $item?->getAttribute('itemgroup')?->getId(), null]), 'IN');
        $queryMapper->where('itemsegment', \array_unique([$request->getData('price_itemsegment', 'int'), $item?->getAttribute('itemsegment')?->getId(), null]), 'IN');
        $queryMapper->where('itemsection', \array_unique([$request->getData('price_itemsection', 'int'), $item?->getAttribute('itemsection')?->getId(), null]), 'IN');
        $queryMapper->where('itemtype', \array_unique([$request->getData('price_itemtype', 'int'), $item?->getAttribute('itemtype')?->getId(), null]), 'IN');

        $queryMapper->where('client', \array_unique([$request->getData('price_client', 'int'), null]), 'IN');
        $queryMapper->where('clientgroup', \array_unique([$request->getData('price_clientgroup', 'int'), $client?->getAttribute('clientgroup')?->getId(), null]), 'IN');
        $queryMapper->where('clientsegment', \array_unique([$request->getData('price_clientsegment', 'int'), $client?->getAttribute('clientsegment')?->getId(), null]), 'IN');
        $queryMapper->where('clientsection', \array_unique([$request->getData('price_clientsection', 'int'), $client?->getAttribute('clientsection')?->getId(), null]), 'IN');
        $queryMapper->where('clienttype', \array_unique([$request->getData('price_clienttype', 'int'), $client?->getAttribute('clienttype')?->getId(), null]), 'IN');
        $queryMapper->where('clientcountry', \array_unique([$request->getData('price_clientcountry'), $client?->mainAddress->getCountry(), null]), 'IN');

        $queryMapper->where('supplier', \array_unique([$request->getData('price_supplier', 'int'), null]), 'IN');
        $queryMapper->where('unit', \array_unique([$request->getData('price_unit', 'int'), null]), 'IN');
        $queryMapper->where('type', $request->getData('price_type', 'int') ?? PriceType::SALES);
        $queryMapper->where('currency', array_unique([$request->getData('price_currency', 'int'), null]), 'IN');

        // @todo: implement start and end

        /*
        @todo: implement quantity
        if ($request->hasData('price_quantity')) {
            $whereQuery = new Where();
            $whereQuery->where('quantity', (int) $request->getData('price_quantity'), '<=')
                ->where('quantity', null, '=', 'OR')

            $queryMapper->where('quantity', $whereQuery);
        }
        */

        $prices = $queryMapper->execute();

        // Find base price (@todo: probably not a good solution)
        $bestBasePrice = null;
        foreach ($prices as $price) {
            if ($price->price !== 0 && $price->priceNew === 0
                && $price->item->getId() !== 0
                && $price->itemgroup->getId() === 0
                && $price->itemsegment->getId() === 0
                && $price->itemsection->getId() === 0
                && $price->itemtype->getId() === 0
                && $price->client->getId() === 0
                && $price->clientgroup->getId() === 0
                && $price->clientsegment->getId() === 0
                && $price->clientsection->getId() === 0
                && $price->clienttype->getId() === 0
                && $price->promocode === ''
            ) {
                if ($price->price < $bestBasePrice?->price ?? \PHP_INT_MAX) {
                    $bestBasePrice = $price;
                }
            }
        }

        // @todo: implement prices which cannot be improved even if there are better prices available (i.e. some customer groups may not get better prices, Dentagen Beispiel)
        // alternatively set prices as 'improvable' => which whitelists a price as can be improved or 'alwaysimproces' which always overwrites other prices
        // Find best price
        $bestPrice = null;
        $bestPriceValue = \PHP_INT_MAX;

        foreach ($prices as $price) {
            $newPrice = $bestBasePrice->price;

            if ($price->price < $newPrice) {
                $newPrice = $price->price;
            }

            if ($price->priceNew < $newPrice) {
                $newPrice = $price->priceNew;
            }

            $newPrice -= $price->discount;
            $newPrice  = (int) ((10000 / $price->discountPercentage) * $newPrice);
            $newPrice  = (int) (($price->quantity === 0 ? 10000 : $price->quantity) / (10000 + $price->bonus) * $newPrice);

            // @todo: the calculation above regarding discount and bonus don't consider the purchased quantity.
            // If a customer receives 1+1 but purchases 2, then he gets 2+2 (if multiply === true) which is better than 1+1 with multiply false.

            if ($newPrice < $bestPriceValue) {
                $bestPriceValue = $newPrice;
                $bestPrice      = $price;
            }
        }

        // Get tax definition
        $tax = ($request->getData('price_type', 'int') ?? PriceType::SALES)
            ? TaxCombinationMapper::get()
                ->where('itemCode', $request->getData('price_item'))
                ->where('clientCode', $account->getAttribute('client_code')->getId())
                ->execute()
            : TaxCombinationMapper::get()
                ->where('itemCode', $request->getData('price_item'))
                ->where('supplierCode', $account->getAttribute('supplier_code')->getId())
                ->execute();

        $response->header->set('Content-Type', MimeType::M_JSON, true);
        $response->set(
            $request->uri->__toString(),
            \array_values($prices)
        );
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
        if (!empty($val = $this->validatePriceCreate($request))) {
            $response->set('price_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $tax = $this->createPriceFromRequest($request);
        $this->createModel($request->header->account, $tax, PriceMapper::class, 'price', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Price', 'Price successfully created', $tax);
    }

    /**
     * Method to create item attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return Price
     *
     * @since 1.0.0
     */
    private function createPriceFromRequest(RequestAbstract $request) : Price
    {
        $price = new Price();
        $price->name = $request->getData('name') ?? '';
        $price->promocode = $request->getData('promocode') ?? '';

        $price->item = new NullItem((int) $request->getData('item'));
        $price->itemgroup = new NullItemAttributeValue((int) $request->getData('itemgroup'));
        $price->itemsegment = new NullItemAttributeValue((int) $request->getData('itemsegment'));
        $price->itemsection = new NullItemAttributeValue((int) $request->getData('itemsection'));
        $price->itemtype = new NullItemAttributeValue((int) $request->getData('itemtype'));

        $price->client = new NullClient((int) $request->getData('client'));
        $price->clientgroup = new NullClientAttributeValue((int) $request->getData('clientgroup'));
        $price->clientsegment = new NullClientAttributeValue((int) $request->getData('clientsegment'));
        $price->clientsection = new NullClientAttributeValue((int) $request->getData('clientsection'));
        $price->clienttype = new NullClientAttributeValue((int) $request->getData('clienttype'));

        $price->supplier = new NullSupplier((int) $request->getData('supplier'));
        $price->unit = (int) $request->getData('unit');
        $price->type = (int) ($request->getData('type') ?? PriceType::SALES);
        $price->quantity = (int) $request->getData('quantity');
        $price->price = (int) $request->getData('price');
        $price->priceNew = (int) $request->getData('price_name');
        $price->discount = (int) $request->getData('discount');
        $price->discountPercentage = (int) $request->getData('discountPercentage');
        $price->bonus = (int) $request->getData('bonus');
        $price->multiply = (bool) ($request->getData('multiply') ?? false);
        $price->currency = $request->getData('currency') ?? ISO4217CharEnum::_EUR;
        $price->start = $request->hasData('start') ? new \DateTime($request->getData('start')) : null;
        $price->end = $request->hasData('end') ? new \DateTime($request->getData('end')) : null;

        return $price;
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
    private function validatePriceCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (false) {
            return $val;
        }

        return [];
    }
}
