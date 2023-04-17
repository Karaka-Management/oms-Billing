<?php
/**
 * Karaka
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
use Modules\Billing\Models\NullBill;
use phpOMS\Localization\ISO3166NameEnum;
use phpOMS\Localization\ISO3166TwoEnum;
use phpOMS\Localization\ISO4217Enum;
use phpOMS\Localization\ISO639Enum;
use phpOMS\Uri\UriFactory;

$countryCodes    = ISO3166TwoEnum::getConstants();
$countries       = ISO3166NameEnum::getConstants();
$languages       = ISO639Enum::getConstants();
$currencies      = ISO4217Enum::getConstants();

/**
 * @var \phpOMS\Views\View $this
 */
$media = $this->getData('media') ?? [];

/** @var \Modules\Billing\Models\Bill $bill */
$bill     = $this->getData('bill') ?? new NullBill();
$elements = $bill->getElements();

$billTypes = $this->getData('billtypes') ?? [];

/** @var \Modules\Auditor\Models\Audit */
$logs = $this->getData('logs') ?? [];

$editable = $bill instanceof NullBill || \in_array($bill->getStatus(), [BillStatus::DRAFT, BillStatus::UNPARSED]);
$disabled = !$editable  ? ' disabled' : '';

echo $this->getData('nav')->render(); ?>

<div class="tabview tab-2 col-simple">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Invoice'); ?></label></li>
            <li><label for="c-tab-2"><?= $this->getHtml('Items'); ?></label></li>
            <li><label for="c-tab-3">Preview</label></li>
            <li><label for="c-tab-4"><?= $this->getHtml('Payment'); ?></label></li>
            <li><label for="c-tab-5"><?= $this->getHtml('Media'); ?></label></li>
            <li><label for="c-tab-6"><?= $this->getHtml('Logs'); ?></label></li>
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
                                        <option value="<?= $this->printHtml($code); ?>"<?= $code === $bill->getLanguage() ? ' selected' : ''; ?>><?= $this->printHtml($language); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iCurrency"><?= $this->getHtml('Currency'); ?></label>
                                    <select id="iCurrency" name="bill_currency"<?= $disabled; ?>>
                                        <?php foreach ($currencies as $code => $currency) : $code = \substr($code, 1); ?>
                                        <option value="<?= $this->printHtml($code); ?>"<?= $code === $bill->getCurrency() ? ' selected' : ''; ?>><?= $this->printHtml($currency); ?>
                                            <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iSource"><?= $this->getHtml('Source'); ?></label>
                                    <span class="input">
                                        <button type="button" formaction="">
                                            <i class="fa fa-book"></i>
                                        </button>
                                        <input type="text" id="iSource" name="bill_source"<?= $disabled; ?>>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="iBillType"><?= $this->getHtml('Type'); ?></label>
                                    <select id="iBillType" name="bill_type"<?= $disabled; ?>>
                                        <?php foreach ($billTypes as $type) : ?>
                                        <option value="<?= $type->getId(); ?>"><?= $this->printHtml($type->getL11n()); ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iClient"><?= $this->getHtml('Client'); ?></label>
                                    <span class="input">
                                        <button type="button" formaction="">
                                            <i class="fa fa-book"></i>
                                        </button>
                                        <input type="text" id="iClient" name="bill_client" value="<?= $bill->client?->number ?? $bill->supplier?->number; ?>"<?= $disabled; ?>>
                                    </span>
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
                                    <input type="text" id="iRecipientBill" name="bill_recipient_bill" value="<?= $this->printHtml($bill->billTo) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iAddressBill"><?= $this->getHtml('Address'); ?></label>
                                    <input type="text" id="iAddressBill" name="bill_address_bill" value="<?= $this->printHtml($bill->billAddress) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iZipBill"><?= $this->getHtml('Zip'); ?></label>
                                    <input type="text" id="iZipBill" name="bill_address_bill" value="<?= $this->printHtml($bill->billZip) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCityBill"><?= $this->getHtml('City'); ?></label>
                                    <input type="text" id="iCityBill" name="bill_city_bill" value="<?= $this->printHtml($bill->billCity) ?>"<?= $disabled; ?>>
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
                                    <input type="text" id="iEmailBill" name="bill_email_bill" value="<?= $this->printHtml($bill->billEmail) ?>"<?= $disabled; ?>>
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
                                    <input type="text" id="iRecipientDelivery" name="bill_recipient_delivery" value="<?= $this->printHtml($bill->shipTo) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iAddressDelivery"><?= $this->getHtml('Address'); ?></label>
                                    <input type="text" id="iAddressDelivery" name="bill_address_delivery" value="<?= $this->printHtml($bill->shipAddress) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iZipDelivery"><?= $this->getHtml('Zip'); ?></label>
                                    <input type="text" id="iZipDelivery" name="bill_zip_delivery" value="<?= $this->printHtml($bill->shipZip) ?>"<?= $disabled; ?>>
                                </div>

                                <div class="form-group">
                                    <label for="iCityDelivery"><?= $this->getHtml('City'); ?></label>
                                    <input type="text" id="iCityDelivery" name="bill_city_delivery" value="<?= $this->printHtml($bill->shipCity) ?>"<?= $disabled; ?>>
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
                        <div class="portlet-head"><?= $this->getHtml('Invoice'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <table
                            id="invoiceElements"
                            class="default sticky"
                            data-tag="form"
                            data-ui-container="tbody"
                            data-ui-element="tr"
                            data-add-tpl=".oms-invoice-add">
                            <thead>
                            <tr>
                                <td>
                                <td><?= $this->getHtml('Item'); ?>
                                <td class="wf-100"><?= $this->getHtml('Name'); ?>
                                <td><?= $this->getHtml('Quantity'); ?>
                                <td><?= $this->getHtml('Discount'); ?>
                                <td><?= $this->getHtml('DiscountP'); ?>
                                <td><?= $this->getHtml('Bonus'); ?>
                                <td><?= $this->getHtml('Tax'); ?>
                                <td><?= $this->getHtml('Price'); ?>
                                <td><?= $this->getHtml('Net'); ?>
                            <tbody class="oms-ordercontainer">
                            <?php if ($editable) : ?>
                            <template class="oms-invoice-add">
                                <tr data-id="">
                                    <td>
                                        <i class="fa fa-chevron-up order-up"></i>
                                        <i class="fa fa-chevron-down order-down"></i>
                                        <i class="fa fa-times btn remove-form"></i>
                                    <td><span class="input">
                                        <button type="button" formaction="">
                                            <label><i class="fa fa-book"></i></label>
                                        </button><input type="text" autocomplete="off"></span>
                                    <td><textarea autocomplete="off"></textarea>
                                    <td><input type="number" min="0" value="" autocomplete="off">
                                    <td><input type="number" min="0" value="" autocomplete="off">
                                    <td><input type="number" min="0" max="100" step="any" value="" autocomplete="off">
                                    <td><input type="number" min="0" step="any" value="" autocomplete="off">
                                    <td><input type="number" min="0" step="any" value="" autocomplete="off">
                                    <td>
                                </tr>
                            </template>
                            <?php endif; ?>
                                <?php foreach ($elements as $element) : ?>
                                <tr>
                                    <td><?php if ($editable) : ?>
                                        <i class="fa fa-chevron-up order-up"></i>
                                        <i class="fa fa-chevron-down order-down"></i>
                                        <i class="fa fa-times btn remove-form"></i>
                                        <?php endif; ?>
                                    <td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input name="" type="text" value="<?= $element->itemNumber; ?>" required<?= $disabled; ?>></span>
                                    <td><textarea required<?= $disabled; ?>><?= $element->itemName; ?></textarea>
                                    <td><input name="" type="number" min="0" value="<?= $element->getQuantity(); ?>" required<?= $disabled; ?>>
                                    <td><input name="" type="text" value="<?= $element->singleSalesPriceNet->getCurrency(symbol: ''); ?>"<?= $disabled; ?>>
                                    <td><input name="" type="number" min="0"<?= $disabled; ?>>
                                    <td><input name="" type="number" min="0" max="100" step="any"<?= $disabled; ?>>
                                    <td><input name="" type="number" min="0" step="any"<?= $disabled; ?>>
                                    <td><input name="" type="number" min="0" step="any"<?= $disabled; ?>>
                                    <td><?= $element->totalSalesPriceNet->getCurrency(); ?>
                                <?php endforeach; ?>
                            <?php if ($editable) : ?>
                                <tr data-id="0">
                                    <td><i class="fa fa-chevron-up order-up"></i>
                                        <i class="fa fa-chevron-down order-down"></i>
                                        <i class="fa fa-times btn remove-form"></i>
                                    <td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input type="text" autocomplete="off"></span>
                                    <td><textarea autocomplete="off"></textarea>
                                    <td><input type="number" min="0" value="" autocomplete="off">
                                    <td><input type="number" min="0" value="" autocomplete="off">
                                    <td><input type="number" min="0" max="100" step="any" value="" autocomplete="off">
                                    <td><input type="number" min="0" step="any" value="" autocomplete="off">
                                    <td><input type="number" min="0" step="any" value="" autocomplete="off">
                                    <td>
                            <?php endif; ?>
                        </table>

                    </section>

                    <?php if ($editable) : ?>
                    <div class="box">
                        <input type="submit" class="add-form" value="Add" form="invoiceElements">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-3" name="tabular-2">
        <div class="tab col-simple">
            <div>
                <div class="col-xs-12 col-sm-3 box">
                    <select id="iBillPreviewType" name="bill_preview_type">>
                        <?php foreach ($billTypes as $type) : ?>
                        <option value="<?= $type->getId(); ?>"><?= $this->printHtml($type->getL11n()); ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-simple">
                <div class="col-xs-12 col-simple">
                    <section id="mediaFile" class="portlet col-simple">
                        <div class="portlet-body col-simple">
                            <iframe class="col-simple" id="iHelperFrame" src="Resources/mozilla/Pdf/web/viewer.html?file=<?= \urlencode('http://127.0.0.1/en/api/bill/render?bill_type='); ?>{#iBillPreviewType}" loading="lazy" allowfullscreen></iframe>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-4" name="tabular-2">
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
                                        <i class="fa fa-chevron-up order-up"></i>
                                        <i class="fa fa-chevron-down order-down"></i>
                                        <i class="fa fa-times btn remove-form"></i>
                                        <?php endif; ?>
                                    <td><input type="datetime-local" autocomplete="off" required>
                                    <td><input type="number" min="0" value="" autocomplete="off" required>
                                </tr>
                            </template>
                        </table>
                    </section>

                    <?php if ($editable) : ?>
                    <div class="box">
                        <input type="submit" class="add-form" value="Add" form="paymentPlan">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-5" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Media'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <div class="form-group">
                                    <label for="iMedia"><?= $this->getHtml('Media'); ?></label>
                                    <div class="ipt-first">
                                        <input type="text" id="iMedia" placeholder="&#xf15b; File">
                                    </div>
                                    <div class="ipt-second"><button><?= $this->getHtml('Select'); ?></button></div>
                                </div>

                                <div class="form-group">
                                    <label for="iUpload"><?= $this->getHtml('Upload'); ?></label>
                                    <input type="file" id="iUpload" form="fTask"><input form="fTask" type="hidden" name="type">
                                </div>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-8">
                    <?= $this->getData('medialist')?->render($media); ?>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-6" name="tabular-2">
        <div class="tab">
            <?php
            $footerView = new \phpOMS\Views\PaginationView($this->l11nManager, $this->request, $this->response);
            $footerView->setTemplate('/Web/Templates/Lists/Footer/PaginationBig');
            $footerView->setPages(20);
            $footerView->setPage(1);
            ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Logs'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <table class="default">
                            <thead>
                            <tr>
                                <td><?= $this->getHtml('ID', '0', '0'); ?>
                                <td><?= $this->getHtml('Trigger', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('Action', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedBy', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedAt', 'Auditor', 'Backend'); ?>
                            <tbody>
                            <?php
                            foreach ($logs as $audit) :
                                $url = UriFactory::build('{/base}/admin/audit/single?id=' . $audit->getId());
                            ?>
                            <tr data-href="<?= $url; ?>">
                                <td><a href="<?= $url; ?>"><?= $audit->getId(); ?></a>
                                <td><a href="<?= $url; ?>"><?= $audit->trigger; ?></a>
                                <td><?php if ($audit->old === null) : echo $this->getHtml('CREATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->old !== null && $audit->new !== null) : echo $this->getHtml('UPDATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->new === null) : echo $this->getHtml('DELETE', 'Auditor', 'Backend'); ?>
                                    <?php else : echo $this->getHtml('UNKNOWN', 'Auditor', 'Backend'); ?>
                                    <?php endif; ?>
                                <td><a class="content"
                                    href="<?= UriFactory::build('{/base}/admin/account/settings?id=' . $audit->createdBy->getId()); ?>"><?= $this->printHtml(
                                    $this->renderUserName('%3$s %2$s %1$s', [$audit->createdBy->name1, $audit->createdBy->name2, $audit->createdBy->name3, $audit->createdBy->login])
                                ); ?></a>
                                <td><a href="<?= $url; ?>"><?= $audit->createdAt->format('Y-m-d'); ?></a>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

