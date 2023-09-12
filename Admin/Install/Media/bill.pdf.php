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
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\Money;

/** @var \phpOMS\Views\View $this */
require_once $this->getData('defaultTemplates')
    ->findFile('.pdf.php')
    ->getAbsolutePath();

/** @var \Modules\Billing\Models\Bill $bill */
$bill = $this->data['bill'] ?? new NullBill();

// Set up default pdf template
/** @phpstan-import-type DefaultPdf from ../../../../Admin/Install/Media/PdfDefaultTemplate/pdfTemplate.pdf.php */
$pdf = new DefaultPdf();

$lang = include __DIR__ . '/lang.php';

$pdf->setHeaderData(
    __DIR__ . '/logo.png', 15,
    $this->data['bill_logo_name'] ?? 'Jingga',
    $this->data['bill_slogan'] ?? 'Business solutions made simple.'
);
$pdf->setCreator($this->data['bill_creator'] ?? 'Jingga');
$pdf->setAuthor($this->data['bill_creator'] ?? 'Jingga');
$pdf->setTitle($this->data['bill_title'] ?? $bill->type->getL11n());
$pdf->setSubject($this->data['bill_subtitle'] ?? '');
$pdf->setKeywords(\implode(', ', $this->data['keywords'] ?? []));
$pdf->language = $bill->language;

$pdf->attributes['legal_name'] = $this->data['legal_company_name'] ?? 'Jingga e. K.';
$pdf->attributes['address']    = $this->data['bill_company_address'] ?? 'Gartenstr. 26';
$pdf->attributes['city']       = $this->data['bill_company_city'] ?? '61206 Wöllstadt';

$pdf->attributes['ceo']        = $this->data['bill_company_ceo'] ?? 'Dennis Eichhorn';
$pdf->attributes['tax_office'] = $this->data['bill_company_tax_office'] ?? 'HRA 5058';
$pdf->attributes['tax_number'] = $this->data['bill_company_tax_id'] ?? '16 814 6253 3';
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

// @todo: depending on amount of lines, there is a solution (html, or use backtracking of tcpdf)

// Address
$pdf->setY(50);
$pdf->setFont('helvetica', '', 10);

$countries       = ISO3166NameEnum::getConstants();
$countryEnumName = ISO3166TwoEnum::getName($bill->billCountry);
$toCountry       = \is_string($countryEnumName) && ($country = ISO3166NameEnum::getByName($countryEnumName)) !== null
    ? $country
    : '';

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
    '', 0, 'L', false, 0, false, false, 0
);
$lineHeight = ($lineHeight - $pdf->getY()) / $addressLineCount;

// Bill head
$pdf->setFont('helvetica', 'B', 16);
$titleWidth = $pdf->getStringWidth($billTypeName, 'helvetica', 'B', 16);

$pdf->setXY(
    $rightPos = ($pdf->getPageWidth() - $titleWidth - \max(60 - $titleWidth, 0) - 15 - 2),
    $topPos + 50 + $lineHeight * $addressLineCount - 38,
    true
);

$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(255, 162, 7);
$pdf->Cell($pdf->getPageWidth() - $rightPos - 15, 0, $billTypeName, 0, 0, 'L', true);

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

$pdf->setFont('helvetica', '', 10);
$pdf->setTextColor(0, 0, 0);

$pdf->setXY($rightPos + 26 + 2, $tempY, true);
$pdf->MultiCell(
    25, 30,
    $bill->number . "\n"
    . ($bill->billDate?->format('Y-m-d') ?? '0') . "\n"
    . ($bill->performanceDate?->format('Y-m-d') ?? '0') . "\n"
    . $bill->accountNumber . "\n"
    . '' . "\n" /* @todo: implement customer / supplier reference as string */
    .  ($bill->billDate?->format('Y-m-d') ?? '0'), /* Consider to add dueDate in addition */
    0, 'L'
);
$pdf->Ln();

$pdf->setY($pdf->getY() - 30);

/*
$pdf->writeHTMLCell(
    $pdf->getPageWidth() - 15 * 2, 0, null, null,
    "<strong>Lorem ipsum dolor sit amet,</strong><br \><br \>Consectetur adipiscing elit. Vivamus ac massa sit amet eros posuere accumsan feugiat vel est. Maecenas ultricies enim eu eros rhoncus, volutpat cursus enim imperdiet. Aliquam et odio ipsum. Quisque dapibus scelerisque tempor. Phasellus purus lorem, venenatis eget pretium ac, convallis et ante. Aenean pulvinar justo consectetur mi tincidunt venenatis. Suspendisse ultricies enim id nulla facilisis lacinia. <br /><br />Nam congue nunc nunc, eu pellentesque eros aliquam ac. Nunc placerat elementum turpis, quis facilisis diam volutpat at. Suspendisse enim leo, convallis nec ornare eu, auctor nec purus. Nunc neque metus, feugiat quis justo nec, mollis dignissim risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In at ornare sem. Cras placerat, sapien sed ornare lacinia, mauris nulla volutpat nisl, eget dapibus nisl ipsum non est. Suspendisse ut nisl a ipsum rhoncus sodales.",
    0, 0, false, true, 'J'
);
$pdf->Ln();
*/

$pdf->setY($pdf->getY() + 10);

$header = [
    $lang[$pdf->language]['Item'],
    $lang[$pdf->language]['Quantity'],
    $lang[$pdf->language]['UnitPrice'],
    $lang[$pdf->language]['Total'],
];

$lines = $bill->getElements();

// Header
$headerCount = \count($header);
$w           = [$pdf->getPageWidth() - 20 - 20 - 20 - 2 * 15, 20, 20, 20];

$pdf->setCellPadding(1, 1, 1, 1);

$taxes = [];
$first = true;

// Data
$fill = false;
foreach($lines as $line) {
    // @todo: add support for empty lines (row = line)
    if (/*$row === null || */$first || $pdf->getY() > $pdf->getPageHeight() - 40) {
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

    $tempY = $pdf->getY();
    $pdf->writeHTMLCell($w[0], 10, null, null, $line->itemNumber . ' ' . $line->itemName, 0, 2, $fill);
    $height = $pdf->getY() - $tempY;

    $singleSalesPriceNet = Money::fromFloatInt($line->singleSalesPriceNet);
    $totalSalesPriceNet  = Money::fromFloatInt($line->totalSalesPriceNet);

    $pdf->MultiCell($w[1], $height, (string) $line->getQuantity(), 0, 'L', $fill, 0, 15 + $w[0], $tempY, true, 0, false, true, 0, 'M', true);
    $pdf->MultiCell($w[2], $height, $singleSalesPriceNet->getCurrency(2, symbol: ''), 0, 'L', $fill, 0, 15 + $w[0] + $w[1], $tempY, true, 0, false, true, 0, 'M', true);
    $pdf->MultiCell($w[3], $height, $totalSalesPriceNet->getCurrency(2, symbol: ''), 0, 'L', $fill, 1, 15 + $w[0] + $w[1] + $w[2], $tempY, true, 0, false, true, 0, 'M', true);

    $fill = !$fill;

    // get taxes
    if (!isset($taxes[$line->taxR->getInt() / 100])) {
        $taxes[$line->taxR->getInt() / 100] = $line->taxP;
    } else {
        $taxes[$line->taxR->getInt() / 100]->add($line->taxP);
    }
}

$pdf->Cell(\array_sum($w), 0, '', 'T');
$pdf->Ln();

if ($pdf->getY() > $pdf->getPageHeight() - 40) {
    $pdf->AddPage();
}

$pdf->setFillColor(240, 240, 240);
$pdf->setTextColor(0);
$pdf->setDrawColor(240, 240, 240);
$pdf->setFont('helvetica', 'B', 10);

$tempY = $pdf->getY();

$netSales = Money::fromFloatInt($bill->netSales);

$pdf->setX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, $lang[$pdf->language]['Subtotal'], 0, 0, 'L', false);
$pdf->Cell($w[3], 7, $netSales->getCurrency(2, symbol: ''), 0, 0, 'L', false);
$pdf->Ln();

foreach ($taxes as $rate => $tax) {
    $tax = Money::fromFloatInt($tax);

    $pdf->setX($w[0] + $w[1] + 15);
    $pdf->Cell($w[2], 7,  $lang[$pdf->language]['Taxes'] . ' (' . $rate . '%)', 0, 0, 'L', false);
    $pdf->Cell($w[3], 7, $tax->getCurrency(2, symbol: ''), 0, 0, 'L', false);
    $pdf->Ln();
}

// @todo: add currency

$pdf->setFillColor(255, 162, 7);
$pdf->setTextColor(255);
$pdf->setDrawColor(255, 162, 7);
$pdf->setFont('helvetica', 'B', 10);

$grossSales = Money::fromFloatInt($bill->grossSales);

$pdf->setX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, \strtoupper($lang[$pdf->language]['Total']), 1, 0, 'L', true);
$pdf->Cell($w[3], 7,  $grossSales->getCurrency(2, symbol: ''), 1, 0, 'L', true);
$pdf->Ln();

$tempY2 = $pdf->getY();

// @todo: fix payment terms
$pdf->setTextColor(0);
$pdf->setFont('helvetica', 'B', 8);
$pdf->setY($tempY);
$pdf->Write(0, $lang[$pdf->language]['PaymentTerms'] . ': CreditCard', '', 0, 'L', false, 0, false, false, 0);

$pdf->setFont('helvetica', '', 8);
$pdf->Write(0, $bill->paymentText, '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

// @todo: fix terms
$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['Terms'] . ': ' . $pdf->attributes['terms'], '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['Currency'] . ': ' . $bill->currency, '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->setFont('helvetica', 'B', 8);
$pdf->Write(0, $lang[$pdf->language]['TaxRemark'], '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->setFont('helvetica', '', 8);
$pdf->Write(0, $bill->termsText, '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->setY($tempY2);
$pdf->Ln();

/*
$pdf->writeHTMLCell(
    $pdf->getPageWidth() - 15 * 2, 0, null, null,
    "Consectetur adipiscing elit. Vivamus ac massa sit amet eros posuere accumsan feugiat vel est. Maecenas ultricies enim eu eros rhoncus, volutpat cursus enim imperdiet. Aliquam et odio ipsum. Quisque dapibus scelerisque tempor. Phasellus purus lorem, venenatis eget pretium ac, convallis et ante. Aenean pulvinar justo consectetur mi tincidunt venenatis. Suspendisse ultricies enim id nulla facilisis lacinia. Nam congue nunc nunc, eu pellentesque eros aliquam ac.<br /><br />Nunc placerat elementum turpis, quis facilisis diam volutpat at. Suspendisse enim leo, convallis nec ornare eu, auctor nec purus. Nunc neque metus, feugiat quis justo nec, mollis dignissim risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In at ornare sem. Cras placerat, sapien sed ornare lacinia, mauris nulla volutpat nisl, eget dapibus nisl ipsum non est. Suspendisse ut nisl a ipsum rhoncus sodales.",
    0, 0, false, true, 'J'
);
$pdf->Ln();
*/

//Close and output PDF document
$pdf->Output(
    $this->data['path'] ?? (($bill->billDate?->format('Y-m-d') ?? '0') . '_' . $bill->number . '.pdf'),
    'I'
);
