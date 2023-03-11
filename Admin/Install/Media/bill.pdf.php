<?php

declare(strict_types=1);

require_once $this->getData('defaultTemplates')
	->findFile('.pdf.php')
	->getAbsolutePath();

$pdf = new DefaultPdf('P', 'mm', 'A4', true, 'UTF-8', false);

$creator = $this->getData('bill_creator') ?? 'Jingga';
$author  = 'Jingga';
$title   = $this->getData('bill_title') ?? 'Invoice';
$subtitle = $this->getData('bill_subtitle') ?? 'Sub title';
$keywords = $this->getData('keywords') ?? [];
$logoName = $this->getData('bill_logo_name') ?? 'Jingga';
$slogan   = $this->getData('bill_slogan') ?? 'Business solutions made simple.';

$legalCompanyName = $this->getData('legal_company_name') ?? 'Jingga e.K.';
$companyAddress = $this->getData('bill_company_address') ?? 'Gartenstr. 26';
$companyCity = $this->getData('bill_company_city') ?? '61206 Woellstadt';
$companyCEO = $this->getData('bill_company_ceo') ?? 'Dennis Eichhorn';
$companyWebsite = $this->getData('bill_company_website') ?? 'www.jingga.app';
$companyEmail = $this->getData('bill_company_email') ?? 'info@jingga.app';
$companyPhone = $this->getData('bill_company_phone') ?? '+49 0152 ????';

$taxOffice = $this->getData('bill_company_tax_office') ?? 'HRB';
$taxId = $this->getData('bill_company_tax_id') ?? 'DE ?????????';
$vatId = $this->getData('bill_company_vat_id') ?? 'DE ??????';

$bankName = $this->getData('bill_company_bank_name') ?? 'Volksbank Mittelhessen';
$bic = $this->getData('bill_company_bic') ?? '';
$iban = $this->getData('bill_company_iban') ?? '';

$billTypeName = $this->getData('bill_type_name') ?? 'INVOICE';

$billInvoiceNumber = $this->getData('bill_invoice_no') ?? '';
$billInvoiceDate = $this->getData('bill_invoice_date') ?? '';
$billServiceDate = $this->getData('bill_service_date') ?? '';
$billCustomerNo = $this->getData('bill_customer_no') ?? '';
$billPO = $this->getData('bill_po') ?? '';
$billDueDate = $this->getData('bill_due_date') ?? '';

$invoiceLines = $this->getData('bill_lines') ?? [];

$paymentTerms = $this->getData('bill_payment_terms') ?? '';
$terms = $this->getData('bill_terms') ?? 'https://jingga.app/terms';
$taxes = $this->getData('bill_taxes') ?? ['19%' => '0.00'];
$currency = $this->getData('bill_currency') ?? 'EUR';

// set document information
$pdf->SetCreator($creator);
$pdf->SetAuthor($author);
$pdf->SetTitle($title);
$pdf->SetSubject($subtitle);
$pdf->SetKeywords(\implode(', ', $keywords));

// set image scale factor
$pdf->SetImageScale(PDF_IMAGE_SCALE_RATIO);

$topPos = $pdf->getY();

// Address
$pdf->SetY(55); // @todo: depending on amount of lines
$pdf->SetFont('helvetica', '', 8);

$lineHeight = $pdf->getY();
$pdf->Write(0, "Dennis Eichhorn\nGartenstr. 26\n61206 Woellstadt", '', 0, 'L', false, 0, false, false, 0);
$lineHeight = ($lineHeight - $pdf->getY()) / 3;

// Document head
$pdf->SetFont('helvetica', 'B', 20);
$titleWidth = $pdf->GetStringWidth($billTypeName, 'helvetica', 'B', 20);

$pdf->SetXY(
	$rightPos = ($pdf->getPageWidth() - $titleWidth - ($titleWidth < 55 ? 55 : 35) + 15),
	$topPos + 50 + $lineHeight * 3 - 38,
	true
);

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(255, 162, 7);
$pdf->Cell($pdf->getPageWidth() - $rightPos - 15, 0, $billTypeName, 0, 0, 'L', true);

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(255, 162, 7);

$pdf->SetXY($rightPos, $tempY = $pdf->getY() + 10, true);
$pdf->MultiCell(23, 30, "Invoice No\nInvoice Date\nService Date\nCustomer No\nPO\nDue Date", 0, 'L');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY($rightPos + 23 + 2, $tempY, true);
$pdf->MultiCell(25, 30, "2022-123456\nYYYY-MM-DD\nYYYY-MM-DD\n123-456-789\n2022-123456\nYYYY-MM-DD", 0, 'L');
$pdf->Ln();

$pdf->SetY($pdf->GetY() - 30);
$pdf->writeHTMLCell(
	$pdf->getPageWidth() - 15 * 2, 0, null, null,
	"<strong>Lorem ipsum dolor sit amet,</strong><br \><br \>Consectetur adipiscing elit. Vivamus ac massa sit amet eros posuere accumsan feugiat vel est. Maecenas ultricies enim eu eros rhoncus, volutpat cursus enim imperdiet. Aliquam et odio ipsum. Quisque dapibus scelerisque tempor. Phasellus purus lorem, venenatis eget pretium ac, convallis et ante. Aenean pulvinar justo consectetur mi tincidunt venenatis. Suspendisse ultricies enim id nulla facilisis lacinia. <br /><br />Nam congue nunc nunc, eu pellentesque eros aliquam ac. Nunc placerat elementum turpis, quis facilisis diam volutpat at. Suspendisse enim leo, convallis nec ornare eu, auctor nec purus. Nunc neque metus, feugiat quis justo nec, mollis dignissim risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In at ornare sem. Cras placerat, sapien sed ornare lacinia, mauris nulla volutpat nisl, eget dapibus nisl ipsum non est. Suspendisse ut nisl a ipsum rhoncus sodales.",
	0, 0, false, true, 'J'
);
$pdf->Ln();

$pdf->SetY($pdf->GetY() + 5);

$header = ['Item', 'Quantity', 'Rate', 'Total'];
$data = [
	['ASDF', 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer.</span>", 2.0, "199.90\n-10 %", "150.399.80\n-15.039"],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains. Here we are testing how it looks like if a very long text is posted in the description without any additional line breaks. It should auto-break!</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	["123-456-789<br><strong>This is a item name</strong><br><span style=\"color: #444;\">This is the item description in more detail for the customer so he knows what this content actually contains.</span>", 2.0, 199.90, 399.80],
	['ASDF', 2.0, 199.90, 399.80],
];

// Header
$w = array($pdf->getPageWidth() - 20 - 20 - 20 - 2*15, 20, 20, 20);
$num_headers = count($header);

$pdf->setCellPadding(1, 1, 1, 1);

$first = true;

// Data
$fill = false;
foreach($data as $row) {
	if ($row === null || $first || $pdf->getY() > $pdf->getPageHeight() - 40) {
		$pdf->SetFillColor(255, 162, 7);
		$pdf->SetTextColor(255);
		$pdf->SetDrawColor(255, 162, 7);
		//$pdf->SetLineWidth(0.3);
		$pdf->SetFont('helvetica', 'B', 8);

		if (!$first || $row === null) {
			$pdf->AddPage();
			$pdf->Ln();
		}

		for($i = 0; $i < $num_headers; ++$i) {
		    $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'L', true);
		}

		$pdf->Ln();
		$pdf->SetFillColor(245, 245, 245);
		$pdf->SetTextColor(0);
		$pdf->SetFont('helvetica', '', 8);

		$first = false;
	}

	$tempY = $pdf->getY();
    $pdf->writeHTMLCell($w[0], 10, null, null, $row[0], 0, 2, $fill);
    $height = $pdf->getY() - $tempY;

    /*
    $pdf->writeHTMLCell($w[1], $height, 15 + $w[0], $tempY, $row[1], 0, 0, $fill);
    $pdf->writeHTMLCell($w[2], $height, 15 + $w[0] + $w[1], $tempY, $row[2], 0, 0, $fill);
    $pdf->writeHTMLCell($w[3], $height, 15 + $w[0] + $w[1] + $w[2], $tempY, $row[3], 0, 1, $fill);
	*/

    $pdf->MultiCell($w[1], $height, $row[1], 0, 'L', $fill, 0, 15 + $w[0], $tempY, true, 0, false, true, 0, 'M', true);
    $pdf->MultiCell($w[2], $height, $row[2], 0, 'L', $fill, 0, 15 + $w[0] + $w[1], $tempY, true, 0, false, true, 0, 'M', true);
    $pdf->MultiCell($w[3], $height, $row[3], 0, 'L', $fill, 1, 15 + $w[0] + $w[1] + $w[2], $tempY, true, 0, false, true, 0, 'M', true);

    $fill = !$fill;
}

$pdf->Cell(\array_sum($w), 0, '', 'T');
$pdf->Ln();

if ($pdf->getY() > $pdf->getPageHeight() - 40) {
	$pdf->AddPage();
}

$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0);
$pdf->SetDrawColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 8);

$tempY = $pdf->GetY();

$pdf->SetX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, 'Subtotal', 0, 0, 'L', false);
$pdf->Cell($w[3], 7, '40.000', 0, 0, 'L', false);
$pdf->Ln();
$pdf->SetX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, 'Taxes (0%)', 0, 0, 'L', false);
$pdf->Cell($w[3], 7, '40.000', 0, 0, 'L', false);
$pdf->Ln();
$pdf->SetX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, 'Taxes (16%)', 0, 0, 'L', false);
$pdf->Cell($w[3], 7, '40.000', 0, 0, 'L', false);
$pdf->Ln();
$pdf->SetX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, 'Taxes (19%)', 0, 0, 'L', false);
$pdf->Cell($w[3], 7, '40.000', 0, 0, 'L', false);
$pdf->Ln();

$pdf->SetFillColor(255, 162, 7);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(255, 162, 7);
$pdf->SetFont('helvetica', 'B', 8);

$pdf->SetX($w[0] + $w[1] + 15);
$pdf->Cell($w[2], 7, 'TOTAL', 1, 0, 'L', true);
$pdf->Cell($w[3], 7, '40.000', 1, 0, 'L', true);
$pdf->Ln();

$tempY2 = $pdf->getY();

$pdf->SetTextColor(0);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetY($tempY);
$pdf->Write(0, 'Payment Terms: ', '', 0, 'L', false, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 8);
$pdf->Write(0, 'Payment within 30 business days', '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->SetFont('helvetica', 'B', 8);
$pdf->Write(0, 'Terms: ', '', 0, 'L', false, 0, false, false, 0);

$pdf->SetFont('helvetica', '', 8);
$pdf->Write(0, 'https://jingga.app/terms', '', 0, 'L', false, 0, false, false, 0);
$pdf->Ln();

$pdf->SetY($tempY2);
$pdf->Ln();

// $pdf->SetY($pdf->GetY() - 30);
$pdf->writeHTMLCell(
	$pdf->getPageWidth() - 15 * 2, 0, null, null,
	"Consectetur adipiscing elit. Vivamus ac massa sit amet eros posuere accumsan feugiat vel est. Maecenas ultricies enim eu eros rhoncus, volutpat cursus enim imperdiet. Aliquam et odio ipsum. Quisque dapibus scelerisque tempor. Phasellus purus lorem, venenatis eget pretium ac, convallis et ante. Aenean pulvinar justo consectetur mi tincidunt venenatis. Suspendisse ultricies enim id nulla facilisis lacinia. Nam congue nunc nunc, eu pellentesque eros aliquam ac.<br /><br />Nunc placerat elementum turpis, quis facilisis diam volutpat at. Suspendisse enim leo, convallis nec ornare eu, auctor nec purus. Nunc neque metus, feugiat quis justo nec, mollis dignissim risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. In at ornare sem. Cras placerat, sapien sed ornare lacinia, mauris nulla volutpat nisl, eget dapibus nisl ipsum non est. Suspendisse ut nisl a ipsum rhoncus sodales.",
	0, 0, false, true, 'J'
);
$pdf->Ln();

//Close and output PDF document
$pdf->Output('example_048.pdf', 'I');