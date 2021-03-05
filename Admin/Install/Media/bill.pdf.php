<?php declare(strict_types=1);

use Mpdf\Mpdf;

/**
 * @var \phpOMS\Views\View $this
 */
$bill = $this->getData('bill');
$elements = $bill->getElements();

$mpdf = new Mpdf([
    'mode'        => 'utf-8',
    'format'      => 'A4-L',
    'orientation' => 'L',
    'margin_left' => 20,
	'margin_right' => 15,
	'margin_top' => 48,
	'margin_bottom' => 25,
	'margin_header' => 10,
	'margin_footer' => 10
]);

$mpdf->SetDisplayMode('fullpage');
$mpdf->SetTitle($bill->getNumber());
$mpdf->SetAuthor('Orange Management');

$html = '
<html>
<head>
	<style>
	body {
		font-family: sans-serif;
		font-size: 10pt;
	}
	p {	margin: 0pt; }
	table.items {
		border: 0.1mm solid #000000;
	}
	td { vertical-align: top; }
	.items td {
		border-left: 0.1mm solid #000000;
		border-right: 0.1mm solid #000000;
	}
	table thead td {
		background-color: #EEEEEE;
		text-align: center;
		border: 0.1mm solid #000000;
		font-variant: small-caps;
	}
	.items td.blanktotal {
		background-color: #EEEEEE;
		border: 0.1mm solid #000000;
		background-color: #FFFFFF;
		border: 0mm none #000000;
		border-top: 0.1mm solid #000000;
		border-right: 0.1mm solid #000000;
	}
	.items td.totals {
		text-align: right;
		border: 0.1mm solid #000000;
	}
	.items td.cost {
		text-align: "." center;
	}
	</style>
</head>
<body>
<!--mpdf
<htmlpageheader name="myheader">
	<table width="100%">
		<tr>
			<td width="50%" style="color:#0000BB; ">
				<span style="font-weight: bold; font-size: 14pt;">Orange Management</span><br />
				123 1313 Webfoot Street<br />
				Duckburg<br />
			</td>
			<td width="50%" style="text-align: right;">
				Invoice No.<br />
				<span style="font-weight: bold; font-size: 12pt;">' . $bill->getNumber() . '</span>
			</td>
		</tr>
	</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
	<div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
		Page {PAGENO} of {nb}
	</div>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<div style="text-align: right">Date: 13th November 2008</div>

<table width="100%" style="font-family: serif;" cellpadding="10">
	<tr>
		<td width="45%" style="border: 0.1mm solid #888888; ">
			<span style="font-size: 7pt; color: #555555; font-family: sans;">SOLD TO:</span><br /><br />
			345 Anotherstreet<br />
			Little Village<br />
			Their City<br />
			CB22 6SO
		</td>
		<td width="10%">&nbsp;</td>
		<td width="45%" style="border: 0.1mm solid #888888;">
			<span style="font-size: 7pt; color: #555555; font-family: sans;">SHIP TO:</span><br /><br />
			345 Anotherstreet<br />
			Little Village<br />
			Their City<br />CB22 6SO</td>
	</tr>
</table>
<br />

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
	<thead>
		<tr>
			<td width="15%">Ref. No.</td>
			<td width="10%">Quantity</td>
			<td width="45%">Description</td>
			<td width="15%">Unit Price</td>
			<td width="15%">Amount</td>
		</tr>
	</thead>
<tbody>';

foreach ($elements as $element) {
$html .= '
		<tr>
			<td align="center">' . $element->itemNumber . '</td>
			<td align="center">' . $element->quantity . '</td>
			<td>' . $element->itemName . '</td>
			<td class="cost">' . $element->singleSalesPriceNet->getCurrency(null) . '</td>
			<td class="cost">' . $element->totalSalesPriceNet->getCurrency(null) . '</td>
		</tr>';
}

$html .= '
		<tr>
			<td class="blanktotal" colspan="3" rowspan="6"></td>
			<td class="totals">Subtotal:</td>
			<td class="totals cost">' . $bill->net->getCurrency(null) . '</td>
		</tr>
		<tr>
			<td class="totals">Tax:</td>
			<td class="totals cost">&pound;18.25</td>
		</tr>
		<tr>
			<td class="totals">Shipping:</td>
			<td class="totals cost">&pound;42.56</td>
		</tr>
		<tr>
			<td class="totals"><strong>TOTAL:</strong></td>
			<td class="totals cost"><strong>' . $bill->gross->getCurrency(null) . '</strong></td>
		</tr>
		<tr>
			<td class="totals">Deposit:</td>
			<td class="totals cost">&pound;100.00</td>
		</tr>
		<tr>
			<td class="totals"><strong>Balance due:</strong></td>
			<td class="totals cost"><strong>&pound;1782.56</strong></td>
		</tr>
	</tbody>
</table>

<div style="text-align: center; font-style: italic;">Payment terms: payment due in 30 days</div>
</body>
</html>
';

$mpdf->AddPage();
$mpdf->WriteHTML($html);

$mpdf->Output($this->getData('path'), \Mpdf\Output\Destination::FILE);
