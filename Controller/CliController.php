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

use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\NullBillType;
use Modules\Billing\Models\SettingsEnum;
use Modules\Payment\Models\PaymentType;
use Modules\SupplierManagement\Models\NullSupplier;
use Modules\SupplierManagement\Models\Supplier;
use Modules\SupplierManagement\Models\SupplierMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Localization\LanguageDetection\Language;
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
        /** @var \Model\Setting $setting */
        $setting = $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        );

        $originalType = $request->getDataInt('type') ?? (int) $setting->content;

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('files')
            ->with('media/types')
            ->with('media/content')
            ->where('id', (int) $request->getData('i'))
            ->where('media/types/id', $originalType)
            ->execute();

        $old = clone $bill;

        $content = \strtolower($bill->getFileByType($originalType)->content->content ?? '');
        $lines   = \explode("\n", $content);

        $language       = $this->detectLanguage($content);
        $bill->language = $language;

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
            ->execute();

        $supplierId     = $this->matchSupplier($content, $suppliers);
        $bill->supplier = new NullSupplier($supplierId);
        $supplier       = $suppliers[$supplierId] ?? new NullSupplier();

        $bill->billTo      = $supplier->account->name1;
        $bill->billAddress = $supplier->mainAddress->address;
        $bill->billCity    = $supplier->mainAddress->city;
        $bill->billZip     = $supplier->mainAddress->postal;
        $bill->billCountry = $supplier->mainAddress->country;

        /* Type */
        $type = $this->findSupplierInvoiceType($content, $identifiers['type'], $language);

        /** @var \Modules\Billing\Models\BillType $billType */
        $billType = BillTypeMapper::get()
            ->where('name', $type)
            ->execute();

        $bill->type = new NullBillType($billType->id);

        /* Number */
        $billNumber   = $this->findBillNumber($lines, $identifiers['bill_no'][$language]);
        $bill->number = $billNumber;

        /* Date */
        $billDateTemp = $this->findBillDate($lines, $identifiers['bill_date'][$language]);
        $billDate     = $this->parseDate($billDateTemp, $supplier, $identifiers['date_format']);

        $bill->billDate = $billDate;

        /* Due */
        $billDueTemp = $this->findBillDue($lines, $identifiers['bill_date'][$language]);
        $billDue     = $this->parseDate($billDueTemp, $supplier, $identifiers['date_format']);
        // @todo implement multiple due dates for bills

        /* Total Net */
        $totalNet       = $this->findBillNet($lines, $identifiers['total_net'][$language]);
        $bill->netCosts = new FloatInt($totalNet);

        /* Total Tax */
        $totalTaxAmount = $this->findBillTaxAmount($lines, $identifiers['total_net'][$language]);

        /* Total Gross */
        $totalGross       = $this->findBillGross($lines, $identifiers['total_gross'][$language]);
        $bill->grossCosts = new FloatInt($totalGross);

        /* Item lines */
        $itemLines = $this->findBillItemLines($lines, $identifiers['item_table'][$language]);

        $this->updateModel($request->header->account, $old, $bill, BillMapper::class, 'bill_parsing', $request->getOrigin());

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Cli/bill-parsed');
        $view->data['bill'] = $bill;

        // @todo change tax code during/after bill parsing

        return $view;
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
    private function detectLanguage(string $content) : string
    {
        $detector = new Language();
        $language = $detector->detect($content)->bestResults()->close();

        if (!\is_array($language) || \count($language) < 1) {
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
    private function findSupplierInvoiceType(string $content, array $types, string $language) : string
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
    private function findBillNumber(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = \trim($found['bill_no']);
                    }

                    break;
                }
            }
        }

        return $bestMatch;
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
    private function findBillDue(array $lines, array $matches) : string
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
                        $bestMatch = \trim($found['bill_due']);
                    }

                    break;
                }
            }
        }

        return $bestMatch;
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
    private function findBillDate(array $lines, array $matches) : string
    {
        $bestPos   = \count($lines);
        $bestMatch = '';

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $row => $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    if ($row < $bestPos) {
                        $bestPos   = $row;
                        $bestMatch = \trim($found['bill_date']);
                    }

                    break;
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
    private function findBillTaxAmount(array $lines, array $matches) : int
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
                        : 10000);

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
     * @param array    $matches Net match patterns
     *
     * @return int
     *
     * @since 1.0.0
     * @todo maybe check with taxes
     * @todo maybe make sure text position is before total_gross
     */
    private function findBillNet(array $lines, array $matches) : int
    {
        $bestMatch = 0;

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_net']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $gross = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : 10000);

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
     * @param array    $matches Gross match patterns
     *
     * @return int
     *
     * @since 1.0.0
     */
    private function findBillGross(array $lines, array $matches) : int
    {
        $bestMatch = 0;

        $found = [];

        foreach ($matches as $match) {
            foreach ($lines as $line) {
                if (\preg_match($match, $line, $found) === 1) {
                    $temp = \trim($found['total_gross']);

                    $posD = \stripos($temp, '.');
                    $posK = \stripos($temp, ',');

                    $hasDecimal = ($posD !== false || $posK !== false)
                        && \max((int) $posD, (int) $posK) + 3 >= \strlen($temp);

                    $gross = ((int) \str_replace(['.', ','], ['', ''], $temp)) * ($hasDecimal
                        ? 100
                        : 10000);

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
     * @param array    $matches Item lines match patterns
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function findBillItemLines(array $lines, array $matches) : array
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

        // Get headline structure = item list structure
        $headlineStructure = [];
        foreach ($matches['headline'] as $type => $match) {
            foreach ($match as $headline) {
                if (($pos = \stripos($line, $headline)) !== false) {
                    $headlineStructure[$type] = $pos;

                    continue;
                }
            }
        }

        \asort($headlineStructure);

        // Get item list until end of item list/table is reached

        return [];
    }

    /**
     * Find possible supplier id
     *
     * Priorities:
     *  1. bill_match_pattern
     *  2. name1 + IBAN
     *  3. name1 + city || address
     *  4. name1
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

        // name1
        foreach ($suppliers as $supplier) {
            if (\stripos($content, $supplier->account->name1) !== false) {
                return $supplier->id;
            }
        }

        return 0;
    }

    /**
     * Create DateTime from date string
     *
     * @param string   $date     Date string
     * @param Supplier $supplier Supplier
     * @param string[] $formats  Date formats
     *
     * @return null|\DateTime
     *
     * @since 1.0.0
     */
    private function parseDate(string $date, Supplier $supplier, array $formats) : ?\DateTime
    {
        if ((!empty($supplier->getAttribute('bill_date_format')->value->valueStr))) {
            $dt = \DateTime::createFromFormat(
                $supplier->getAttribute('bill_date_format')->value->valueStr ?? '',
                $date
            );

            return $dt === false ? new \DateTime('1970-01-01') : $dt;
        }

        foreach ($formats as $format) {
            if (($obj = \DateTime::createFromFormat($format, $date)) !== false) {
                return $obj === false ? null : $obj;
            }
        }

        return null;
    }
}
