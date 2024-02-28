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

use Modules\Billing\Models\BillStatus;
use phpOMS\Localization\ISO3166NameEnum;
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\ISO4217Enum;
use phpOMS\Localization\ISO639Enum;
use phpOMS\Uri\UriFactory;

$countryCodes = ISO3166TwoEnum::getConstants();
$countries    = ISO3166NameEnum::getConstants();
$languages    = ISO639Enum::getConstants();
$currencies   = ISO4217Enum::getConstants();

/**
 * @var \phpOMS\Views\View $this
 */

/** @var \Modules\Billing\Models\Bill $bill */
$bill     = $this->data['bill'];
$elements = $bill->elements;

$billTypes = $this->data['billtypes'] ?? [];

$archive = $bill->getFileByTypeName('external');

/** @var \Modules\Auditor\Models\Audit */
$logs = $this->data['logs'] ?? [];

$editable = $bill->id === 0 || \in_array($bill->status, [BillStatus::DRAFT, BillStatus::UNPARSED]);
$disabled = $editable  ? '' : ' disabled';

$isNew = $archive->id === 0;

echo $this->data['nav']->render(); ?>
<?php if (!$bill->isValid()) : ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet hl-1">
            <article class="hl-1">
                <ul>
                <?php if (!$bill->areElementsValid()) : ?>
                    <li><?= $this->getHtml('E_bill_items'); ?></li>
                <?php endif; ?>
                <?php if (!$bill->validateTaxAmountElements()) : ?>
                    <li><?= $this->getHtml('E_bill_taxes'); ?></li>
                <?php endif; ?>
                <?php if (!$bill->validateNetElements()) : ?>
                    <li><?= $this->getHtml('E_bill_net'); ?></li>
                <?php endif; ?>
                <?php if (!$bill->validateGrossElements()) : ?>
                    <li><?= $this->getHtml('E_bill_gross'); ?></li>
                <?php endif; ?>
                <?php if (!$bill->validatePriceQuantityElements()) : ?>
                    <li><?= $this->getHtml('E_bill_unit'); ?></li>
                <?php endif; ?>
                </ul>
            </article>
        </section>
    </div>
</div>
<?php endif; ?>

<div class="tabview tab-2 col-simple">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Invoice'); ?></label>
            <li><label for="c-tab-2"><?= $this->getHtml('Items'); ?></label>
            <li><label for="c-tab-3"><?= $this->getHtml('Internal'); ?></label>
            <?php if (!$isNew) : ?><li><label for="c-tab-4"><?= $this->getHtml('Archive'); ?></label><?php endif; ?>
            <!--<li><label for="c-tab-5"><?= $this->getHtml('Payment'); ?></label>-->
            <li><label for="c-tab-6"><?= $this->getHtml('Files'); ?></label>
            <?php if (!$isNew && !empty($logs)) : ?><li><label for="c-tab-7"><?= $this->getHtml('Logs'); ?></label><?php endif; ?>
        </ul>
    </div>
    <div class="tab-content col-simple">
        <input type="radio" id="c-tab-1" name="tabular-2" checked>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <form>
                            <div class="portlet-head"><?= $this->getHtml('Invoice'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iLanguage"><?= $this->getHtml('Language'); ?></label>
                                    <select id="iLanguage" name="bill_language"<?= $disabled; ?>>
                                        <?php foreach ($languages as $code => $language) : $code = \strtolower(\substr($code, 1)); ?>
                                        <option value="<?= $this->printHtml($code); ?>"<?= $code === $bill->language ? ' selected' : ''; ?>><?= $this->printHtml($language); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iCurrency"><?= $this->getHtml('Currency'); ?></label>
                                    <select id="iCurrency" name="bill_currency"<?= $disabled; ?>>
                                        <?php foreach ($currencies as $code => $currency) : $code = \substr($code, 1); ?>
                                        <option value="<?= $this->printHtml($code); ?>"<?= $code === $bill->currency ? ' selected' : ''; ?>><?= $this->printHtml($currency); ?>
                                            <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iSource"><?= $this->getHtml('Source'); ?></label>
                                    <span class="input">
                                        <button type="button" formaction="">
                                            <i class="g-icon">book</i>
                                        </button>
                                        <input type="text" id="iSource" name="bill_source"<?= $disabled; ?>>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="iBillType"><?= $this->getHtml('Type'); ?></label>
                                    <select id="iBillType" name="bill_type"<?= $disabled; ?>>
                                        <?php foreach ($billTypes as $type) : ?>
                                        <option value="<?= $type->id; ?>"<?= $type->id === $bill->type->id ? ' selected' : ''; ?>><?= $this->printHtml($type->getL11n()); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iClient"><?= $this->getHtml('Supplier'); ?></label>
                                    <div class="ipt-wrap">
                                        <div class="ipt-first">
                                            <span class="input">
                                                <button type="button" formaction="">
                                                    <i class="g-icon">book</i>
                                                </button>
                                                <input type="text" id="iClient" name="bill_client" value="<?= $bill->client?->number ?? $bill->supplier?->number; ?>"<?= $disabled; ?>>
                                            </span>
                                        </div>
                                        <?php if (($bill->client?->id ?? 0) > 0) : ?>
                                        <div class="ipt-second">
                                             <a class="button" href="<?= UriFactory::build('{/base}/sales/client/view?id=' . $bill->client->id); ?>"><?= $this->getHtml('Client'); ?></a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="iInvoiceDate"><?= $this->getHtml('Invoice'); ?></label>
                                    <input type="datetime-local" id="iInvoiceDate" name="bill_invoice_date"
                                        value="<?= $bill->createdAt->format('Y-m-d\TH:i'); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iDeliveryDate"><?= $this->getHtml('Delivery'); ?></label>
                                    <input type="datetime-local" id="iDeliveryDate" name="bill_delivery_date"
                                        value="<?= $bill->createdAt->format('Y-m-d\TH:i'); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iDueDate"><?= $this->getHtml('Due'); ?></label>
                                    <input type="datetime-local" id="iDueDate" name="bill_due"
                                        value="<?= (new \DateTime('now'))->format('Y-m-d\TH:i'); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iShipment"><?= $this->getHtml('Shipment'); ?></label>
                                    <select id="iShipment" name="bill_shipment_type"<?= $disabled; ?>>
                                        <option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iTermsOfDelivery"><?= $this->getHtml('TermsOfDelivery'); ?></label>
                                    <select id="iTermsOfDelivery" name="bill_termsofdelivery"<?= $disabled; ?>>
                                        <option>
                                    </select>
                                </div>
                            </div>
                            <?php if ($editable) : ?>
                            <div class="portlet-foot">
                                <input type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>" name="create-invoice">
                            </div>
                            <?php endif; ?>
                        </form>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Billing'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <div class="form-group">
                                    <label for="iAddressListBill"><?= $this->getHtml('Addresses'); ?></label>
                                    <select id="iAddressListBill" name="bill_address_bill_list"<?= $disabled; ?>>
                                        <option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iRecipientBill"><?= $this->getHtml('Recipient'); ?></label>
                                    <input type="text" id="iRecipientBill" name="bill_recipient_bill" value="<?= $this->printHtml($bill->billTo); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iAddressBill"><?= $this->getHtml('Address'); ?></label>
                                    <input type="text" id="iAddressBill" name="bill_address_bill" value="<?= $this->printHtml($bill->billAddress); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iZipBill"><?= $this->getHtml('Zip'); ?></label>
                                    <input type="text" id="iZipBill" name="bill_address_bill" value="<?= $this->printHtml($bill->billZip); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCityBill"><?= $this->getHtml('City'); ?></label>
                                    <input type="text" id="iCityBill" name="bill_city_bill" value="<?= $this->printHtml($bill->billCity); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCountryBill"><?= $this->getHtml('Country'); ?></label>
                                    <select id="iCountryBill" name="bill_country_bill"<?= $disabled; ?>>
                                        <?php foreach ($countryCodes as $code3 => $code2) : ?>
                                            <option value="<?= $this->printHtml($code2); ?>"<?= $code2 === $bill->billCountry ? ' selected' : ''; ?>><?= $this->printHtml($countries[$code3]); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iEmailBill"><?= $this->getHtml('Email'); ?></label>
                                    <input type="text" id="iEmailBill" name="bill_email_bill" value="<?= $this->printHtml($bill->billEmail); ?>"<?= $disabled; ?>>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Delivery'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <div class="form-group">
                                    <label for="iAddressListDelivery"><?= $this->getHtml('Addresses'); ?></label>
                                    <select id="iAddressListDelivery" name="bill_address_delivery_list"<?= $disabled; ?>>
                                        <option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iRecipientDelivery"><?= $this->getHtml('Recipient'); ?></label>
                                    <input type="text" id="iRecipientDelivery" name="bill_recipient_delivery" value="<?= $this->printHtml($bill->shipTo); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iAddressDelivery"><?= $this->getHtml('Address'); ?></label>
                                    <input type="text" id="iAddressDelivery" name="bill_address_delivery" value="<?= $this->printHtml($bill->shipAddress); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iZipDelivery"><?= $this->getHtml('Zip'); ?></label>
                                    <input type="text" id="iZipDelivery" name="bill_zip_delivery" value="<?= $this->printHtml($bill->shipZip); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCityDelivery"><?= $this->getHtml('City'); ?></label>
                                    <input type="text" id="iCityDelivery" name="bill_city_delivery" value="<?= $this->printHtml($bill->shipCity); ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCountryDelivery"><?= $this->getHtml('Country'); ?></label>
                                    <select id="iCountryDelivery" name="bill_country_delivery"<?= $disabled; ?>>
                                            <option value="" <?= $bill->shipTo === '' ? 'selected ' : ''; ?>><?= $this->getHtml('Select', '0', '0'); ?>
                                        <?php foreach ($countryCodes as $code3 => $code2) : ?>
                                            <option value="<?= $this->printHtml($code2); ?>"<?= $code2 === $bill->shipCountry ? ' selected' : ''; ?>><?= $this->printHtml($countries[$code3]); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-2" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Invoice'); ?><i class="g-icon download btn end-xs">download</i></div>
                        <div class="slider">
                        <table
                            id="invoiceElements"
                            class="default sticky"
                            data-action="<?= \phpOMS\Uri\UriFactory::build('{/api}billing/bill/element?{?}&csrf={$CSRF}'); ?>"
                            data-tag="form"
                            data-ui-container="tbody"
                            data-ui-element="tr"
                            data-on-change="1"
                            data-add-tpl=".oms-invoice-add">
                            <thead>
                            <tr>
                                <td>
                                <td style="min-width:150px"><?= $this->getHtml('Item'); ?>
                                <td class="wf-100" style="min-width:150px"><?= $this->getHtml('Name'); ?>
                                <td style="min-width:75px"><?= $this->getHtml('Quantity'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Discount'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('DiscountP'); ?>
                                <td style="min-width:75px"><?= $this->getHtml('Bonus'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Price'); ?>
                                <td style="min-width:75px"><?= $this->getHtml('TaxP'); ?>
                                <td><?= $this->getHtml('Net'); ?>
                                <td><?= $this->getHtml('Margin'); ?>
                            <tbody class="oms-ordercontainer">
                            <?php if ($editable) : ?>
                            <template class="oms-invoice-add">
                                <tr data-id="">
                                    <td>
                                        <i class="g-icon order-up">expand_less</i>
                                        <i class="g-icon order-down">expand_more</i>
                                        <i class="g-icon btn remove-form">close</i>
                                    <td><span class="input">
                                        <button type="button" formaction="">
                                            <label><i class="g-icon">book</i></label>
                                        </button><input name="item_number" type="text" autocomplete="off"></span>
                                    <td><textarea name="item_description" autocomplete="off"></textarea>
                                    <td><input name="item_quantity" type="number" step="any" value="" autocomplete="off">
                                    <td><input name="item_discountp" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_discountr" type="number" min="-100" max="100" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_bonus" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_price" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_taxr" type="number" step="0.01" value="" autocomplete="off">
                                    <td>
                                    <td>
                                </tr>
                            </template>
                            <?php endif; ?>
                                <?php foreach ($elements as $element) : ?>
                                <tr>
                                    <td><?php if ($editable) : ?>
                                        <i class="g-icon order-up">expand_less</i>
                                        <i class="g-icon order-down">expand_more</i>
                                        <i class="g-icon btn remove-form">close</i>
                                        <?php endif; ?>
                                    <td><span class="input">
                                        <button type="button" formaction="">
                                            <i class="g-icon">book</i>
                                        </button><input name="item_number" autocomplete="off" type="text" value="<?= $element->itemNumber; ?>"<?= $disabled; ?>></span>
                                    <td><textarea name="item_description" autocomplete="off"<?= $disabled; ?>><?= $element->itemName; ?></textarea>
                                    <td><input name="item_quantity" autocomplete="off" type="number" step="any" value="<?= $element->quantity->sub($element->discountQ)->getAmount($element->container->quantityDecimals); ?>"<?= $disabled; ?>>
                                    <td><input name="item_discountp" autocomplete="off" type="number" step="0.01" value="<?= $element->singleDiscountP->getAmount(); ?>"<?= $disabled; ?>>
                                    <td><input name="item_discountr" autocomplete="off" type="number" step="0.01" value="<?= $element->singleDiscountR->getAmount(); ?>"<?= $disabled; ?>>
                                    <td><input name="item_bonus" autocomplete="off" type="number" min="-100" max="100" step="0.01" value="<?= $element->discountQ->getAmount($element->container->quantityDecimals); ?>"<?= $disabled; ?>>
                                    <td><input name="item_price" autocomplete="off" type="number" step="0.01" value="<?= $element->singleSalesPriceNet->getFloat(); ?>"<?= $disabled; ?>>
                                    <td><input name="item_taxr" autocomplete="off" type="number" step="0.01" value="<?= $element->taxR->getAmount(); ?>"<?= $disabled; ?>>
                                    <td><?= $this->getCurrency($element->totalSalesPriceNet, symbol: ''); ?>
                                    <td><?= \number_format($element->totalSalesPriceNet->value === 0 ? 0 : (1 - $element->totalPurchasePriceNet->value / $element->totalSalesPriceNet->value) * 100, 2); ?>%
                                <?php endforeach; ?>
                            <?php if ($editable) : ?>
                                <tr data-id="0">
                                    <td><i class="g-icon order-up">expand_less</i>
                                        <i class="g-icon order-down">expand_more</i>
                                        <i class="g-icon btn remove-form">close</i>
                                    <td><span class="input">
                                        <button type="button" formaction="">
                                            <i class="g-icon">book</i></button><input name="item_number" type="text" autocomplete="off"></span>
                                    <td><textarea name="item_description" autocomplete="off"></textarea>
                                    <td><input name="item_quantity" type="number" step="any" value="" autocomplete="off">
                                    <td><input name="item_discountp" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_discountr" type="number" min="-100" max="100" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_bonus" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_price" type="number" step="0.01" value="" autocomplete="off">
                                    <td><input name="item_taxr" type="number" step="0.01" value="" autocomplete="off">
                                    <td>
                                    <td>
                            <?php endif; ?>
                            <tfoot>
                                <tr class="hl-2">
                                    <td colspan="3"><?= $this->getHtml('Total'); ?>
                                    <td>
                                    <td><?= $bill->netDiscount->getAmount(2); ?>
                                    <td><?= \number_format($bill->netDiscount->value === 0 ? 0 : ($bill->netDiscount->value / ($bill->netSales->value + $bill->netDiscount->value)) * 100, 2); ?>%
                                    <td>
                                    <td>
                                    <td><?= $bill->taxP->getAmount(2); ?>
                                    <td><?= $bill->netSales->getAmount(2); ?>
                                    <td><?= \number_format($bill->netSales->value === 0 ? 0 : (1 - $bill->netCosts->value / $bill->netSales->value) * 100, 2); ?>%
                        </table>
                        </div>
                    </section>

                    <?php if ($editable) : ?>
                    <div class="box">
                        <input type="submit" class="add-form" value="<?= $this->getHtml('Add', '0', '0'); ?>" form="invoiceElements">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-3" name="tabular-2">
        <div class="tab col-simple">
            <div>
                <div class="col-xs-12 col-sm-3 box">
                    <select id="iBillPreviewType" name="bill_preview_type"
                    data-action='[{"listener": "change", "action": [{"key": 1, "type": "dom.reload", "src": "iPreviewBill"}]}]'>
                        <?php foreach ($billTypes as $type) : ?>
                        <option value="<?= $type->id; ?>"<?= $type->id === $bill->type->id ? ' selected' : ''; ?>><?= $this->printHtml($type->getL11n()); ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-simple">
                <div class="col-xs-12 col-simple">
                    <section id="mediaFile" class="portlet col-simple">
                        <div class="portlet-body col-simple">
                            <iframe class="col-simple" id="iPreviewBill" data-src="Resources/mozilla/Pdf/web/viewer.html?file=<?= \urlencode(UriFactory::build('{/api}bill/render/preview?bill=' . $bill->id) . '&bill_type='); ?>{#iBillPreviewType}" loading="lazy" allowfullscreen></iframe>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <?php if (!$isNew) : ?>
        <input type="radio" id="c-tab-4" name="tabular-2">
        <div class="tab col-simple">
            <?php if ($bill->status === BillStatus::DRAFT
                || $bill->status === BillStatus::UNPARSED
                || $bill->status === BillStatus::ACTIVE
            ) : ?>
            <div>
                <div class="col-xs-12 col-sm-3 box">
                    <form id="iInvoiceRecognition"
                        action="<?= UriFactory::build('{/api}bill/parse?id=' . $bill->id . '&async=0'); ?>"
                        method="post"
                        data-redirect="<?= UriFactory::build('{%}'); ?>">
                        <input type="submit" value="<?= $this->getHtml('Parse') ?>">
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-simple">
                <div class="col-xs-12 col-simple">
                    <section id="mediaFile" class="portlet col-simple">
                        <div class="portlet-body col-simple">
                            <iframe id="iBillArchive"
                                class="col-simple"
                                src="<?= UriFactory::build('{/api}media/export') . '?id=' . $archive->id; ?>"
                                loading="lazy" allowfullscreen></iframe>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!--
        <input type="radio" id="c-tab-5" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Payment'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <div class="form-group">
                                    <label for="iPaymentTypeList"><?= $this->getHtml('Types'); ?></label>
                                    <input type="text" id="iPaymentTypeList" name="bill_payment_type_list"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iPaymentType"><?= $this->getHtml('Type'); ?></label>
                                    <select id="iPaymentType" name="bill_payment_type"<?= $disabled; ?>>
                                        <option><?= $this->getHtml('MoneyTransfer'); ?>
                                        <option><?= $this->getHtml('Prepaid'); ?>
                                        <option><?= $this->getHtml('AlreadyPaid'); ?>
                                        <option><?= $this->getHtml('CreditCard'); ?>
                                        <option><?= $this->getHtml('DirectDebit'); ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iPaymentDueDate"><?= $this->getHtml('Due'); ?></label>
                                    <input type="datetime-local" id="iPaymentDueDate" name="bill_payment_due_date"<?= $disabled; ?>>
                                </div>
                            </div>

                            <div class="portlet-separator"></div>

                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iPaymentCashbackDate1"><?= $this->getHtml('Cashback'); ?></label>
                                    <input type="datetime-local" id="iPaymentCashbackDate1" name="bill_payment_cashback_date1"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iPaymentCashbackAmount1"><?= $this->getHtml('Cashback'); ?></label>
                                    <input type="number" id="iPaymentCashbackAmount1" name="bill_payment_cashback_amount1"<?= $disabled; ?>>
                                </div>
                            </div>

                            <div class="portlet-separator"></div>

                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iPaymentCashbackDate2"><?= $this->getHtml('Cashback'); ?></label>
                                    <input type="datetime-local" id="iPaymentCashbackDate2" name="bill_payment_cashback_date2"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iPaymentCashbackAmount2"><?= $this->getHtml('Cashback'); ?></label>
                                    <input type="number" id="iPaymentCashbackAmount2" name="bill_payment_cashback_amount2"<?= $disabled; ?>>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-8">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('PaymentPlan'); ?></div>
                        <table id="paymentPlan"
                            class="default sticky"
                            data-tag="form"
                            data-ui-container="tbody"
                            data-ui-element="tr"
                            data-add-tpl=".oms-payment-add">
                            <thead>
                            <tr>
                                <td>
                                <td class="wf-100"><?= $this->getHtml('Date'); ?>
                                <td><?= $this->getHtml('Amount'); ?>
                            <tbody class="oms-ordercontainer">
                            <template class="oms-payment-add">
                                <tr data-id="">
                                    <td><?php if ($editable) : ?>
                                        <i class="g-icon order-up">expand_less</i>
                                        <i class="g-icon order-down">expand_more</i>
                                        <i class="g-icon btn remove-form">close</i>
                                        <?php endif; ?>
                                    <td><input type="datetime-local" autocomplete="off" required>
                                    <td><input type="number" value="" autocomplete="off" required>
                                </tr>
                            </template>
                        </table>
                    </section>

                    <?php if ($editable) : ?>
                    <div class="box">
                        <input type="submit" class="add-payment-form" value="<?= $this->getHtml('Add', '0', '0'); ?>" form="paymentPlan">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        -->

        <input type="radio" id="c-tab-6" name="tabular-2">
        <div class="tab col-simple">
            <?= $this->data['media-upload']->render('bill-file', 'files', '', $bill->files); ?>
        </div>

        <?php if (!$isNew && !empty($logs)) : ?>
        <input type="radio" id="c-tab-7" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Logs'); ?><i class="g-icon download btn end-xs">download</i></div>
                        <table class="default sticky">
                            <thead>
                            <tr>
                                <td><?= $this->getHtml('ID', '0', '0'); ?>
                                <td><?= $this->getHtml('Action', 'Auditor', 'Backend'); ?>
                                <td class="wf-100"><?= $this->getHtml('Trigger', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedBy', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedAt', 'Auditor', 'Backend'); ?>
                            <tbody>
                            <?php
                            foreach ($logs as $audit) :
                                $url = UriFactory::build('{/base}/admin/audit/view?id=' . $audit->id);
                            ?>
                            <tr data-href="<?= $url; ?>">
                                <td><a href="<?= $url; ?>"><?= $audit->id; ?></a>
                                <td><?php if ($audit->old === null) : echo $this->getHtml('CREATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->old !== null && $audit->new !== null) : echo $this->getHtml('UPDATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->new === null) : echo $this->getHtml('DELETE', 'Auditor', 'Backend'); ?>
                                    <?php else : echo $this->getHtml('UNKNOWN', 'Auditor', 'Backend'); ?>
                                    <?php endif; ?>
                                <td><a href="<?= $url; ?>"><?= $audit->trigger; ?></a>
                                <td><a class="content"
                                    href="<?= UriFactory::build('{/base}/admin/account/settings?id=' . $audit->createdBy->id); ?>"><?= $this->printHtml(
                                    $this->renderUserName('%3$s %2$s %1$s', [$audit->createdBy->name1, $audit->createdBy->name2, $audit->createdBy->name3, $audit->createdBy->login])
                                ); ?></a>
                                <td><a href="<?= $url; ?>"><?= $audit->createdAt->format('Y-m-d H:i'); ?></a>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
