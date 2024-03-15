<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO4217DecimalEnum;
use phpOMS\Localization\ISO4217SymbolEnum;
use phpOMS\Localization\LanguageDetection\Language;
use phpOMS\Localization\Localization;
use phpOMS\Stdlib\Base\FloatInt;
use phpOMS\Stdlib\Base\Iban;
use phpOMS\Validation\Finance\EUVat;
use phpOMS\Validation\Finance\IbanEnum;

/**
 * Bill type enum.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class InvoiceRecognition
{
    /**
     * Detect bill components
     *
     * @param Bill   $bill    Bill
     * @param string $content Bill content
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function detect(Bill $bill, string $content) : void
    {
        $content = \strtolower($content);
        $lines   = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }

        $lines = \array_values($lines);

        $language = self::detectLanguage($content);
        if (!\in_array($language, ['en', 'de'])) {
            $language = 'en';
        }

        $bill->language = $language;

        $l11n = Localization::fromLanguage($language);

        $identifierContent = \file_get_contents(__DIR__ . '/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $bill->billCountry = self::findCountry($lines, $identifiers, $language);

        $currency        = self::findCurrency($lines);
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
        $type = self::findSupplierInvoiceType($content, $identifiers['type'], $language);

        /*
        @var \Modules\Billing\Models\BillType $billType
        $billType = BillTypeMapper::get()
            ->where('name', $type)
            ->execute();

        $bill->type = new NullBillType($billType->id);
        */

        /* Number */
        $billNumber     = self::findBillNumber($lines, $identifiers['bill_no'][$language]);
        $bill->external = $billNumber;

        /* Reference / PO */
        // @todo implement

        /* Date */
        $billDateTemp = self::findBillDate($lines, $identifiers['bill_date'][$language]);
        $billDate     = self::parseDate($billDateTemp, $identifiers['date_format']);

        $bill->billDate = $billDate;

        /* Due */
        $billDueTemp = self::findBillDue($lines, $identifiers['bill_due'][$language]);
        $billDue     = self::parseDate($billDueTemp, $identifiers['date_format']);
        // @todo implement multiple due dates for bills

        /* Total */
        $totalGross = self::findBillGross($lines, $identifiers['total_gross'][$language]);
        $totalNet   = self::findBillNet($lines, $identifiers['total_net'][$language]);

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
        $totalTaxAmount = self::findBillTaxAmount($lines, $identifiers['total_tax'][$language]);
        $taxRates       = self::findBillTaxRates($lines, $identifiers['tax_rate'][$language]);

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
        $itemLines = self::findBillItemLines($lines, $identifiers['item_table'][$language]);

        // @todo Try to find item from item database
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
            }

            /* Total Special */
            // @question How do we want to apply total discounts?
            //      Option 1: Apply in relation to the amount per line item (this would be correct for stock evaluation)
            //      Option 2: Additional element (For correct stock evaluation we could do a internal/backend correction in the lot price calculation)
            //
            //      Option 2 seems nicer from a user perspective!
            $totalSpecial = self::findBillSpecial($lines, $identifiers, $language, $itemLineEnd);
            foreach ($totalSpecial as $key => $amount) {
                if ($amount === 0) {
                    continue;
                }

                $key = \str_replace('total_', '', $key);

                $billElement       = new BillElement();
                $billElement->bill = $bill;

                $billElement->taxR->value = $taxRates;

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
        if (empty($bill->elements)) {
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
    }

    /**
     * Detect language from content
     *
     * @param string $content String to analyze
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function detectLanguage(string $content) : string
    {
        $detector = new Language();
        $language = $detector->detect($content)->bestResults()->close();

        if (!\is_array($language) || empty($language)) {
            return 'en';
        }

        return \substr(\array_keys($language)[0], 0, 2);
    }

    /**
     * Detect the supplier bill type
     *
     * @param string $content  String to analyze
     * @param array  $types    Possible bill types
     * @param string $language Bill language
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findSupplierInvoiceType(string $content, array $types, string $language) : string
    {
        $bestPos   = \strlen($content);
        $bestMatch = '';

        foreach ($types as $name => $type) {
            foreach ($type[$language] as $l11n) {
                $found = \stripos($content, \strtolower($l11n));

                if ($found !== false && $found < $bestPos) {
                    $bestPos   = $found;
                    $bestMatch = $name;
                }
            }
        }

        return empty($bestMatch) ? 'purchase_invoice' : $bestMatch;
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findBillNumber(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['bill_no'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill due date
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Due match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findBillDue(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        // @todo don't many invoices have the due date at the bottom? bestPos doesn't make sense?!
                        $bestPos   = $row;
                        $bestMatch = $found['bill_due'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill date
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Date match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findBillDate(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['bill_date'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Tax match patterns
     *
     * @return int
     *
     * @since 1.0.0
     * @todo Handle multiple tax lines
     *      Example: 19% and 7%
     */
    public static function findBillTaxAmount(array $lines, array $matches) : int
    {
        $bestMatch = 0;

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_tax']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $gross = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($gross > $bestMatch) {
                        $bestMatch = $gross;
                    }
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Tax match patterns
     *
     * @return int
     *
     * @since 1.0.0
     * @todo Handle multiple tax lines
     *      Example: 19% and 7%
     */
    public static function findBillTaxRates(array $lines, array $matches) : int
    {
        $bestMatch = 0;

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['tax_rate']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $rate = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($rate > $bestMatch) {
                        $bestMatch = $rate;
                    }
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Net match patterns
     *
     * @return string
     *
     * @bug Issue with net/discount/gross in one line
     *
     * @since 1.0.0
     * @todo maybe check with taxes
     * @todo maybe make sure text position is before total_gross
     */
    public static function findBillNet(array $lines, array $matches) : string
    {
        $bestMatch    = 0;
        $bestMatchStr = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1
                    && \preg_match('/[,.]{1,1}[\d]{4}$/', $found['total_net']) !== 1
                ) {
                    $temp = \trim($found['total_net']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $net = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($net > $bestMatch) {
                        $bestMatch    = $net;
                        $bestMatchStr = $temp;
                    }
                }
            }
        }

        return $bestMatchStr;
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Gross match patterns
     *
     * @return string
     *
     * @bug Issue with net/discount/gross in one line
     *
     * @since 1.0.0
     */
    public static function findBillGross(array $lines, array $matches) : string
    {
        $bestMatch    = 0;
        $bestMatchStr = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1
                    && \preg_match('/[,.]{1,1}[\d]{4}$/', $found['total_gross']) !== 1
                ) {
                    $temp = \trim($found['total_gross']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $gross = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($gross > $bestMatch) {
                        $bestMatch    = $gross;
                        $bestMatchStr = $temp;
                    }
                }
            }
        }

        return $bestMatchStr;
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Gross match patterns
     *
     * @return array
     *
     * @bug Issue with net/discount/gross in one line
     *
     * @since 1.0.0
     */
    public static function findBillSpecial(array $lines, array $matches, string $language, int $lineStart) : array
    {
        // Find discounts
        $bestDiscount = 0;
        $found        = [];

        foreach ($matches['total_discount'][$language] as $match) {
            foreach ($lines as $idx => $line) {
                if ($idx < $lineStart) {
                    continue;
                }

                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_discount']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $discount = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    $discount = \abs($discount);

                    if ($discount > $bestDiscount) {
                        $bestDiscount = $discount;
                        $discountLine = $idx;

                        break;
                    }
                }
            }
        }

        // Find shipping
        $bestShipping = 0;
        $found        = [];

        $shippingLine = 0;

        foreach ($matches['total_shipping'][$language] as $match) {
            foreach ($lines as $idx => $line) {
                if ($idx < $lineStart) {
                    continue;
                }

                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_shipping']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $shipping = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($shipping > $bestShipping) {
                        $bestShipping = $shipping;
                        $shippingLine = $idx;

                        break;
                    }
                }
            }
        }

        // Find customs
        $bestCustoms = 0;
        $found       = [];

        $customsLine = 0;

        foreach ($matches['total_customs'][$language] as $match) {
            foreach ($lines as $idx => $line) {
                if ($idx < $lineStart) {
                    continue;
                }

                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_customs']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $customs = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($customs > $bestCustoms) {
                        $bestCustoms = $customs;
                        $customsLine = $idx;

                        break;
                    }
                }
            }
        }

        // Find insurance
        $bestInsurance = 0;
        $found         = [];

        $insuranceLine = 0;

        foreach ($matches['total_insurance'][$language] as $match) {
            foreach ($lines as $idx => $line) {
                if ($idx < $lineStart) {
                    continue;
                }

                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_insurance']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $insurance = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($insurance > $bestInsurance) {
                        $bestInsurance = $insurance;
                        $insuranceLine = $idx;

                        break;
                    }
                }
            }
        }

        // Find surcharge
        $bestSurcharge = 0;
        $found         = [];

        foreach ($matches['total_surcharge'][$language] as $match) {
            foreach ($lines as $idx => $line) {
                if ($idx < $lineStart) {
                    continue;
                }

                if (\preg_match($match, $line, $found) === 1
                    && $idx !== $shippingLine
                    && $idx !== $customsLine
                    && $idx !== $insuranceLine
                ) {
                    $temp = \trim($found['total_surcharge']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $surcharge = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : FloatInt::DIVISOR);

                    if ($surcharge > $bestSurcharge) {
                        $bestSurcharge = $surcharge;

                        break;
                    }
                }
            }
        }

        return [
            'total_discount'  => -1 * $bestDiscount,
            'total_shipping'  => $bestShipping,
            'total_customs'   => $bestCustoms,
            'total_insurance' => $bestInsurance,
            'total_surcharge' => $bestSurcharge,
        ];
    }

    /**
     * Detect the supplier bill gross amount
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Item lines match patterns
     *
     * @return array
     *
     * @since 1.0.0
     */
    public static function findBillItemLines(array $lines, array $matches) : array
    {
        // Find start for item list (should be a headline containing certain words)
        $startLine = 0;
        $bestMatch = 0;

        foreach ($lines as $idx => $line) {
            $headlineMatches = 0;

            foreach ($matches['headline'] as $match) {
                foreach ($match as $headline) {
                    if (\stripos($line, $headline) !== false) {
                        ++$headlineMatches;

                        continue;
                    }
                }
            }

            if ($headlineMatches > $bestMatch && $headlineMatches > 1) {
                $bestMatch = $headlineMatches;
                $startLine = $idx;
            }
        }

        if ($startLine === 0) {
            return [];
        }

        // Find end of item lines
        $line = $lines[$startLine];

        // Get headline structure = item list structure
        $headlineStructure = [];
        foreach ($matches['headline'] as $type => $match) {
            foreach ($match as $headline) {
                // We have to make sure that there are
                if (\preg_match('/(\s{1,}' . $headline . '|' . $headline . '\s{1,})/', $line) === 1) {
                    $headlineStructure[$type] = true;

                    continue;
                }
            }
        }

        \asort($headlineStructure);

        $rows = [];

        // Get item list until end of item list/table is reached
        $found          = [];
        $structureCount = \count($headlineStructure);
        $linesSkipped   = 0;

        foreach ($lines as $l => $line) {
            // @todo find better way to identify end of item table
            // @bug find way to handle multiple pages
            // @bug find way to handle multi-line item description
            if ($l <= $startLine) {
                continue;
            }

            if ($linesSkipped > 2) {
                break;
            }

            if (\preg_match_all($matches['parts'], $line, $found) !== $structureCount) {
                ++$linesSkipped;
                continue;
            }

            $linesSkipped = 0;

            $temp = [];
            $c    = 0;
            foreach ($headlineStructure as $idx => $_) {
                $subFound = [];

                $temp[$idx] = \preg_match($matches['row'][$idx], $found[2][$c], $subFound) === 1
                    ? $subFound[0]
                    : '';

                ++$c;
            }

            $rows[$l] = $temp;
        }

        return $rows;
    }

    /**
     * Create DateTime from date string
     *
     * @param string   $date    Date string
     * @param string[] $formats Date formats
     *
     * @return null|\DateTime
     *
     * @since 1.0.0
     */
    public static function parseDate(string $date, array $formats, string $supplierFormat = '') : ?\DateTime
    {
        if ((!empty($supplierFormat))) {
            $dt = \DateTime::createFromFormat(
                $supplierFormat,
                $date
            );

            return $dt === false ? new \DateTime('1970-01-01') : $dt;
        }

        $now       = new \DateTime('now');
        $bestMatch = null;

        foreach ($formats as $format) {
            if (($obj = \DateTime::createFromFormat($format, $date)) !== false) {
                if (\abs($obj->getTimestamp() - $now->getTimestamp()) < 60 * 60 * 24 * 365 * 10) {
                    // The estimated date should be within 10 years
                    return $obj;
                }

                $bestMatch = $obj;
            }
        }

        return $bestMatch;
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findEmail(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['email'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findPhone(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['phone'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findWebsite(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['website'];
                    }

                    break;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findVat(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['vat_id'];
                    }

                    break;
                }
            }
        }

        if (\stripos($bestMatch, 'S') > 1
            || \stripos($bestMatch, 'O') > 1
        ) {
            $subIban   = \substr($bestMatch, 2);
            $subIban   = \str_replace(['S', 'O'], ['5', '0'], $subIban);
            $bestMatch = \substr($bestMatch, 0, 2) . $subIban;
        }

        return \str_replace([' ', '-'], '', \strtoupper(\trim($bestMatch)));
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findTaxId(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        // @performance A lot of these loops (see other functions as well) can be optimized
        //      Go over the lines first this way we stop the loop much earlier.
        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['tax_id'];
                    }

                    // Break 2 is required because here we also support searching for VAT ID.
                    // We do this because some software may use the identifiers for VAT and Tax id interchangeably
                    // The highest priority $match use the actual identifier and afterwards the other identifiers follow.
                    break 2;
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Detect the supplier bill number
     *
     * @param string[] $lines   Bill lines
     * @param array    $matches Number match patterns
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findIban(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = $found['iban'];
                    }

                    break;
                }
            }
        }

        $bestMatch = \trim(\strtoupper($bestMatch));
        $bestMatch = \str_replace(' ', '', $bestMatch);
        $bestMatch = \wordwrap($bestMatch, 4, ' ', true);

        // Trying to fix bad parsing
        if (\stripos($bestMatch, 'S') > 1
            || \stripos($bestMatch, 'O') > 1
        ) {
            /** @var string $format */
            $format = IbanEnum::getByName('_' . \substr($bestMatch, 0, 2)) ?? '';

            $len       = \strlen($bestMatch);
            $formatLen = \strlen($format);

            for ($i = 0; $i < $len; ++$i) {
                if ($i >= $formatLen) {
                    break;
                }

                if ($format[$i] !== 'k' && $format[$i] !== 'n') {
                    continue;
                }

                if ($bestMatch[$i] === 'O'
                    || $bestMatch[$i] === 'o'
                ) {
                    $bestMatch[$i] = '0';
                } elseif ($bestMatch[$i] === 'S'
                    || $bestMatch[$i] === 's'
                ) {
                    $bestMatch[$i] = '5';
                }
            }
        }

        return \trim($bestMatch);
    }

    /**
     * Find country from bill
     *
     * @param string[] $lines    Lines
     * @param array    $matches  Match patterns
     * @param string   $language Bill language
     */
    public static function findCountry(array $lines, array $matches, string $language) : string
    {
        $iban = self::findIban($lines, $matches['iban']);
        if (\phpOMS\Validation\Finance\Iban::isValid($iban)) {
            $obj = new Iban($iban);

            if (ISO3166TwoEnum::isValidValue($obj->getCountry())) {
                return \strtoupper($obj->getCountry());
            }
        }

        $vatId = self::findVat($lines, $matches['vat_id'][$language]);
        if (EUVat::isValid($vatId)) {
            return \strtoupper(\substr($vatId, 0, 2));
        }

        $email   = self::findEmail($lines, $matches['email']);
        $country = \strtoupper(\substr($email, \strrpos($email, '.') + 1));

        if (ISO3166TwoEnum::isValidValue($country)) {
            return \strtoupper($country);
        }

        $website = self::findWebsite($lines, $matches['website']);
        $country = \strtoupper(\substr($website, \strrpos($website, '.') + 1));

        if (ISO3166TwoEnum::isValidValue($country)) {
            return \strtoupper($country);
        }

        $countries = ISO3166TwoEnum::countryFromLanguage($language);

        return empty($countries) ? 'US' : \reset($countries);
    }

    /**
     * Find currency
     *
     * @param string[] $lines Lines
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function findCurrency(array $lines) : string
    {
        $symbols  = ISO4217SymbolEnum::getConstants();
        $currency = '';

        foreach ($lines as $line) {
            foreach ($symbols as $symbol) {
                $match = $symbol;
                if (\preg_match('/[\x20-\x7e]/', $symbol) === 1) {
                    $match = ' ' . $symbol . ' ';
                }

                if (\strpos($line, $match) !== false) {
                    /** @var string $currency */
                    $currency = ISO4217SymbolEnum::getName($symbol);

                    /** @var string $currency */
                    $currency = ISO4217CharEnum::getByName($currency) ?? '';

                    break;
                }
            }
        }

        if (!empty($currency)) {
            return $currency;
        }

        $symbols = ISO4217CharEnum::getConstants();

        foreach ($lines as $line) {
            foreach ($symbols as $symbol) {
                if (\strpos($line, ' ' . $symbol . ' ') !== false) {
                    $currency = $symbol;

                    break;
                }
            }
        }

        return $currency;
    }
}
