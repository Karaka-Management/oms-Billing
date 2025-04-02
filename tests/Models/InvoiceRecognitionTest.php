<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\Bill;
use Modules\Billing\Models\InvoiceRecognition;
use phpOMS\Ai\Ocr\Tesseract\TesseractOcr;
use phpOMS\Utils\Parser\Pdf\PdfParser;

require_once __DIR__ . '/../Autoloader.php';

/**
 * @internal
 */
final class InvoiceRecognitionTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testNetSales($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['netSales'], $billObj->netSales->value);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testTaxRate($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['tax_rate'], \reset($billObj->elements)->taxR->value);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testGrossSales($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['grossSales'], $billObj->grossSales->value);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testTaxAmount($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['tax_amount'], $billObj->taxP->value);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testBillDate($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['bill_date'], $billObj->billDate?->format('Y-m-d'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testBillLanguage($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['language'], $billObj->language);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testBillCurrency($json, $content) : void
    {
        $billObj = new Bill();
        InvoiceRecognition::detect($billObj, $content);

        $test = \json_decode(\file_get_contents($json), true);

        self::assertEquals($test['currency'], $billObj->currency);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testIban($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            \str_replace(' ', '', $test['iban']),
            \str_replace(' ', '', InvoiceRecognition::findIban($lines, $identifiers['iban']))
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testVATId($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            $test['vat_id'],
            InvoiceRecognition::findVat($lines, $identifiers['vat_id'][$test['language']])
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testTaxId($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            $test['tax_id'],
            InvoiceRecognition::findTaxId($lines, $identifiers['tax_id'][$test['language']])
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testWebsite($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            $test['website'],
            InvoiceRecognition::findWebsite($lines, $identifiers['website'])
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testEmail($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            $test['email'],
            InvoiceRecognition::findEmail($lines, $identifiers['email'])
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('billList')]
    #[\PHPUnit\Framework\Attributes\Group('optional')]
    public function testPhone($json, $content) : void
    {
        $identifierContent = \file_get_contents(__DIR__ . '/../../Models/bill_identifier.json');
        if ($identifierContent === false) {
            $identifierContent = '{}';
        }

        /** @var array $identifiers */
        $identifiers = \json_decode($identifierContent, true);

        $test = \json_decode(\file_get_contents($json), true);

        $lines = \explode("\n", $content);
        foreach ($lines as $line => $value) {
            if (empty(\trim($value))) {
                unset($lines[$line]);
            }
        }
        $lines = \array_values($lines);

        self::assertEquals(
            \str_replace(' ', '', $test['phone']),
            \str_replace(' ', '', InvoiceRecognition::findPhone($lines, $identifiers['phone'][$test['language']]))
        );
    }

    public static array $billList = [];

    public static function billList()
    {
        /*
        if (\str_ends_with(__DIR__ . '/bills/12.png', 'pdf')) {
            $content = PdfParser::pdf2text(__DIR__ . '/bills/12.png');
        } else {
            $ocr = new TesseractOcr();

            $content = $ocr->parseImage(__DIR__ . '/bills/12.png');
        }

        return [
            [
                __DIR__ . '/bills/12.json',
                $content
            ]
        ];
        */

        if (!empty(self::$billList)) {
            return self::$billList;
        }

        $files = \scandir(__DIR__ . '/bills/');
        foreach ($files as $bill) {
            if ($bill === '.' || $bill === '..' || \str_ends_with($bill, '.json')) {
                continue;
            }

            $parts = \explode('.', $bill);
            $count = \count($parts);
            unset($parts[$count - 1]);

            if (\str_ends_with(__DIR__ . '/bills/' . $bill, 'pdf')) {
                $content = PdfParser::pdf2text(__DIR__ . '/bills/' . $bill);
            } else {
                $ocr = new TesseractOcr();

                $content = $ocr->parseImage(__DIR__ . '/bills/' . $bill);
            }

            $element = [
                __DIR__ . '/bills/' . \implode('', $parts) . '.json',
                $content,
            ];

            self::$billList[] = $element;
        }

        return self::$billList;
    }

    public static function tearDownAfterClass() : void
    {
        self::$billList = [];
    }
}
