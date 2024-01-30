<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Media
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Billing\Models\NullBill;
use phpOMS\Localization\ISO3166NameEnum;
use phpOMS\Localization\Money;

/** @var \phpOMS\Views\View $this */

require_once $this->data['defaultTemplates']->findFile('.pdf.php')->getAbsolutePath();

/** @var \Modules\Billing\Models\Bill $bill */
$bill = $this->data['bill'] ?? new NullBill();

// Set up default pdf template
/** @phpstan-import-type DefaultPdf from ../../../../Admin/Install/Media/PdfDefaultTemplate/pdfTemplate.pdf.php */
$pdf = new DefaultPdf();

$lang = include __DIR__ . '/lang.php';

$pdf->attributes['title_name'] = $this->data['bill_logo_name'] ?? 'Jingga';
$pdf->attributes['slogan']     = $this->data['bill_slogan'] ?? 'Business solutions made simple.';

$pdf->setHeaderData(
    __DIR__ . '/logo.png', 15,
    $pdf->attributes['title_name'],
    $pdf->attributes['slogan'],
);
$pdf->setCreator((string) ($this->data['bill_creator'] ?? 'Jingga'));
$pdf->setAuthor((string) ($this->data['bill_creator'] ?? 'Jingga'));
$pdf->setTitle((string) ($this->data['bill_title'] ?? $bill->type->getL11n()));
$pdf->setSubject((string) ($this->data['bill_subtitle'] ?? ''));
$pdf->setKeywords(\implode(', ', (array) ($this->data['keywords'] ?? [])));
$pdf->language = $bill->language;

$pdf->attributes['legal_name'] = $this->data['legal_company_name'] ?? 'Jingga e. K.';
$pdf->attributes['address']    = $this->data['bill_company_address'] ?? 'Kirchstr. 33';
$pdf->attributes['city']       = $this->data['bill_company_city'] ?? '61191 Rosbach';

$pdf->attributes['ceo']        = $this->data['bill_company_ceo'] ?? 'Dennis Eichhorn';
$pdf->attributes['tax_office'] = $this->data['bill_company_tax_office'] ?? 'HRA 5058';
$pdf->attributes['tax_number'] = $this->data['bill_company_tax_id'] ?? 'DE362646968';
$pdf->attributes['terms']      = $this->data['bill_company_terms'] ?? 'https://jingga.app/terms';

$pdf->attributes['bank_name']    = $this->data['bill_company_bank_name'] ?? 'Volksbank Mittelhessen';
$pdf->attributes['swift']        = $this->data['bill_company_swift'] ?? 'VBMHDE5F';
$pdf->attributes['bank_account'] = $this->data['bill_company_bank_account'] ?? 'DE62 5139 0000 0084 8044 10';

$pdf->attributes['website'] = $this->data['bill_company_website'] ?? 'www.jingga.app';
$pdf->attributes['email']   = $this->data['bill_company_email'] ?? 'info@jingga.app';
$pdf->attributes['phone']   = $this->data['bill_company_phone'] ?? '+49 152 04337728';

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();
$topPos = $pdf->getY();

// Set up default bill template
$billTypeName = \strtoupper($bill->type->getL11n());

// Address
$pdf->setY(50);
$pdf->setFont('helvetica', '', 10);

$toCountry = ISO3166NameEnum::getBy2Code($bill->billCountry);

$addressString = \trim(
    $bill->billTo . "\n"
    . (empty($bill->billAddress) ? '' : ($bill->billAddress . "\n"))
    . (empty($bill->billCity) ? '' : ($bill->billCity . "\n"))
    . (empty($toCountry) ? '' : ($toCountry . "\n")),
    "\n "
);

// Count the char "\n" in $addressString
$addressLineCount = \substr_count($addressString, "\n") + 1;

$lineHeight = $pdf->getY();
$pdf->Write(
    0,
    $addressString,
    '', false, 'L', false, 0, false, false, 0
);
$lineHeight = ($lineHeight - $pdf->getY()) / $addressLineCount;

$pageWidth  = $pdf->getPageWidth();
$pageHeight = $pdf->getPageHeight();

// Bill head
$pdf->setFont('helvetica', 'B', 16);
$titleWidth = $pdf->getStringWidth($billTypeName, 'helvetica', 'B', 16);
$titleWidth = \is_array($titleWidth) ? \array_sum($titleWidth) : $titleWidth;

$pdf->setXY(
    $rightPos = ($pageWidth - $titleWidth - \max(60 - $titleWidth, 0) - 15 - 2),
    $topPos + 50 + $lineHeight * $addressLineCount - 38,
    true
);

$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(255, 162, 7);
$pdf->Cell($pageWidth - $rightPos - 15, 0, $billTypeName, 0, 0, 'L', true);

$pdf->setFont('helvetica', '', 10);
$pdf->setTextColor(255, 162, 7);

$pdf->setXY($rightPos, $tempY = $pdf->getY() + 10, true);
$pdf->MultiCell(
    26, 30,
    $lang[$pdf->language]['InvoiceNo'] . "\n"
    . $lang[$pdf->language]['InvoiceDate'] . "\n"
    . $lang[$pdf->language]['ServiceDate'] . "\n"
    . $lang[$pdf->language]['CustomerNo'] . "\n"
    . $lang[$pdf->language]['PO'] . "\n"
    . $lang[$pdf->language]['DueDate'],
    0, 'L'
);

//$pdf->setFont('helvetica', '', 10);
$pdf->setTextColor(0, 0, 0);

$pdf->setXY($rightPos + 26 + 2, $tempY, true);
$pdf->MultiCell(
    25, 30,
    $bill->number . "\n"
    . ($bill->billDate?->format('Y-m-d') ?? '0') . "\n"
    . ($bill->performanceDate?->format('Y-m-d') ?? '0') . "\n"
    . $bill->accountNumber . "\n"
    . '' . "\n" /* @todo implement customer / supplier reference as string */
    .  ($bill->billDate?->format('Y-m-d') ?? '0'), /* Consider to add dueDate in addition */
    0, 'L'
);
$pdf->Ln();

$pdf->setY($pdf->getY() - 20);

$header = [
    $lang[$pdf->language]['Item'],
    $lang[$pdf->language]['Quantity'],
    $lang[$pdf->language]['UnitPrice'],
    $lang[$pdf->language]['Total'],
];

$lines = $bill->elements;

// Header
$headerCount = \count($header);
$w           = [$pageWidth - 20 - 20 - 20 - 2 * 15, 20, 20, 20];

$pdf->setCellPadding(1);

$taxes = [];
$first = true;

// Data
$fill = false;
foreach($lines as $line) {
    // @todo depending on amount of lines, there is a solution (html, or use backtracking of tcpdf)
    if ($first || $pdf->getY() > $pageHeight - 40) {
        $pdf->setFillColor(255, 162, 7);
        $pdf->setTextColor(255);
        $pdf->setDrawColor(255, 162, 7);
        //$pdf->SetLineWidth(0.3);
        $pdf->setFont('helvetica', 'B', 10);

        if (!$first/* || $row === null*/) {
            $pdf->AddPage();
            $pdf->Ln();
        }

        for($i = 0; $i < $headerCount; ++$i) {
            $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'L', true);
        }

        $pdf->Ln();
        $pdf->setFillColor(245, 245, 245);
        $pdf->setTextColor(0);
        $pdf->setFont('helvetica', '', 10);

        $first = false;
    }

    // Discounts are shown below the original price -> additional line
    // We don't want discount columns because that hints at customers they might be able to get discounts.
    $lines = 1
        + ((int) ($line->discountQ->value > 0))
        + ((int) ($line->singleDiscountP->value > 0))
        + ((int) ($line->singleDiscountR->value > 0));

    $tempY = $pdf->getY();
    //$pdf->writeHTMLCell($w[0], 10, null, null, $line->itemNumber . ' ' . $line->itemName, 0, 2, $fill);
    $pdf->MultiCell($w[0], 10 * $lines, \trim($line->itemNumber . ' ' . $line->itemName), 0, 'L', $fill, 2, null, null, true, 0, true, true, 0, 'M', false);
    $height = $pdf->getY() - $tempY;

    $singleListPriceNet = Money::fromFloatInt($line->singleListPriceNet);
    $totalSalesPriceNet = Money::fromFloatInt($line->totalSalesPriceNet);

    if ($line->quantity->value === 0) {
        $pdf->MultiCell($w[1] + $w[2] + $w[3], $height, '', 0, 'L', $fill, 0, 15 + $w[0], $tempY, true, 0, false, true, 0, 'M', true);
    } else {
        $pdf->MultiCell($w[1], $height, (string) $line->quantity->getAmount($line->container->quantityDecimals), 0, 'L', $fill, 0, 15 + $w[0], $tempY, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell($w[2], $height, $singleListPriceNet->getCurrency(2, symbol: ''), 0, 'L', $fill, 0, 15 + $w[0] + $w[1], $tempY, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell($w[3], $height, $totalSalesPriceNet->getCurrency(2, symbol: ''), 0, 'L', $fill, 1, 15 + $w[0] + $w[1] + $w[2], $tempY, true, 0, false, true, 0, 'M', true);
    }

    $fill = !$fill;

    // get taxes
    if (!isset($taxes[$line->taxR->value / 10000])) {
        $taxes[$line->taxR->value / 10000] = $line->taxP;
    } else {
        $taxes[$line->taxR->value / 10000]->add($line->taxP);
    }
}

$pdf->Cell(\array_sum($w), 0, '', 'T');
$pdf->Ln();

if ($pdf->getY() > $pageHeight - 40) {
    $pdf->AddPage();
}

$pdf->setFillColor(240, 240, 240);
$pdf->setTextColor(0);
$pdf->setDrawColor(240, 240, 240);
$pdf->setFont('helvetica', 'B', 10);

$tempY = $pdf->getY();

$netSales = Money::fromFloatInt($bill->netSales);

$pdf->setX($w[0] + $w[1] + 12);
$pdf->Cell($w[2], 7, $lang[$pdf->language]['Subtotal'], 0, 0, 'L', false);
$pdf->Cell($w[3], 7, $netSales->getCurrency(2, symbol: ''), 0, 0, 'L', false);
$pdf->Ln();

foreach ($taxes as $rate => $tax) {
    $tax = Money::fromFloatInt($tax);

    $pdf->setX($w[0] + $w[1] + 12);
    $pdf->Cell($w[2], 7,  $lang[$pdf->language]['Taxes'] . ' (' . $rate . '%)', 0, 0, 'L', false);
    $pdf->Cell($w[3], 7, $tax->getCurrency(2, symbol: ''), 0, 0, 'L', false);
    $pdf->Ln();
}

$pdf->setFillColor(255, 162, 7);
$pdf->setTextColor(255);
$pdf->setDrawColor(255, 162, 7);
//$pdf->setFont('helvetica', 'B', 10);

$grossSales = Money::fromFloatInt($bill->grossSales);

$pdf->setX($w[0] + $w[1] + 12);
$pdf->Cell($w[2], 7, \strtoupper($lang[$pdf->language]['Total']), 1, 0, 'L', true);
$pdf->Cell($w[3] + 3, 7,  $grossSales->getCurrency(2, symbol: ''), 1, 0, 'L', true);
$pdf->Ln();

$tempY2 = $pdf->getY();

// @todo fix payment terms
$pdf->setTextColor(0);
$pdf->setFont('helvetica', 'B', 8);
$pdf->setY($tempY);
$pdf->Write(0, $lang[$pdf->language]['PaymentTerms'] . ': CreditCard', '', false, 'L', false, 0, false, false, 0);

$pdf->setFont('helvetica', '', 8);
$pdf->Write(0, $bill->paymentText, '', false, 'L', false, 0, false, false, 0);
$pdf->Ln();

// @todo fix terms
$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['Terms'] . ': ' . $pdf->attributes['terms'], '', false, 'L', false, 0, false, false, 0);
$pdf->Ln();

//$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['Currency'] . ': ' . $bill->currency, '', false, 'L', false, 0, false, false, 0);
$pdf->Ln();

//$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['TaxRemark'], '', false, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->setFont('helvetica', '', 8);
$pdf->Write(0, $bill->termsText, '', false, 'L', false, 0, false, false, 0);
//$pdf->Ln();

//$pdf->setY($tempY2);
//$pdf->Ln();

//Close and output PDF document
$path = (string) ($this->data['path'] ?? (($bill->billDate?->format('Y-m-d') ?? '0') . '_' . $bill->number . '.pdf'));
$pdf->Output($path, 'I');
