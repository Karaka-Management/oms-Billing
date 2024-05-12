<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\InvoiceRecognition;
use Modules\Billing\Models\NullBillType;
use Modules\Billing\Models\SettingsEnum;
use Modules\Payment\Models\PaymentType;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO4217DecimalEnum;
use phpOMS\Localization\Localization;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Views\View;

/**
 * Billing controller class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class CliController extends Controller
{
    /**
     * Analyze supplier bill
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function cliParseSupplierBill(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Cli/bill-parsed');

        $tag = null;
        if (!$request->hasData('-t')) {
            $tag = TagMapper::get()
                ->where('name', 'external_bill')
                ->execute();
        }

        $externalType = $request->getDataInt('-t') ?? (int) ($tag?->id);

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('elements')
            ->with('files')
            ->with('files/tags')
            ->with('files/content')
            ->where('id', (int) $request->getData('-i'))
            ->where('files/tags', $externalType)
            ->execute();

        if ($bill->id === 0) {
            return $view;
        }

        $old = clone $bill;

        $content = \strtolower($bill->getFileByTag($externalType)->content->content ?? '');
        $lines   = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }

        $lines = \array_values($lines);

        $language = InvoiceRecognition::detectLanguage($content);

        if (!\in_array($language, ['en', 'de'])) {
            $language = 'en';
        }

        $bill->language = $language;

        $l11n = Localization::fromLanguage($language);

        $identifierContent = \file_get_contents(__DIR__ . '/../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        /* Supplier */
        /** @var \Modules\SupplierManagement\Models\Supplier[] $suppliers */
        $suppliers = SupplierMapper::getAll()
            ->with('account')
            ->with('mainAddress')
            ->with('attributes/type')
            ->where('attributes/type/name', ['bill_match_pattern', 'bill_date_format'], 'IN')
            ->executeGetArray();

        $supplierId     = $this->matchSupplier($content, $suppliers);
        $bill->supplier = new NullSupplier($supplierId);
        $supplier       = $suppliers[$supplierId] ?? new NullSupplier();

        if ($supplier->id !== 0) {
            $bill->billTo      = $supplier->account->name1;
            $bill->billAddress = $supplier->mainAddress->address;
            $bill->billCity    = $supplier->mainAddress->city;
            $bill->billZip     = $supplier->mainAddress->postal;
            $bill->billCountry = $supplier->mainAddress->country;
        } else {
            $bill->billCountry = InvoiceRecognition::findCountry($lines, $identifiers, $language);
        }

        $currency        = InvoiceRecognition::findCurrency($lines);
        $countryCurrency = ISO4217CharEnum::currencyFromCountry($bill->billCountry);

        // Identified currency has to be country currency or one of the top globally used currencies
        if ($currency !== \in_array($currency, [
                $countryCurrency, ISO4217CharEnum::_USD, ISO4217CharEnum::_EUR, ISO4217CharEnum::_JPY,
                ISO4217CharEnum::_GBP, ISO4217CharEnum::_AUD, ISO4217CharEnum::_CAD, ISO4217CharEnum::_CHF,
                ISO4217CharEnum::_CNH, ISO4217CharEnum::_CNY,
            ])
        ) {
            $currency = $countryCurrency;
        }

        $bill->currency = $currency;

        $rd = -FloatInt::MAX_DECIMALS + ISO4217DecimalEnum::getByName('_' . $bill->currency);

        /* Type */
        $type = InvoiceRecognition::findSupplierInvoiceType($content, $identifiers['type'], $language);

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()
            ->where('name', $type)
            ->execute();

        $bill->type = new NullBillType($billType->id);

        /* Number */
        $billNumber     = InvoiceRecognition::findBillNumber($lines, $identifiers['bill_no'][$language]);
        $bill->external = $billNumber;

        /* Reference / PO */
        // @todo implement

        /* Date */
        $billDateTemp = InvoiceRecognition::findBillDate($lines, $identifiers['bill_date'][$language]);
        $billDate     = InvoiceRecognition::parseDate($billDateTemp, $identifiers['date_format'], $supplier->getAttribute('bill_date_format')->value->valueStr ?? '');

        $bill->billDate = $billDate;

        /* Due */
        $billDueTemp = InvoiceRecognition::findBillDue($lines, $identifiers['bill_due'][$language]);
        $billDue     = InvoiceRecognition::parseDate($billDueTemp, $identifiers['date_format'], $supplier->getAttribute('bill_date_format')->value->valueStr ?? '');
        // @todo implement multiple due dates for bills

        /* Total */
        $totalGross = InvoiceRecognition::findBillGross($lines, $identifiers['total_gross'][$language]);
        $totalNet   = InvoiceRecognition::findBillNet($lines, $identifiers['total_net'][$language]);

        // The number format needs to be corrected:
        //      Languages don't always respect the l11n number format
        //      Sometimes parsing errors can happen
        $format = FloatInt::identifyNumericFormat($totalGross);

        if ($format !== null) {
            $l11n->thousands = $format['thousands'];
            $l11n->decimal   = $format['decimal'];
        }

        $bill->grossSales = new FloatInt($totalGross, $l11n->thousands, $l11n->decimal);
        $bill->netSales   = new FloatInt($totalNet, $l11n->thousands, $l11n->decimal);

        /* Total Tax */
        // @todo taxes depend on local tax id (if company in Germany but invoice from US -> only gross amount important, there is no net)
        $totalTaxAmount = InvoiceRecognition::findBillTaxAmount($lines, $identifiers['total_tax'][$language]);
        $taxRates       = InvoiceRecognition::findBillTaxRates($lines, $identifiers['tax_rate'][$language]);

        if ($bill->netSales->value === 0) {
            $bill->netSales->value = $taxRates === 0
                ? $bill->grossSales->value
                : (int) \round($bill->grossSales->value / (1.0 + $taxRates / (FloatInt::DIVISOR * 100)), $rd);
        }

        if ($bill->grossSales->value === 0) {
            $bill->grossSales->value = $taxRates === 0
                ? $bill->netSales->value
                : $bill->netSales->value + ((int) \round($bill->netSales->value * $taxRates / (FloatInt::DIVISOR * 100), $rd));
        }

        // We just assume that finding the net sales value is more likely
        // If this turns out to be false, we need to recalculate the netSales from the grossSales instead
        if ($bill->grossSales->value === $bill->netSales->value) {
            $bill->grossSales->value = $bill->netSales->value + ((int) \round($bill->netSales->value * $taxRates / (FloatInt::DIVISOR * 100), $rd));
        }

        if ($taxRates === 0 && $bill->netSales->value !== $bill->grossSales->value) {
            $taxRates = ((int) ($bill->grossSales->value / ($bill->grossSales->value / FloatInt::DIVISOR))) - FloatInt::DIVISOR;
        }

        /* Item lines */
        $itemLines = InvoiceRecognition::findBillItemLines($lines, $identifiers['item_table'][$language]);

        // @todo Try to find item from item database
        // @todo Some of the element value setting is unnecessary as it happens also in the recalculatePrices()
        //      Same goes for the bill element creations further down below
        if (empty($bill->elements)) {
            $itemLineEnd = 0;
            foreach ($itemLines as $line => $itemLine) {
                $itemLineEnd = $line;

                $billElement       = new BillElement();
                $billElement->bill = $bill;

                $billElement->taxR->value = $taxRates;

                if (isset($itemLine['description'])) {
                    $billElement->itemName = \trim($itemLine['description']);
                }

                if (isset($itemLine['quantity'])) {
                    $billElement->quantity = new FloatInt($itemLine['quantity'], $l11n->thousands, $l11n->decimal);
                }

                // Unit
                if (isset($itemLine['price'])) {
                    $billElement->singleListPriceNet = new FloatInt($itemLine['price'], $l11n->thousands, $l11n->decimal);

                    $billElement->singleSalesPriceNet    = $billElement->singleListPriceNet;
                    $billElement->singlePurchasePriceNet = $billElement->singleSalesPriceNet;

                    if ($billElement->taxR->value > 0) {
                        $billElement->singleListPriceGross->value = $billElement->singleListPriceNet->value + ((int) \round($billElement->singleSalesPriceNet->value * $billElement->taxR->value / (FloatInt::DIVISOR * 100), $rd));
                        $billElement->singleSalesPriceGross       = $billElement->singleListPriceGross;
                    } else {
                        $billElement->singleListPriceGross  = $billElement->singleListPriceNet;
                        $billElement->singleSalesPriceGross = $billElement->singleListPriceGross;
                    }
                }

                // Total
                if (isset($itemLine['total'])) {
                    $billElement->totalListPriceNet = new FloatInt($itemLine['total'], $l11n->thousands, $l11n->decimal);

                    $billElement->totalSalesPriceNet    = $billElement->totalListPriceNet;
                    $billElement->totalPurchasePriceNet = $billElement->totalSalesPriceNet;

                    if ($billElement->taxR->value > 0) {
                        $billElement->totalListPriceGross->value = $billElement->totalListPriceNet->value + ((int) \round($billElement->totalSalesPriceNet->value * $billElement->taxR->value / (FloatInt::DIVISOR * 100), $rd));
                        $billElement->totalSalesPriceGross       = $billElement->totalListPriceGross;
                    } else {
                        $billElement->totalListPriceGross  = $billElement->totalListPriceNet;
                        $billElement->totalSalesPriceGross = $billElement->totalListPriceGross;
                    }
                }

                $billElement->taxP->value = $billElement->totalSalesPriceGross->value - $billElement->totalSalesPriceNet->value;

                $billElement->recalculatePrices();
                $bill->elements[] = $billElement;

                $this->createModel($request->header->account, $billElement, BillElementMapper::class, 'bill_element', $request->getOrigin());
            }

            /* Total Special */
            // @question How do we want to apply total discounts?
            //      Option 1: Apply in relation to the amount per line item (this would be correct for stock evaluation)
            //      Option 2: Additional element (For correct stock evaluation we could do a internal/backend correction in the lot price calculation)
            //
            //      Option 2 seems nicer from a user perspective!
            $totalSpecial = InvoiceRecognition::findBillSpecial($lines, $identifiers, $language, $itemLineEnd);
            foreach ($totalSpecial as $key => $amount) {
                if ($amount === 0) {
                    continue;
                }

                $key = \str_replace('total_', '', $key);

                $billElement       = new BillElement();
                $billElement->bill = $bill;

                $billElement->taxR->value = $taxRates;

                $internalRequest  = new HttpRequest();
                $internalResponse = new HttpResponse();

                $internalRequest->header->account = $request->header->account;
                $internalRequest->header->l11n    = $request->header->l11n;

                $internalRequest->setData('search', $key);
                $internalRequest->setData('limit', 1);

                $internalResponse->header->l11n           = clone $response->header->l11n;
                $internalResponse->header->l11n->language = $bill->language;

                $this->app->moduleManager->get('ItemManagement', 'Api')->apiItemFind($internalRequest, $internalResponse);
                $item = $internalResponse->getDataArray('')[0];

                $billElement->itemName = $key;

                if ($item->id !== 0) {
                    $billElement->item       = $item;
                    $billElement->itemNumber = $item->number;
                    $billElement->itemName   = $item->getL11n('name1')->content;
                }

                $billElement->quantity->value = FloatInt::DIVISOR;

                // Unit
                $billElement->singleListPriceNet = new FloatInt($amount, $l11n->thousands, $l11n->decimal);

                $billElement->singleSalesPriceNet    = $billElement->singleListPriceNet;
                $billElement->singlePurchasePriceNet = $billElement->singleSalesPriceNet;

                if ($billElement->taxR->value > 0) {
                    $billElement->singleListPriceGross->value = $billElement->singleListPriceNet->value + ((int) \round($billElement->singleSalesPriceNet->value * $billElement->taxR->value / (FloatInt::DIVISOR * 100), $rd));
                    $billElement->singleSalesPriceGross       = $billElement->singleListPriceGross;
                } else {
                    $billElement->singleListPriceGross  = $billElement->singleListPriceNet;
                    $billElement->singleSalesPriceGross = $billElement->singleListPriceGross;
                }

                // Total
                $billElement->totalListPriceNet     = $billElement->singleListPriceNet;
                $billElement->totalSalesPriceNet    = $billElement->singleSalesPriceNet;
                $billElement->totalPurchasePriceNet = $billElement->singlePurchasePriceNet;
                $billElement->totalListPriceGross   = $billElement->singleListPriceGross;
                $billElement->totalSalesPriceGross  = $billElement->singleSalesPriceGross;

                $billElement->taxP->value = $billElement->totalSalesPriceGross->value - $billElement->totalSalesPriceNet->value;

                $billElement->recalculatePrices();
                $bill->elements[] = $billElement;

                $this->createModel($request->header->account, $billElement, BillElementMapper::class, 'bill_element', $request->getOrigin());
            }
        }

        if (!empty($bill->elements)) {
            // Calculate totals from elements
            $totalNet   = 0;
            $totalGross = 0;
            foreach ($bill->elements as $element) {
                $totalNet   += $element->totalSalesPriceNet->value;
                $totalGross += $element->totalSalesPriceGross->value;
            }

            $bill->grossSales = new FloatInt($totalGross);
            $bill->netCosts   = new FloatInt($totalNet);
            $bill->netSales   = $bill->netCosts;
        }

        $bill->taxP->value = $bill->grossSales->value - $bill->netSales->value;

        // No elements could be identified -> make total a bill element
        if (empty($itemLines) && empty($bill->elements)) {
            $billElement       = new BillElement();
            $billElement->bill = $bill;

            // List price
            $billElement->singleListPriceNet->value = $bill->netSales->value;
            $billElement->totalListPriceNet->value  = $bill->netSales->value;

            $billElement->singleListPriceGross->value = $bill->grossSales->value;
            $billElement->totalListPriceGross->value  = $bill->grossSales->value;

            // Unit price
            $billElement->singleSalesPriceNet->value    = $bill->netSales->value;
            $billElement->singlePurchasePriceNet->value = $bill->netSales->value;

            $billElement->singleSalesPriceGross->value = $bill->grossSales->value;

            // Total
            $billElement->totalSalesPriceNet->value    = $bill->netSales->value;
            $billElement->totalPurchasePriceNet->value = $bill->netSales->value;

            $billElement->totalSalesPriceGross->value = $bill->grossSales->value;

            $billElement->taxP->value = $bill->taxP->value;
            $billElement->taxR->value = $taxRates;

            $billElement->recalculatePrices();
            $bill->elements[] = $billElement;

            $this->createModel($request->header->account, $billElement, BillElementMapper::class, 'bill_element', $request->getOrigin());
        }

        // Re-calculate totals from elements due to change
        $totalNet   = 0;
        $totalGross = 0;
        foreach ($bill->elements as $element) {
            $totalNet   += $element->totalSalesPriceNet->value;
            $totalGross += $element->totalSalesPriceGross->value;
        }

        $bill->grossSales = new FloatInt($totalGross);
        $bill->netCosts   = new FloatInt($totalNet);
        $bill->netSales   = $bill->netCosts;

        $bill->taxP->value = $bill->grossSales->value - $bill->netSales->value;

        $this->updateModel($request->header->account, $old, $bill, BillMapper::class, 'bill_parsing', $request->getOrigin());

        // @todo change tax code during/after bill parsing

        $view->data['bill'] = $bill;

        // Fix internal document
        $request->setData('bill', $bill->id, true);
        $billResponse = new HttpResponse();
        $this->app->moduleManager->get('Billing', 'ApiBill')->apiBillPdfArchiveCreate($request, $billResponse);

        return $view;
    }

    /**
     * Find possible supplier id
     *
     * Priorities:
     *  1. bill_match_pattern
     *  2. name1 + IBAN
     *  3. name1 + city || address
     *
     * @param string     $content   Content to analyze
     * @param Supplier[] $suppliers Suppliers
     *
     * @return int
     *
     * @since 1.0.0
     */
    private function matchSupplier(string $content, array $suppliers) : int
    {
        // bill_match_pattern
        foreach ($suppliers as $supplier) {
            // @todo consider to support regex?
            if ((!empty($supplier->getAttribute('bill_match_pattern')->value->valueStr)
                    && \stripos($content, $supplier->getAttribute('bill_match_pattern')->value->valueStr) !== false)
            ) {
                return $supplier->id;
            }
        }

        // name1 + IBAN
        foreach ($suppliers as $supplier) {
            if (\stripos($content, $supplier->account->name1) !== false) {
                $ibans = $supplier->getPaymentsByType(PaymentType::SWIFT);
                foreach ($ibans as $iban) {
                    if (\stripos($content, $iban->content2) !== false) {
                        return $supplier->id;
                    }
                }
            }
        }

        // name1 + city || address
        foreach ($suppliers as $supplier) {
            if (\stripos($content, $supplier->account->name1) !== false
                && ((!empty($supplier->mainAddress->city)
                        && \stripos($content, $supplier->mainAddress->city) !== false)
                    || (!empty($supplier->mainAddress->address)
                        && \stripos($content, $supplier->mainAddress->address) !== false)
                )
             ) {
                return $supplier->id;
            }
        }

        return 0;
    }
}
