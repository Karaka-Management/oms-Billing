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
use Modules\Billing\Models\Price\NullPrice;
use Modules\Billing\Models\Price\Price;
use Modules\Billing\Models\Price\PriceMapper;
use Modules\Billing\Models\Price\PriceType;
use Modules\Billing\Models\Tax\TaxCombinationMapper;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ClientManagement\Models\NullClient;
use Modules\Finance\Models\TaxCodeMapper;
use Modules\ItemManagement\Models\Item;
use Modules\ItemManagement\Models\ItemMapper;
use Modules\ItemManagement\Models\NullItem;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
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
    public function findBestPrice(RequestAbstract $request, ?Item $item = null, ?Client $client = null, ?Supplier $supplier = null)
    {
        // Get item
        if ($item === null && $request->hasData('price_item')) {
            /** @var null|\Modules\ItemManagement\Models\Item $item */
            $item = ItemMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('price_item'))
                ->where('attributes/type/name', ['segment', 'section', 'sales_group', 'product_group', 'product_type'], 'IN')
                ->execute();
        }

        // Get client
        if ($client === null && $request->hasData('client')) {
            /** @var \Modules\ClientManagement\Models\Client $client */
            $client = ClientMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('client'))
                ->where('attributes/type/name', ['segment', 'section', 'client_group', 'client_type'], 'IN')
                ->execute();
        }

        $quantity = new FloatInt($request->getDataString('price_quantity') ?? 10000);

        // Get all relevant prices
        $queryMapper = PriceMapper::getAll();

        if ($request->hasData('price_name')) {
            $queryMapper->where('name', $request->getData('name'));
        }

        $queryMapper->where('promocode', \array_unique([$request->getData('promocode'), null]), 'IN');

        $queryMapper->where('item', \array_unique([$request->getDataInt('item'), $item?->id, null]), 'IN');
        $queryMapper->where('itemsalesgroup', \array_unique([$request->getDataInt('sales_group'), $item?->getAttribute('sales_group')->value->getValue(), null]), 'IN');
        $queryMapper->where('itemproductgroup', \array_unique([$request->getDataInt('product_group'), $item?->getAttribute('product_group')->value->getValue(), null]), 'IN');
        $queryMapper->where('itemsegment', \array_unique([$request->getDataInt('item_segment'), $item?->getAttribute('segment')->value->getValue(), null]), 'IN');
        $queryMapper->where('itemsection', \array_unique([$request->getDataInt('item_section'), $item?->getAttribute('section')->value->getValue(), null]), 'IN');
        $queryMapper->where('itemtype', \array_unique([$request->getDataInt('product_type'), $item?->getAttribute('product_type')->value->getValue(), null]), 'IN');

        $queryMapper->where('client', \array_unique([$request->getDataInt('client'), $client?->id, null]), 'IN');
        $queryMapper->where('clientgroup', \array_unique([$request->getDataInt('client_group'), $client?->getAttribute('client_group')->value->getValue(), null]), 'IN');
        $queryMapper->where('clientsegment', \array_unique([$request->getDataInt('client_segment'), $client?->getAttribute('segment')->value->getValue(), null]), 'IN');
        $queryMapper->where('clientsection', \array_unique([$request->getDataInt('client_section'), $client?->getAttribute('section')->value->getValue(), null]), 'IN');
        $queryMapper->where('clienttype', \array_unique([$request->getDataInt('client_type'), $client?->getAttribute('client_type')->value->getValue(), null]), 'IN');
        $queryMapper->where('clientcountry', \array_unique([$request->getData('client_region'), $client?->mainAddress->country, null]), 'IN');

        $queryMapper->where('supplier', \array_unique([$request->getDataInt('supplier'), $supplier?->id, null]), 'IN');
        $queryMapper->where('unit', \array_unique([$request->getDataInt('price_unit'), null]), 'IN');
        $queryMapper->where('type', $request->getDataInt('price_type') ?? PriceType::SALES);
        $queryMapper->where('currency', \array_unique([$request->getDataString('currency'), null]), 'IN');

        // @todo implement start and end

        /*
        @todo implement quantity
        if ($request->hasData('price_quantity')) {
            $whereQuery = new Where();
            $whereQuery->where('quantity', (int) $request->getData('price_quantity'), '<=')
                ->where('quantity', null, '=', 'OR')

            $queryMapper->where('quantity', $whereQuery);
        }
        */

        /** @var \Modules\Billing\Models\Price\Price[] $prices */
        $prices = $queryMapper->execute();

        // Find base price (@todo probably not a good solution)
        $basePrice = null;
        foreach ($prices as $price) {
            if ($price->priceNew > 0
                && $price->item->id !== 0
                && $price->itemsalesgroup->id === 0
                && $price->itemproductgroup->id === 0
                && $price->itemsegment->id === 0
                && $price->itemsection->id === 0
                && $price->itemtype->id === 0
                && $price->client->id === 0
                && $price->clientgroup->id === 0
                && $price->clientsegment->id === 0
                && $price->clientsection->id === 0
                && $price->clienttype->id === 0
                && $price->promocode === ''
                && $price->priceNew->value < ($basePrice?->priceNew->value ?? \PHP_INT_MAX)
            ) {
                $basePrice = $price;
            }
        }

        $basePrice ??= new NullPrice();

        // @todo implement prices which cannot be improved even if there are better prices available (i.e. some customer groups may not get better prices, Dentagen Beispiel)
        // alternatively set prices as 'improvable' => which whitelists a price as can be improved or 'alwaysimproces' which always overwrites other prices
        // Find best price
        $bestPrice      = $basePrice;
        $bestPriceValue = \PHP_INT_MAX;

        $discounts = [];

        foreach ($prices as $price) {
            if ($price->isAdditive && $price->priceNew->value === 0) {
                $discounts[] = $price;
            }

            $newPrice = $bestPrice->price->value ?? $basePrice->price->value;

            if ($price->priceNew->value > 0 && $price->priceNew->value < $newPrice) {
                $newPrice = $price->priceNew->value;
            }

            if ($price->priceNew->value > 0 && $price->priceNew->value < $newPrice) {
                $newPrice = $price->priceNew->value;
            }

            // Calculate the price EFFECT (this is the theoretical unit price)
            // 1. subtract discount value
            // 2. subtract discount percentage
            // 3. subtract bonus effect

            $newPrice -= $price->discount->value;
            $newPrice = (int) ($newPrice - $price->bonus->value / 10000 * $price->priceNew->value / $quantity->value);
            $newPrice = (int) ((1000000 - $price->discountPercentage->value) / 1000000 * $newPrice);

            // @todo If a customer receives 1+1 but purchases 2, then he gets 2+2 (if multiply === true) which is better than 1+1 with multiply false.
            // Same goes for amount discounts?

            if ($newPrice < $bestPriceValue) {
                $bestPriceValue = $newPrice;
                $bestPrice      = $price;
            }
        }

        if ($bestPrice->price->value === 0) {
            $discounts[] = clone $bestPrice;
            $bestPrice   = $basePrice;
        }

        // Actual price calculation
        $bestActualPriceValue = $bestPrice?->price->value ?? \PHP_INT_MAX;

        $discountAmount     = $bestPrice->discount->value;
        $discountPercentage = $bestPrice->discountPercentage->value;
        $bonus              = $bestPrice->bonus->value;

        foreach ($discounts as $discount) {
            $bestActualPriceValue -= $discount->discount->value;

            $discountAmount     += $discount->discount->value;
            $discountPercentage += $discount->discountPercentage->value;
            $bonus              += $discount->bonus->value;
        }

        $bestActualPriceValue -= $discountAmount;
        $bestActualPriceValue = (int) \round((1000000 - $discountPercentage) / 1000000 * $bestActualPriceValue, 0);

        return [
            'basePrice'       => $basePrice->price,
            'bestPrice'       => $bestPrice->price,
            'bestActualPrice' => new FloatInt($bestActualPriceValue),
            'discounts'       => $discounts,
            'discountPercent' => new FloatInt($discountPercentage),
            'discountAmount'  => new FloatInt($discountAmount),
            'bonus'           => new FloatInt($bonus),
        ];
    }

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
        if ($request->hasData('client')) {
            /** @var \Modules\ClientManagement\Models\Client $client */
            $client = ClientMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('client'))
                ->execute();

            /** @var \Modules\ClientManagement\Models\Client */
            $account = $client;
        } else {
            /** @var \Modules\SupplierManagement\Models\Supplier $supplier */
            $supplier = SupplierMapper::get()
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('supplier'))
                ->execute();

            /** @var \Modules\SupplierManagement\Models\Supplier $account */
            $account = $supplier;
        }

        $quantity = new FloatInt($request->getDataString('price_quantity') ?? 10000);

        // Get all relevant prices
        $queryMapper = PriceMapper::getAll();

        if ($request->hasData('price_name')) {
            $queryMapper->where('name', $request->getData('name'));
        }

        $queryMapper->where('promocode', \array_unique([$request->getData('promocode'), null]), 'IN');

        $queryMapper->where('item', \array_unique([$request->getData('item', 'int'), null]), 'IN');
        $queryMapper->where('itemsalesgroup', \array_unique([$request->getData('sales_group', 'int'), $item?->getAttribute('sales_group')->id, null]), 'IN');
        $queryMapper->where('itemproductgroup', \array_unique([$request->getData('product_group', 'int'), $item?->getAttribute('product_group')->id, null]), 'IN');
        $queryMapper->where('itemsegment', \array_unique([$request->getData('item_segment', 'int'), $item?->getAttribute('segment')->id, null]), 'IN');
        $queryMapper->where('itemsection', \array_unique([$request->getData('item_section', 'int'), $item?->getAttribute('section')->id, null]), 'IN');
        $queryMapper->where('itemtype', \array_unique([$request->getData('product_type', 'int'), $item?->getAttribute('product_type')->id, null]), 'IN');

        $queryMapper->where('client', \array_unique([$request->getData('client', 'int'), null]), 'IN');
        $queryMapper->where('clientgroup', \array_unique([$request->getData('client_group', 'int'), $client?->getAttribute('client_group')->id, null]), 'IN');
        $queryMapper->where('clientsegment', \array_unique([$request->getData('client_segment', 'int'), $client?->getAttribute('segment')->id, null]), 'IN');
        $queryMapper->where('clientsection', \array_unique([$request->getData('client_section', 'int'), $client?->getAttribute('section')->id, null]), 'IN');
        $queryMapper->where('clienttype', \array_unique([$request->getData('client_type', 'int'), $client?->getAttribute('client_type')->id, null]), 'IN');
        $queryMapper->where('clientcountry', \array_unique([$request->getData('client_region'), $client?->mainAddress->country, null]), 'IN');

        $queryMapper->where('supplier', \array_unique([$request->getData('supplier', 'int'), null]), 'IN');
        $queryMapper->where('unit', \array_unique([$request->getData('price_unit', 'int'), null]), 'IN');
        $queryMapper->where('type', $request->getData('price_type', 'int') ?? PriceType::SALES);
        $queryMapper->where('currency', \array_unique([$request->getData('currency', 'int'), null]), 'IN');

        // @todo implement start and end

        /*
        @todo implement quantity
        if ($request->hasData('price_quantity')) {
            $whereQuery = new Where();
            $whereQuery->where('quantity', (int) $request->getData('price_quantity'), '<=')
                ->where('quantity', null, '=', 'OR')

            $queryMapper->where('quantity', $whereQuery);
        }
        */

        /** @var \Modules\Billing\Models\Price\Price[] $prices */
        $prices = $queryMapper->execute();

        // Find base price (@todo probably not a good solution)
        $bestBasePrice = null;
        foreach ($prices as $price) {
            if ($price->priceNew->value !== 0 && $price->priceNew === 0
                && $price->item->id !== 0
                && $price->itemsalesgroup->id === 0
                && $price->itemproductgroup->id === 0
                && $price->itemsegment->id === 0
                && $price->itemsection->id === 0
                && $price->itemtype->id === 0
                && $price->client->id === 0
                && $price->clientgroup->id === 0
                && $price->clientsegment->id === 0
                && $price->clientsection->id === 0
                && $price->clienttype->id === 0
                && $price->promocode === ''
                && $price->priceNew->value < ($bestBasePrice?->price->value ?? \PHP_INT_MAX)
            ) {
                $bestBasePrice = $price;
            }
        }

        // @todo implement prices which cannot be improved even if there are better prices available (i.e. some customer groups may not get better prices, Dentagen Beispiel)
        // alternatively set prices as 'improvable' => which whitelists a price as can be improved or 'alwaysimproces' which always overwrites other prices
        // Find best price
        $bestPrice      = null;
        $bestPriceValue = \PHP_INT_MAX;

        foreach ($prices as $price) {
            $newPrice = $bestBasePrice?->price->value ?? \PHP_INT_MAX;

            if ($price->priceNew->value < $newPrice) {
                $newPrice = $price->priceNew->value;
            }

            // Calculate the price EFFECT (this is the theoretical unit price)
            // 1. subtract discount value
            // 2. subtract discount percentage
            // 3. subtract bonus effect

            $newPrice -= $price->discount->value;
            $newPrice = (int) ((1000000 - $price->discountPercentage->value) / 1000000 * $newPrice);
            $newPrice = (int) ($newPrice - $price->bonus->value / 10000 * $price->priceNew->value / $quantity->value);

            // @todo If a customer receives 1+1 but purchases 2, then he gets 2+2 (if multiply === true) which is better than 1+1 with multiply false.
            // Same goes for amount discounts?

            if ($newPrice < $bestPriceValue) {
                $bestPriceValue = $newPrice;
                $bestPrice      = $price;
            }
        }

        // Actual price calculation
        $bestActualPrice = $bestBasePrice?->price->value ?? \PHP_INT_MAX;
        $bestActualPrice -= $bestPrice->discount->value;

        // @todo now perform subtractive improvements (e.g. promocodes are often subtractive)

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

        $taxCode = TaxCodeMapper::get()
            ->where('abbr', $tax->taxCode)
            ->execute();

        $result = [
            'taxcode'         => $taxCode->abbr,
            'grossPercentage' => $taxCode->percentageInvoice,
            'net'             => $bestActualPrice,
            'taxes'           => $bestActualPrice * $taxCode->percentageInvoice / 1000000,
            'gross'           => $bestActualPrice + $bestActualPrice * $taxCode->percentageInvoice / 1000000,
        ];

        $response->header->set('Content-Type', MimeType::M_JSON, true);
        $response->set(
            $request->uri->__toString(),
            $result
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

        $price->item             = new NullItem((int) $request->getData('item'));
        $price->itemsegment      = new NullAttributeValue((int) $request->getData('itemsegment'));
        $price->itemsection      = new NullAttributeValue((int) $request->getData('itemsection'));
        $price->itemsalesgroup   = new NullAttributeValue((int) $request->getData('itemsalesgroup'));
        $price->itemproductgroup = new NullAttributeValue((int) $request->getData('itemproductgroup'));
        $price->itemtype         = new NullAttributeValue((int) $request->getData('itemtype'));

        $price->client        = new NullClient((int) $request->getData('client'));
        $price->clientsegment = new NullAttributeValue((int) $request->getData('clientsegment'));
        $price->clientsection = new NullAttributeValue((int) $request->getData('clientsection'));
        $price->clientgroup   = new NullAttributeValue((int) $request->getData('clientgroup'));
        $price->clienttype    = new NullAttributeValue((int) $request->getData('clienttype'));

        $price->supplier           = new NullSupplier((int) $request->getData('supplier'));
        $price->unit               = (int) $request->getData('unit');
        $price->type               = PriceType::tryFromValue($request->getDataInt('type')) ?? PriceType::SALES;
        $price->quantity           = new FloatInt($request->getDataString('quantity') ?? 0);
        $price->price              = new FloatInt($request->getDataString('price') ?? 0);
        $price->priceNew           = new FloatInt($request->getDataString('price_new') ?? 0);
        $price->discount           = new FloatInt($request->getDataString('discount') ?? 0);
        $price->discountPercentage = new FloatInt($request->getDataString('discountPercentage') ?? 0);
        $price->bonus              = new FloatInt($request->getDataString('bonus') ?? 0);
        $price->multiply           = $request->getDataBool('multiply') ?? false;
        $price->currency           = ISO4217CharEnum::tryFromValue($request->getDataString('currency')) ?? ISO4217CharEnum::_EUR;
        $price->start              = $request->getDataDateTime('start');
        $price->end                = $request->getDataDateTime('end');

        return $price;
    }

    /**
     * Validate item attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @todo consider to prevent name 'default'?
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
        $new->name = $new->name !== 'default'
            ? ($request->getDataString('name') ?? $new->name)
            : $new->name;

        $new->promocode = $request->getDataString('promocode') ?? $new->promocode;

        $new->item             = $request->hasData('item') ? new NullItem((int) $request->getData('item')) : $new->item;
        $new->itemsalesgroup   = $request->hasData('itemsalesgroup') ? new NullAttributeValue((int) $request->getData('itemsalesgroup')) : $new->itemsalesgroup;
        $new->itemproductgroup = $request->hasData('itemproductgroup') ? new NullAttributeValue((int) $request->getData('itemproductgroup')) : $new->itemproductgroup;
        $new->itemsegment      = $request->hasData('itemsegment') ? new NullAttributeValue((int) $request->getData('itemsegment')) : $new->itemsegment;
        $new->itemsection      = $request->hasData('itemsection') ? new NullAttributeValue((int) $request->getData('itemsection')) : $new->itemsection;
        $new->itemtype         = $request->hasData('itemtype') ? new NullAttributeValue((int) $request->getData('itemtype')) : $new->itemtype;

        $new->client        = $request->hasData('client') ? new NullClient((int) $request->getData('client')) : $new->client;
        $new->clientgroup   = $request->hasData('clientgroup') ? new NullAttributeValue((int) $request->getData('clientgroup')) : $new->clientgroup;
        $new->clientsegment = $request->hasData('clientsegment') ? new NullAttributeValue((int) $request->getData('clientsegment')) : $new->clientsegment;
        $new->clientsection = $request->hasData('clientsection') ? new NullAttributeValue((int) $request->getData('clientsection')) : $new->clientsection;
        $new->clienttype    = $request->hasData('clienttype') ? new NullAttributeValue((int) $request->getData('clienttype')) : $new->clienttype;

        $new->supplier           = $request->hasData('supplier') ? new NullSupplier((int) $request->getData('supplier')) : $new->supplier;
        $new->unit               = $request->getDataInt('unit') ?? $new->unit;
        $new->type               = PriceType::tryFromValue($request->getDataInt('type')) ?? $new->type;
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
     * @todo implement
     * @todo consider to block 'default' name
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

        if ($price->name === 'default') {
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
