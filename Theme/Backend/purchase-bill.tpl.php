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

use phpOMS\Uri\UriFactory;

// Media helper functions (e.g. file icon generator)
include __DIR__ . '/../../../Media/Theme/Backend/template-functions.php';

/**
 * @var \phpOMS\Views\View $this
 */

/** @var \Modules\Billing\Models\Bill $bill */
$bill     = $this->data['bill'];
$elements = $bill->elements;

$billTypes = $this->data['billtypes'] ?? [];

$originalType = $this->data['originalType'];
$original     = $bill->getFileByType($originalType);

/** @var \Modules\Auditor\Models\Audit */
$logs = $this->data['logs'] ?? [];

echo $this->data['nav']->render(); ?>

<div class="tabview tab-2 col-simple">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Invoice'); ?></label>
            <li><label for="c-tab-2"><?= $this->getHtml('Items'); ?></label>
            <li><label for="c-tab-3"><?= $this->getHtml('Preview'); ?></label>
            <li><label for="c-tab-4"><?= $this->getHtml('Original'); ?></label>
            <li><label for="c-tab-5"><?= $this->getHtml('Payment'); ?></label>
            <li><label for="c-tab-6"><?= $this->getHtml('Media'); ?></label>
            <li><label for="c-tab-7"><?= $this->getHtml('Logs'); ?></label>
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
                                <table class="layout wf-100">
                                    <tr><td><label for="iSource"><?= $this->getHtml('Source'); ?></label>
                                    <tr><td><span class="input"><button type="button" formaction=""><i class="g-icon">book</i></button><input type="text" id="iSource" name="source"></span>
                                    <tr><td><label for="iType"><?= $this->getHtml('Type'); ?></label>
                                    <tr><td><select id="iType" name="type">
                                                <option><?= $this->getHtml('Invoice'); ?>
                                                <option><?= $this->getHtml('Offer'); ?>
                                                <option><?= $this->getHtml('Confirmation'); ?>
                                                <option><?= $this->getHtml('DeliveryNote'); ?>
                                                <option><?= $this->getHtml('CreditNote'); ?>
                                            </select>
                                    <tr><td><label for="iClient"><?= $this->getHtml('Client'); ?></label>
                                    <tr><td><span class="input"><button type="button" formaction=""><i class="g-icon">book</i></button><input type="text" id="iClient" name="client"></span>
                                    <tr><td><label for="iDelivery"><?= $this->getHtml('Delivery'); ?></label>
                                    <tr><td><input type="datetime-local" id="iDelivery" name="delivery">
                                    <tr><td><label for="iDue"><?= $this->getHtml('Due'); ?></label>
                                    <tr><td><input type="datetime-local" id="iDue" name="due">
                                    <tr><td><label for="iFreightage"><?= $this->getHtml('Freightage'); ?></label>
                                    <tr><td><input type="number" id="iFreightage" name="freightage">
                                    <tr><td><label for="iShipment"><?= $this->getHtml('Shipment'); ?></label>
                                    <tr><td><select id="iShipment" name="shipment">
                                                <option>
                                            </select>
                                    <tr><td><label for="iTermsOfDelivery"><?= $this->getHtml('TermsOfDelivery'); ?></label>
                                    <tr><td><select id="iTermsOfDelivery" name="termsofdelivery">
                                                <option>
                                            </select>
                                </table>
                            </div>
                            <div class="portlet-foot"><input type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>" name="create-bill"></div>
                        </form>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Invoice'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <table class="layout wf-100">
                                    <tr><td><label for="iAddressS"><?= $this->getHtml('Addresses'); ?></label>
                                    <tr><td><select id="iAddressS" name="addressS">
                                                <option>
                                            </select>
                                    <tr><td><label for="iIRecipient"><?= $this->getHtml('Recipient'); ?></label>
                                    <tr><td><input type="text" id="iIRecipient" name="irecipient">
                                    <tr><td><label for="iAddress"><?= $this->getHtml('Address'); ?></label>
                                    <tr><td><input type="text" id="iAddress" name="address">
                                    <tr><td><label for="iZip"><?= $this->getHtml('Zip'); ?></label>
                                    <tr><td><input type="text" id="iZip" name="zip">
                                    <tr><td><label for="iCity"><?= $this->getHtml('City'); ?></label>
                                    <tr><td><input type="text" id="iCity" name="city">
                                    <tr><td><label for="iCountry"><?= $this->getHtml('Country'); ?></label>
                                    <tr><td><input type="text" id="iCountry" name="country">
                                </table>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Delivery'); ?></div>
                        <div class="portlet-body">
                            <form>
                                <table class="layout wf-100">
                                    <tr><td><label for="iAddressS"><?= $this->getHtml('Addresses'); ?></label>
                                    <tr><td><select id="iAddressS" name="addressS">
                                                <option>
                                            </select>
                                    <tr><td><label for="iDRecipient"><?= $this->getHtml('Recipient'); ?></label>
                                    <tr><td><input type="text" id="iDRecipient" name="drecipient">
                                    <tr><td><label for="iAddress"><?= $this->getHtml('Address'); ?></label>
                                    <tr><td><input type="text" id="iAddress" name="address">
                                    <tr><td><label for="iZip"><?= $this->getHtml('Zip'); ?></label>
                                    <tr><td><input type="text" id="iZip" name="zip">
                                    <tr><td><label for="iCity"><?= $this->getHtml('City'); ?></label>
                                    <tr><td><input type="text" id="iCity" name="city">
                                    <tr><td><label for="iCountry"><?= $this->getHtml('Country'); ?></label>
                                    <tr><td><input type="text" id="iCountry" name="country">
                                </table>
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
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Invoice'); ?><i class="g-icon download btn end-xs">download</i></div>
                        <div class="slider">
                        <table class="default sticky" id="invoice-item-list">
                            <thead>
                            <tr>
                                <td>
                                <td style="min-width:150px"><?= $this->getHtml('Item'); ?>
                                <td class="wf-100" style="min-width:150px"><?= $this->getHtml('Name'); ?>
                                <td style="min-width:50px"><?= $this->getHtml('Quantity'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Price'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Discount'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('DiscountP'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Bonus'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Tax'); ?>
                                <td style="min-width:90px"><?= $this->getHtml('Net'); ?>
                            <tbody>
                            <?php foreach ($elements as $element) : ?>
                            <tr>
                                <td><i class="g-icon add">add</i> <i class="g-icon order-up">expand_less</i> <i class="g-icon order-down">expand_more</i>
                                <td><span class="input"><button type="button" formaction=""><i class="g-icon">book</i></button><input name="" type="text" value="<?= $element->itemNumber; ?>" required></span>
                                <td><textarea required><?= $element->itemName; ?></textarea>
                                <td><input name="" type="number" min="0" value="<?= $element->quantity; ?>" required>
                                <td><input name="" type="text" value="<?= $this->getCurrency($element->singleSalesPriceNet, ''); ?>">
                                <td><input name="" type="number" min="0">
                                <td><input name="" type="number" min="0" max="100" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td><?= $this->getCurrency($element->totalSalesPriceNet); ?>
                            <?php endforeach; ?>
                            <tr>
                                <td><i class="g-icon">add</i> <i class="g-icon order-up">expand_less</i> <i class="g-icon order-down">expand_more</i>
                                <td><span class="input"><button type="button" formaction=""><i class="g-icon">book</i></button><input name="" type="text" required></span>
                                <td><textarea required></textarea>
                                <td><input name="" type="number" min="0" value="0" required>
                                <td><input name="" type="text">
                                <td><input name="" type="number" min="0">
                                <td><input name="" type="number" min="0" max="100" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td>
                        </table>
                        </div>
                        <div class="portlet-foot">
                            <?= $this->getHtml('Freightage'); ?>: 0.00 -
                            <?= $this->getHtml('Net'); ?>: <?= $this->getCurrency($bill->netSales); ?> -
                            <?= $this->getHtml('Tax'); ?>: 0.00 -
                            <?= $this->getHtml('Total'); ?>: <?= $this->getCurrency($bill->grossSales); ?>
                        </div>
                    </div>
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
                        <option value="<?= $type->id; ?>"><?= $this->printHtml($type->getL11n()); ?>
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

        <input type="radio" id="c-tab-4" name="tabular-2">
        <div class="tab col-simple">
            <div class="row col-simple">
                <div class="col-xs-12 col-simple">
                    <section id="mediaFile" class="portlet col-simple">
                        <div class="portlet-body col-simple">
                            <?php if ($original->id > 0) : ?>
                                <iframe class="col-simple" id="iOriginal" src="<?= UriFactory::build('Resources/mozilla/Pdf/web/viewer.html?file=' . \urlencode(UriFactory::build('{/api}media/export?id=' . $original->id))); ?>" allowfullscreen></iframe>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-5" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="box wf-100">
                        <header><h1><?= $this->getHtml('Payment'); ?></h1></header>
                        <div class="inner">
                            <form>
                                <table class="layout wf-100">
                                    <tr><td><label for="iType"><?= $this->getHtml('Type'); ?></label>
                                    <tr><td><select id="iType" name="type">
                                                <option>
                                            </select>
                                    <tr><td><label for="iType"><?= $this->getHtml('Type'); ?></label>
                                    <tr><td><select id="iType" name="type">
                                                <option><?= $this->getHtml('MoneyTransfer'); ?>
                                                <option><?= $this->getHtml('Prepaid'); ?>
                                                <option><?= $this->getHtml('AlreadyPaid'); ?>
                                                <option><?= $this->getHtml('CreditCard'); ?>
                                                <option><?= $this->getHtml('DirectDebit'); ?>
                                            </select>
                                    <tr><td><label for="iDue"><?= $this->getHtml('Due'); ?></label>
                                    <tr><td><input type="datetime-local" id="iDue" name="due">
                                    <tr><td><label for="iDue"><?= $this->getHtml('Due'); ?> - <?= $this->getHtml('Cashback'); ?></label>
                                    <tr><td><input type="datetime-local" id="iDue" name="due">
                                    <tr><td><label for="iCashBack"><?= $this->getHtml('Cashback'); ?></label>
                                    <tr><td><input type="number" id="iCashBack" name="cashback">
                                    <tr><td><label for="iDue"><?= $this->getHtml('Due'); ?> - <?= $this->getHtml('Cashback'); ?> 2</label>
                                    <tr><td><input type="datetime-local" id="iDue" name="due">
                                    <tr><td><label for="iCashBack2"><?= $this->getHtml('Cashback'); ?> 2</label>
                                    <tr><td><input type="number" id="iCashBack2" name="cashback2">
                                    <tr><td><input type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>" name="create-bill">
                                </table>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-6" name="tabular-2">
        <div class="tab col-simple">
            <?= $this->data['media-upload']->render('bill-file', 'files', '', $bill->files); ?>
        </div>

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
                                <td><?= $this->getHtml('Trigger', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('Action', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedBy', 'Auditor', 'Backend'); ?>
                                <td><?= $this->getHtml('CreatedAt', 'Auditor', 'Backend'); ?>
                            <tbody>
                            <?php
                            foreach ($logs as $audit) :
                                $url = UriFactory::build('{/base}/admin/audit/view?id=' . $audit->id);
                            ?>
                            <tr data-href="<?= $url; ?>">
                                <td><a href="<?= $url; ?>"><?= $audit->id; ?></a>
                                <td><a href="<?= $url; ?>"><?= $audit->trigger; ?></a>
                                <td><?php if ($audit->old === null) : echo $this->getHtml('CREATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->old !== null && $audit->new !== null) : echo $this->getHtml('UPDATE', 'Auditor', 'Backend'); ?>
                                    <?php elseif ($audit->new === null) : echo $this->getHtml('DELETE', 'Auditor', 'Backend'); ?>
                                    <?php else : echo $this->getHtml('UNKNOWN', 'Auditor', 'Backend'); ?>
                                    <?php endif; ?>
                                <td><a class="content"
                                    href="<?= UriFactory::build('{/base}/admin/account/settings?id=' . $audit->createdBy->id); ?>"><?= $this->printHtml(
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
