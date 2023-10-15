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

use Modules\Attribute\Models\NullAttributeValue;
use Modules\Billing\Models\Price\Price;
use Modules\Billing\Models\Price\PriceMapper;
use Modules\Billing\Models\Price\PriceType;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ClientManagement\Models\NullClient;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\NullItem;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\System\MimeType;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
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
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiPricingFind(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // Get item
        /** @var null|\Modules\ItemManagement\Models\Item $item */
        $item = null;
        if ($request->hasData('price_item')) {
            /** @var null|\Modules\ItemManagement\Models\Item $item */
            $item = ItemMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_item'))
                ->execute();
        }

        // Get account
        /** @var null|\Modules\ClientManagement\Models\Client|\Modules\SupplierManagement\Models\Supplier $account */
        $account = null;

        /** @var null|\Modules\ClientManagement\Models\Client $client */
        $client = null;

        /** @var null|\Modules\SupplierManagement\Models\Supplier $supplier */
        $supplier = null;

        if ($request->hasData('price_client')) {
            /** @var \Modules\ClientManagement\Models\Client $client */
            $client = ClientMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_client'))
                ->execute();

            /** @var \Modules\ClientManagement\Models\Client */
            $account = $client;
        } else {
            /** @var \Modules\SupplierManagement\Models\Supplier $supplier */
            $supplier = SupplierMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_supplier'))
                ->execute();

            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = $supplier;
        }

        // Get all relevant prices
        $queryMapper = PriceMapper::getAll();

        if ($request->hasData('price_name')) {
            $queryMapper->where('name', $request->getData('price_name'));
        }

        $queryMapper->where('promocode', \array_unique([$request->getData('price_promocode'), null]), 'IN');

        $queryMapper->where('item', \array_unique([$request->getData('price_item', 'int'), null]), 'IN');
        $queryMapper->where('itemgroup', \array_unique([$request->getData('price_itemgroup', 'int'), $item?->getAttribute('itemgroup')->id, null]), 'IN');
        $queryMapper->where('itemsegment', \array_unique([$request->getData('price_itemsegment', 'int'), $item?->getAttribute('itemsegment')->id, null]), 'IN');
        $queryMapper->where('itemsection', \array_unique([$request->getData('price_itemsection', 'int'), $item?->getAttribute('itemsection')->id, null]), 'IN');
        $queryMapper->where('itemtype', \array_unique([$request->getData('price_itemtype', 'int'), $item?->getAttribute('itemtype')->id, null]), 'IN');

        $queryMapper->where('client', \array_unique([$request->getData('price_client', 'int'), null]), 'IN');
        $queryMapper->where('clientgroup', \array_unique([$request->getData('price_clientgroup', 'int'), $client?->getAttribute('clientgroup')->id, null]), 'IN');
        $queryMapper->where('clientsegment', \array_unique([$request->getData('price_clientsegment', 'int'), $client?->getAttribute('clientsegment')->id, null]), 'IN');
        $queryMapper->where('clientsection', \array_unique([$request->getData('price_clientsection', 'int'), $client?->getAttribute('clientsection')->id, null]), 'IN');
        $queryMapper->where('clienttype', \array_unique([$request->getData('price_clienttype', 'int'), $client?->getAttribute('clienttype')->id, null]), 'IN');
        $queryMapper->where('clientcountry', \array_unique([$request->getData('price_clientcountry'), $client?->mainAddress->getCountry(), null]), 'IN');

        $queryMapper->where('supplier', \array_unique([$request->getData('price_supplier', 'int'), null]), 'IN');
        $queryMapper->where('unit', \array_unique([$request->getData('price_unit', 'int'), null]), 'IN');
        $queryMapper->where('type', $request->getData('price_type', 'int') ?? PriceType::SALES);
        $queryMapper->where('currency', \array_unique([$request->getData('price_currency', 'int'), null]), 'IN');

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

        /** @var \Modules\Billing\Models\Price\Price[] $prices */
        $prices = $queryMapper->execute();

        // Find base price (@todo: probably not a good solution)
        $bestBasePrice = null;
        foreach ($prices as $price) {
            if ($price->price->value !== 0 && $price->priceNew === 0
                && $price->item->id !== 0
                && $price->itemgroup->id === 0
                && $price->itemsegment->id === 0
                && $price->itemsection->id === 0
                && $price->itemtype->id === 0
                && $price->client->id === 0
                && $price->clientgroup->id === 0
                && $price->clientsegment->id === 0
                && $price->clientsection->id === 0
                && $price->clienttype->id === 0
                && $price->promocode === ''
                && $price->price->value < ($bestBasePrice?->price->value ?? \PHP_INT_MAX)
            ) {
                $bestBasePrice = $price;
            }
        }

        // @todo: implement prices which cannot be improved even if there are better prices available (i.e. some customer groups may not get better prices, Dentagen Beispiel)
        // alternatively set prices as 'improvable' => which whitelists a price as can be improved or 'alwaysimproces' which always overwrites other prices
        // Find best price
        $bestPrice      = null;
        $bestPriceValue = \PHP_INT_MAX;

        foreach ($prices as $price) {
            $newPrice = $bestBasePrice?->price->value ?? \PHP_INT_MAX;

            if ($price->price->value < $newPrice) {
                $newPrice = $price->price->value;
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
        /** @var \Modules\Billing\Models\Tax\TaxCombination $tax */
        $tax = ($request->getDataInt('price_type') ?? PriceType::SALES) === PriceType::SALES
            ? TaxCombinationMapper::get()
                ->where('itemCode', $request->getDataInt('price_item'))
                ->where('clientCode', $account->getAttribute('client_code')->value->id)
                ->execute()
            : TaxCombinationMapper::get()
                ->where('itemCode', $request->getDataInt('price_item'))
                ->where('supplierCode', $account->getAttribute('supplier_code')->value->id)
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
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiPriceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validatePriceCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $tax = $this->createPriceFromRequest($request);
        $this->createModel($request->header->account, $tax, PriceMapper::class, 'price', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $tax);
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
        $price            = new Price();
        $price->name      = $request->getDataString('name') ?? '';
        $price->promocode = $request->getDataString('promocode') ?? '';

        $price->item        = new NullItem((int) $request->getData('item'));
        $price->itemgroup   = new NullAttributeValue((int) $request->getData('itemgroup'));
        $price->itemsegment = new NullAttributeValue((int) $request->getData('itemsegment'));
        $price->itemsection = new NullAttributeValue((int) $request->getData('itemsection'));
        $price->itemtype    = new NullAttributeValue((int) $request->getData('itemtype'));

        $price->client        = new NullClient((int) $request->getData('client'));
        $price->clientgroup   = new NullAttributeValue((int) $request->getData('clientgroup'));
        $price->clientsegment = new NullAttributeValue((int) $request->getData('clientsegment'));
        $price->clientsection = new NullAttributeValue((int) $request->getData('clientsection'));
        $price->clienttype    = new NullAttributeValue((int) $request->getData('clienttype'));

        $price->supplier           = new NullSupplier((int) $request->getData('supplier'));
        $price->unit               = (int) $request->getData('unit');
        $price->type               = $request->getDataInt('type') ?? PriceType::SALES;
        $price->quantity           = (int) $request->getData('quantity');
        $price->price              = new FloatInt((int) $request->getData('price'));
        $price->priceNew           = (int) $request->getData('price_new');
        $price->discount           = (int) $request->getData('discount');
        $price->discountPercentage = (int) $request->getData('discountPercentage');
        $price->bonus              = (int) $request->getData('bonus');
        $price->multiply           = $request->getDataBool('multiply') ?? false;
        $price->currency           = $request->getDataString('currency') ?? ISO4217CharEnum::_EUR;
        $price->start              = $request->getDataDateTime('start') ?? null;
        $price->end                = $request->getDataDateTime('end') ?? null;

        return $price;
    }

    /**
     * Validate item attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: consider to prevent name 'base'?
     * Might not be possible because it is used internally as well (see apiItemCreate in ItemManagement)
     *
     * @since 1.0.0
     */
    private function validatePriceCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = !$request->hasData('name'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to update Price
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
    public function apiPriceUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validatePriceUpdate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Price\Price $old */
        $old = PriceMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updatePriceFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, PriceMapper::class, 'price', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update Price from request.
     *
     * @param RequestAbstract $request Request
     * @param Price           $new     Model to modify
     *
     * @return Price
     *
     * @since 1.0.0
     */
    public function updatePriceFromRequest(RequestAbstract $request, Price $new) : Price
    {
        $new->name = $new->name !== 'base'
            ? ($request->getDataString('name') ?? $new->name)
            : $new->name;

        $new->promocode = $request->getDataString('promocode') ?? $new->promocode;

        $new->item        = $request->hasData('item') ? new NullItem((int) $request->getData('item')) : $new->item;
        $new->itemgroup   = $request->hasData('itemgroup') ? new NullAttributeValue((int) $request->getData('itemgroup')) : $new->itemgroup;
        $new->itemsegment = $request->hasData('itemsegment') ? new NullAttributeValue((int) $request->getData('itemsegment')) : $new->itemsegment;
        $new->itemsection = $request->hasData('itemsection') ? new NullAttributeValue((int) $request->getData('itemsection')) : $new->itemsection;
        $new->itemtype    = $request->hasData('itemtype') ? new NullAttributeValue((int) $request->getData('itemtype')) : $new->itemtype;

        $new->client        = $request->hasData('client') ? new NullClient((int) $request->getData('client')) : $new->client;
        $new->clientgroup   = $request->hasData('clientgroup') ? new NullAttributeValue((int) $request->getData('clientgroup')) : $new->clientgroup;
        $new->clientsegment = $request->hasData('clientsegment') ? new NullAttributeValue((int) $request->getData('clientsegment')) : $new->clientsegment;
        $new->clientsection = $request->hasData('clientsection') ? new NullAttributeValue((int) $request->getData('clientsection')) : $new->clientsection;
        $new->clienttype    = $request->hasData('clienttype') ? new NullAttributeValue((int) $request->getData('clienttype')) : $new->clienttype;

        $new->supplier           = $request->hasData('supplier') ? new NullSupplier((int) $request->getData('supplier')) : $new->supplier;
        $new->unit               = $request->getDataInt('unit') ?? $new->unit;
        $new->type               = $request->getDataInt('type') ?? $new->type;
        $new->quantity           = $request->getDataInt('quantity') ?? $new->quantity;
        $new->price              = $request->hasData('price') ? new FloatInt((int) $request->getData('price')) : $new->price;
        $new->priceNew           = $request->getDataInt('price_new') ?? $new->priceNew;
        $new->discount           = $request->getDataInt('discount') ?? $new->discount;
        $new->discountPercentage = $request->getDataInt('discountPercentage') ?? $new->discountPercentage;
        $new->bonus              = $request->getDataInt('bonus') ?? $new->bonus;
        $new->multiply           = $request->getDataBool('multiply') ?? $new->multiply;
        $new->currency           = $request->getDataString('currency') ?? $new->currency;
        $new->start              = $request->getDataDateTime('start') ?? $new->start;
        $new->end                = $request->getDataDateTime('end') ?? $new->end;

        return $new;
    }

    /**
     * Validate Price update request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo: implement
     * @todo: consider to block 'base' name
     *
     * @since 1.0.0
     */
    private function validatePriceUpdate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to delete Price
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
    public function apiPriceDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validatePriceDelete($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidDeleteResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Billing\Models\Price\Price $price */
        $price = PriceMapper::get()->where('id', (int) $request->getData('id'))->execute();

        if ($price->name === 'base') {
            // default price cannot be deleted
            $this->createInvalidDeleteResponse($request, $response, []);

            return;
        }

        $this->deleteModel($request->header->account, $price, PriceMapper::class, 'price', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $price);
    }

    /**
     * Validate Price delete request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validatePriceDelete(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }
}
