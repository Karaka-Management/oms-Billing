<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use phpOMS\System\File\FileUtils;
use phpOMS\Uri\UriFactory;

// Media helper functions (e.g. file icon generator)
include __DIR__ . '/../../../Media/Theme/Backend/template-functions.php';

/**
 * @var \phpOMS\Views\View $this
 */
/** @var Modules\Billing\Models\Bill $bill */
$bill     = $this->getData('bill');
$elements = $bill->getElements();
$media    = $bill->getMedia();

$previewType = $this->getData('previewType');
$billPdf     = $bill->getFileByType($previewType);

echo $this->getData('nav')->render(); ?>

<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Invoice'); ?></label></li>
            <li><label for="c-tab-2"><?= $this->getHtml('Items'); ?></label></li>
            <li><label for="c-tab-3"><?= $this->getHtml('Preview'); ?></label></li>
            <li><label for="c-tab-4"><?= $this->getHtml('Payment'); ?></label></li>
            <li><label for="c-tab-5"><?= $this->getHtml('Media'); ?></label></li>
            <li><label for="c-tab-6"><?= $this->getHtml('Logs'); ?></label></li>
        </ul>
    </div>
    <div class="tab-content">
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
                                    <tr><td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input type="text" id="iSource" name="source"></span>
                                    <tr><td><label for="iType"><?= $this->getHtml('Type'); ?></label>
                                    <tr><td><select id="iType" name="type">
                                                <option><?= $this->getHtml('Invoice'); ?>
                                                <option><?= $this->getHtml('Offer'); ?>
                                                <option><?= $this->getHtml('Confirmation'); ?>
                                                <option><?= $this->getHtml('DeliveryNote'); ?>
                                                <option><?= $this->getHtml('CreditNote'); ?>
                                            </select>
                                    <tr><td><label for="iClient"><?= $this->getHtml('Client'); ?></label>
                                    <tr><td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input type="text" id="iClient" name="client"></span>
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
                                    <tr><td><input name="" type="text" id="iDRecipient" name="drecipient">
                                    <tr><td><label for="iAddress"><?= $this->getHtml('Address'); ?></label>
                                    <tr><td><input name="" type="text" id="iAddress" name="address">
                                    <tr><td><label for="iZip"><?= $this->getHtml('Zip'); ?></label>
                                    <tr><td><input name="" type="text" id="iZip" name="zip">
                                    <tr><td><label for="iCity"><?= $this->getHtml('City'); ?></label>
                                    <tr><td><input name="" type="text" id="iCity" name="city">
                                    <tr><td><label for="iCountry"><?= $this->getHtml('Country'); ?></label>
                                    <tr><td><input name="" type="text" id="iCountry" name="country">
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
                        <div class="portlet-head"><?= $this->getHtml('Invoice'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <div class="slider">
                        <table class="default" id="invoice-item-list">
                            <thead>
                            <tr>
                                <td>
                                <td><?= $this->getHtml('Item'); ?>
                                <td class="wf-100"><?= $this->getHtml('Name'); ?>
                                <td><?= $this->getHtml('Quantity'); ?>
                                <td><?= $this->getHtml('Price'); ?>
                                <td><?= $this->getHtml('Discount'); ?>
                                <td><?= $this->getHtml('DiscountP'); ?>
                                <td><?= $this->getHtml('Bonus'); ?>
                                <td><?= $this->getHtml('Tax'); ?>
                                <td><?= $this->getHtml('Net'); ?>
                            <tbody>
                            <?php foreach ($elements as $element) : ?>
                            <tr>
                                <td><i class="fa fa-plus add"></i> <i class="fa fa-chevron-up order-up"></i> <i class="fa fa-chevron-down order-down"></i>
                                <td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input name="" type="text" value="<?= $element->itemNumber; ?>" required></span>
                                <td><textarea required><?= $element->itemName; ?></textarea>
                                <td><input name="" type="number" min="0" value="<?= $element->quantity; ?>" required>
                                <td><input name="" type="text" value="<?= $element->singleSalesPriceNet->getCurrency(); ?>">
                                <td><input name="" type="number" min="0">
                                <td><input name="" type="number" min="0" max="100" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td><input name="" type="number" min="0" step="any">
                                <td><?= $element->totalSalesPriceNet->getCurrency(); ?>
                            <?php endforeach; ?>
                            <tr>
                                <td><i class="fa fa-plus"></i> <i class="fa fa-chevron-up order-up"></i> <i class="fa fa-chevron-down order-down"></i>
                                <td><span class="input"><button type="button" formaction=""><i class="fa fa-book"></i></button><input name="" type="text" required></span>
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
                            <?= $this->getHtml('Net'); ?>: <?= $bill->netSales->getCurrency(); ?> -
                            <?= $this->getHtml('Tax'); ?>: 0.00 -
                            <?= $this->getHtml('Total'); ?>: <?= $bill->grossSales->getCurrency(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-3" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <section id="mediaFile" class="portlet">
                        <div class="portlet-body">
                            <iframe style="min-height: 600px;" data-form="iUiSettings" data-name="iframeHelper" id="iHelperFrame" src="<?= UriFactory::build('{/backend}Resources/mozilla/Pdf/web/viewer.html?{?}&file=' . ($billPdf->isAbsolute ? '' : '/../../../../') . $billPdf->getPath()); ?>" allowfullscreen></iframe>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-4" name="tabular-2">
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
        <input type="radio" id="c-tab-5" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6 col-lg-4">
                    <section class="box wf-100">
                        <header><h1><?= $this->getHtml('Media'); ?></h1></header>

                        <div class="inner">
                            <form>
                                <table class="layout wf-100">
                                    <tbody>
                                    <tr><td colspan="2"><label for="iMedia"><?= $this->getHtml('Media'); ?></label>
                                    <tr><td><input type="text" id="iMedia" placeholder="&#xf15b; File"><td><button><?= $this->getHtml('Select'); ?></button>
                                    <tr><td colspan="2"><label for="iUpload"><?= $this->getHtml('Upload'); ?></label>
                                    <tr><td><input type="file" id="iUpload" form="fTask"><input form="fTask" type="hidden" name="type"><td>
                                </table>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-md-6 col-lg-8">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Media'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <table class="default" id="invoice-item-list">
                            <thead>
                            <tr>
                                <td>
                                <td>
                                <td class="wf-100"><?= $this->getHtml('Name'); ?>
                                <td><?= $this->getHtml('Type'); ?>
                            <tbody>
                            <?php foreach ($media as $file) :
                                $url = $file->extension === 'collection'
                                ? UriFactory::build('{/prefix}media/list?path=' . \rtrim($file->getVirtualPath(), '/') . '/' . $file->name)
                                : UriFactory::build('{/prefix}media/single?id=' . $file->getId()
                                    . '&path={?path}' . (
                                            $file->getId() === 0
                                                ? '/' . $file->name
                                                : ''
                                        )
                                );

                                $icon = $fileIconFunction(FileUtils::getExtensionType($file->extension));
                            ?>
                            <tr data-href="<?= $url; ?>">
                                <td>
                                <td data-label="<?= $this->getHtml('Type'); ?>"><a href="<?= $url; ?>"><i class="fa fa-<?= $this->printHtml($icon); ?>"></i></a>
                                <td><a href="<?= $url; ?>"><?= $file->name; ?></a>
                                <td><a href="<?= $url; ?>"><?= $file->extension; ?></a>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <input type="radio" id="c-tab-6" name="tabular-2">
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box wf-100">
                        <table class="default">
                            <caption><?= $this->getHtml('Logs'); ?><i class="fa fa-download floatRight download btn"></i></caption>
                            <thead>
                            <tr>
                                <td>IP
                                <td><?= $this->getHtml('ID', '0', '0'); ?>
                                <td><?= $this->getHtml('Name'); ?>
                                <td class="wf-100"><?= $this->getHtml('Log'); ?>
                                <td><?= $this->getHtml('Date'); ?>
                            <tbody>
                            <tr>
                                <td><?= $this->printHtml($this->request->getOrigin()); ?>
                                <td><?= $this->printHtml((string) $this->request->header->account); ?>
                                <td><?= $this->printHtml((string) $this->request->header->account); ?>
                                <td>Create Invoice
                                <td><?= $this->printHtml((new \DateTime('now'))->format('Y-m-d H:i:s')); ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

