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
use Modules\Billing\Models\Price\PriceStatus;
use Modules\Billing\Models\Price\PriceType;
use Modules\ClientManagement\Models\Client;
use Modules\ClientManagement\Models\ClientMapper;
use Modules\ClientManagement\Models\NullClient;
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
        $item ??= new NullItem();
        $client ??= new NullClient();
        $supplier ??= new NullSupplier();

        // Get item
        if ($item->id === 0 && $request->hasData('price_item')) {
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
        if ($client->id === 0 && $request->hasData('client')) {
            /** @var \Modules\ClientManagement\Models\Client $client */
            $client = ClientMapper::get()
                ->with('mainAddress')
                ->with('attributes')
                ->with('attributes/type')
                ->with('attributes/value')
                ->where('id', (int) $request->getData('client'))
                ->where('attributes/type/name', ['segment', 'section', 'client_group', 'client_type'], 'IN')
                ->execute();
        }

        // Get supplier
        if ($supplier->id === 0 && $request->hasData('supplier')) {
            $supplier = SupplierMapper::get()
                ->where('id', $request->getDataInt('supplier'))
                ->execute();
        }

        $quantity = new FloatInt($request->getDataString('price_quantity') ?? FloatInt::DIVISOR);
        $quantity->value = $quantity->value === 0 ? FloatInt::DIVISOR : $quantity->value;

        // Get all relevant prices
        $queryMapper = PriceMapper::getAll()
            ->where('status', PriceStatus::ACTIVE);

        if ($request->hasData('price_name')) {
            $queryMapper->where('name', $request->getData('name'));
        }

        $queryMapper->where('promocode', \array_unique([$request->getDataString('promocode') ?? '', '']), 'IN');

        // Item
        if ($item->id !== 0) {
            $queryMapper->where('item', $item->id);
        } elseif ($request->hasData('item')) {
            $queryMapper->where('item', $request->getDataInt('item'));
        }

        // Item segment
        $itemSegment = [null, $request->getDataInt('item_segment')];
        if ($item->getAttribute('segment')->value->id !== 0) {
            $itemSegment[] = $item->getAttribute('segment')->value->getValue();
        }
        $queryMapper->where('itemsegment', \array_unique($itemSegment), 'IN');

        // Item section
        $itemSection = [null, $request->getDataInt('item_section')];
        if ($item->getAttribute('section')->value->id !== 0) {
            $itemSection[] = $item->getAttribute('section')->value->getValue();
        }
        $queryMapper->where('itemsection', \array_unique($itemSection), 'IN');

        // Item sales group
        $itemSalesGroups = [null, $request->getDataInt('sales_group')];
        if ($item->getAttribute('sales_group')->value->id !== 0) {
            $itemSalesGroups[] = $item->getAttribute('sales_group')->value->getValue();
        }
        $queryMapper->where('itemsalesgroup', \array_unique($itemSalesGroups), 'IN');

        // Item product group
        $itemProductGroups = [null, $request->getDataInt('product_group')];
        if ($item->getAttribute('product_group')->value->id !== 0) {
            $itemProductGroups[] = $item->getAttribute('product_group')->value->getValue();
        }
        $queryMapper->where('itemproductgroup', \array_unique($itemProductGroups), 'IN');

        // Item product type
        $itemProductType = [null, $request->getDataInt('product_type')];
        if ($item->getAttribute('product_type')->value->id !== 0) {
            $itemProductType[] = $item->getAttribute('product_type')->value->getValue();
        }
        $queryMapper->where('itemtype', \array_unique($itemProductType), 'IN');

        // Client
        if ($client->id !== 0) {
            $queryMapper->where('client', $client->id);
        } elseif ($request->hasData('client')) {
            $queryMapper->where('client', $request->getDataInt('client'));
        }

        // Client segment
        $clientSegment = [null, $request->getDataInt('client_segment')];
        if ($client->getAttribute('segment')->value->id !== 0) {
            $clientSegment[] = $client->getAttribute('segment')->value->getValue();
        }
        $queryMapper->where('clientsegment', \array_unique($clientSegment), 'IN');

        // Client section
        $clientSection = [null, $request->getDataInt('client_section')];
        if ($client->getAttribute('section')->value->id !== 0) {
            $clientSection[] = $client->getAttribute('section')->value->getValue();
        }
        $queryMapper->where('clientsection', \array_unique($clientSection), 'IN');

        // Client group
        $clientGroup = [null, $request->getDataInt('client_group')];
        if ($client->getAttribute('client_group')->value->id !== 0) {
            $clientGroup[] = $client->getAttribute('client_group')->value->getValue();
        }
        $queryMapper->where('clientgroup', \array_unique($clientGroup), 'IN');

        // Client type
        $clientType = [null, $request->getDataInt('client_type')];
        if ($client->getAttribute('client_type')->value->id !== 0) {
            $clientType[] = $client->getAttribute('client_type')->value->getValue();
        }
        $queryMapper->where('clienttype', \array_unique($clientType), 'IN');

        // Client type
        $clientCountry = [null, $request->getDataInt('client_region')];
        if ($client->mainAddress->id !== 0) {
            $clientCountry[] = $client->mainAddress->country;
        }
        $queryMapper->where('clientcountry', \array_unique($clientCountry), 'IN');

        // Supplier
        if ($supplier->id !== 0) {
            $queryMapper->where('supplier', $supplier->id);
        } elseif ($request->hasData('supplier')) {
            $queryMapper->where('supplier', $request->getDataInt('supplier'));
        }

        if ($request->hasData('price_unit')) {
            $queryMapper->where('unit', $request->getDataInt('price_unit'));
        }

        $queryMapper->where('type', $request->getDataInt('price_type') ?? ($supplier->id === 0 ? PriceType::SALES : PriceType::PURCHASE));

        if ($request->hasData('currency')) {
            $queryMapper->where('currency', $request->getData('currency'));
        }

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

        // Find base price
        $basePrice = null;
        foreach ($prices as $price) {
            if (/*$price->priceNew->value > 0 */ // Price could be 0
                $price->id !== 0
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

        // @todo implement prices which cannot be improved even if there are better prices available
        //      (i.e. some customer groups may not get better prices, Dentagen Beispiel)
        //      alternatively set prices as 'improvable' => which whitelists a price as can be improved
        //      or 'always_improves' which always overwrites other prices

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
            $newPrice = (int) ($newPrice - $price->bonus->value / FloatInt::DIVISOR * $price->priceNew->value / $quantity->value);
            $newPrice = (int) (((FloatInt::DIVISOR * 100) - $price->discountPercentage->value) / (FloatInt::DIVISOR * 100) * $newPrice);

            // @todo If a customer receives 1+1 but purchases 2, then he gets 2+2 (if multiply === true) which is better than 1+1 with multiply false.
            // Same goes for amount discounts?

            if ($newPrice < $bestPriceValue) {
                $bestPriceValue = $newPrice;
                $bestPrice      = $price;
            }
        }

        if ($bestPrice->priceNew->value === 0) {
            $discounts[] = clone $bestPrice;
            $bestPrice   = $basePrice;
        }

        // Actual price calculation
        $bestActualPriceValue = $bestPrice?->priceNew->value ?? \PHP_INT_MAX;

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
        $bestActualPriceValue = (int) \round(((FloatInt::DIVISOR * 100) - $discountPercentage) / (FloatInt::DIVISOR * 100) * $bestActualPriceValue, 0);

        return [
            'basePrice'       => $basePrice->priceNew,
            'bestPrice'       => $bestPrice->priceNew,
            'supplier'        => $bestPrice->supplier->id,
            'bestActualPrice' => new FloatInt($bestActualPriceValue),
            'discounts'       => $discounts,
            'discountPercent' => new FloatInt($discountPercentage),
            'discountAmount'  => new FloatInt($discountAmount),
            'bonus'           => new FloatInt($bonus),
        ];
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

        if ($new->name === 'default'
            && $old->priceNew->value !== $new->priceNew->value
        ) {
            /** @var \Modules\ItemManagement\Models\Item $item */
            $item = ItemMapper::get()->where('id', $new->item)->execute();
            $itemNew = clone $item;

            if ($new->type === PriceType::SALES) {
                $itemNew->salesPrice = $new->priceNew;
            } else {
                $itemNew->purchasePrice = $new->priceNew;
            }

            $this->updateModel($request->header->account, $item, $itemNew, ItemMapper::class, 'price', $request->getOrigin());
        }

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

        $new->status = PriceStatus::tryFromValue($request->getDataInt('type')) ?? $new->status;

        $new->promocode = $request->getDataString('promocode') ?? $new->promocode;

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
        $new->quantity           = $request->getDataInt('quantity') ?? $new->quantity;
        $new->price              = $new->priceNew;
        $new->priceNew           = $request->hasData('price_new') ? new FloatInt((int) $request->getData('price_new')) :
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
