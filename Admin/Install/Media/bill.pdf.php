<?php

declare(strict_types=1);

class MYPDF extends TCPDF
{
    public string $fontName = '';

    public int $fontSize = 8;

    public int $sideMargin = 15;

    //Page header
    public function Header() : void {
        if ($this->header_xobjid === false) {
            $this->header_xobjid = $this->startTemplate($this->w, 0);

            // Set Logo
            $image_file = __DIR__ . '/logo.png';
            $this->Image($image_file, 15, 15, 15, 15, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

            // Set Title
            $this->SetFont('helvetica', 'B', 20);
            $this->setX(15 + 15 + 3);
            $this->Cell(0, 14, $this->header_title, 0, false, 'L', 0, '', 0, false, 'T', 'M');

            $this->SetFont('helvetica', '', 10);
            $this->setX(15 + 15 + 3);
            $this->Cell(0, 26, $this->header_string, 0, false, 'L', 0, '', 0, false, 'T', 'M');

            $this->endTemplate();
        }

        $x  = 0;
        $dx = 0;

        if (!$this->header_xobj_autoreset && $this->booklet && (($this->page % 2) == 0)) {
            // adjust margins for booklet mode
            $dx = ($this->original_lMargin - $this->original_rMargin);
        }

        if ($this->rtl) {
            $x = $this->w + $dx;
        } else {
            $x = 0 + $dx;
        }

        $this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
        if ($this->header_xobj_autoreset) {
            // reset header xobject template at each page
            $this->header_xobjid = false;
        }
    }

    // Page footer
    public function Footer() : void {
        $this->SetY(-25);

        $this->SetFont('helvetica', 'I', 7);
        $this->Cell($this->getPageWidth() - 22, 0, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->Ln();
        $this->Ln();

        $this->SetFillColor(245, 245, 245);
        $this->SetX(0);
        $this->Cell($this->getPageWidth(), 25, '', 0, 0, 'L', true, '', 0, false, 'T', 'T');

        $this->SetFont('helvetica', '', 7);
        $this->SetXY(15 + 10, -15, true);
        $this->MultiCell(30, 0, "Jingga e.K.\nGartenstr. 26\n61206 Woellstadt", 0, 'L', false, 1, null, null, true, 0, false, true, 0, 'B');

        $this->SetXY(25 + 15 + 20, -15, true);
        $this->MultiCell(40, 0, "Geschäftsführer: Dennis Eichhorn\nFinanzamt: HRB ???\nUSt Id: DE ??????????", 0, 'L', false, 1, null, null, true, 0, false, true, 0, 'B');

        $this->SetXY(25 + 45 + 15 + 30, -15, true);
        $this->MultiCell(35, 0, "Volksbank Mittelhessen\nBIC: ??????????\nIBAN: ???????????", 0, 'L', false, 1, null, null, true, 0, false, true, 0, 'B');

        $this->SetXY(25 + 45 + 35 + 15 + 40, -15, true);
        $this->MultiCell(35, 0, "www.jingga.app\ninfo@jingga.app\n+49 0152 ???????", 0, 'L', false, 1, null, null, true, 0, false, true, 0, 'B');
    }
}

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// set document information
$pdf->SetCreator("Jingga");
$pdf->SetAuthor('Jingga');
$pdf->SetTitle('Invoice');
$pdf->SetSubject('Sub title');
$pdf->SetKeywords('Invoice, 2022');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Jingga', 'Business solutions made simple.');

// set header and footer fonts
$pdf->SetHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->SetFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(15, 30, 15);

// set auto page breaks
$pdf->SetAutoPageBreak(true, 25);

// set image scale factor
$pdf->SetImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();

$topPos = $pdf->getY();

// Address
$pdf->SetY(55); // @todo: depending on amount of lines
$pdf->SetFont('helvetica', '', 8);

$lineHeight = $pdf->getY();
$pdf->Write(0, "Dennis Eichhorn\nGartenstr. 26\n61206 Woellstadt", '', 0, 'L', false, 0, false, false, 0);
$lineHeight = ($lineHeight - $pdf->getY()) / 3;

// Document head
$pdf->SetFont('helvetica', 'B', 20);
$title      = 'INVOICE';
$titleWidth = $pdf->GetStringWidth($title, 'helvetica', 'B', 20);

$pdf->SetXY(
    $rightPos = ($pdf->getPageWidth() - $titleWidth - ($titleWidth < 55 ? 55 : 35) + 15),
    $topPos + 50 + $lineHeight * 3 - 38,
    true
);

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFillColor(255, 162, 7);
$pdf->Cell($pdf->getPageWidth() - $rightPos - 15, 0, $title, 0, 0, 'L', true);

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
$data   = [
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
$w           = [$pdf->getPageWidth() - 20 - 20 - 20 - 2 * 15, 20, 20, 20];
$num_headers = \count($header);

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
