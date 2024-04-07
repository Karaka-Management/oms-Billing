<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Accounting
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Billing\Models\Tax\BillTaxType;
use Modules\Billing\Models\Tax\NullTaxCombination;
use phpOMS\Uri\UriFactory;

$taxcombination = $this->data['taxcombination'] ?? new NullTaxCombination();
$isNew = $taxcombination->id === 0;

$combinationType = BillTaxType::getConstants();

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <form method="<?= $isNew ? 'PUT' : 'POST'; ?>" action="<?= UriFactory::build('{/api}finance/tax/combination?csrf={$CSRF}'); ?>">
                <div class="portlet-head"><?= $this->getHtml('TaxCode'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iId"><?= $this->getHtml('ID', '0', '0'); ?></label>
                        <input type="text" name="id" id="iId" value="<?= $taxcombination->id; ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="iType"><?= $this->getHtml('Type'); ?></label>
                        <select id="iType" name="type"
                            data-action='[
                                {"key": 1, "listener": "change", "action": [
                                    {"key": 1, "type": "dom.get", "base": "", "selector": "#iType"},
                                    {"key": 2, "type": "if", "conditions": [
                                        {
                                            "comp": "==",
                                            "value": "1",
                                            "jump": 3
                                        },
                                        {
                                            "comp": "==",
                                            "value": "2",
                                            "jump": 8
                                        }
                                    ]},
                                    {"key": 3, "type": "dom.attr.change", "subtype": "add", "attr": "class", "value": "vh", "base": "", "selector": "#iSupplierGroup"},
                                    {"key": 4, "type": "dom.attr.change", "subtype": "add", "attr": "class", "value": "vh", "base": "", "selector": "#iItemPurchaseGroup"},
                                    {"key": 5, "type": "dom.attr.change", "subtype": "remove", "attr": "class", "value": "vh", "base": "", "selector": "#iItemSalesGroup"},
                                    {"key": 6, "type": "dom.attr.change", "subtype": "remove", "attr": "class", "value": "vh", "base": "", "selector": "#iClientGroup"},
                                    {"key": 7, "type": "jump", "jump": 12},
                                    {"key": 8, "type": "dom.attr.change", "subtype": "add", "attr": "class", "value": "vh", "base": "", "selector": "#iClientGroup"},
                                    {"key": 9, "type": "dom.attr.change", "subtype": "add", "attr": "class", "value": "vh", "base": "", "selector": "#iItemSalesGroup"},
                                    {"key": 10, "type": "dom.attr.change", "subtype": "remove", "attr": "class", "value": "vh", "base": "", "selector": "#iItemPurchaseGroup"},
                                    {"key": 11, "type": "dom.attr.change", "subtype": "remove", "attr": "class", "value": "vh", "base": "", "selector": "#iSupplierGroup"}
                                ]}
                            ]'>
                            <?php foreach ($combinationType as $type) : ?>
                                <option value="<?= $type; ?>"<?= $taxcombination->taxType === $type ? ' selected': ''; ?>><?= $this->getHtml(':combinationtype-' . $type); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="iClientGroup" class="form-group<?= $taxcombination->taxType === BillTaxType::PURCHASE ? ' vh' : ''; ?>">
                        <label for="iClient"><?= $this->getHtml('Client'); ?></label>
                        <select id="iClient" name="account_code">
                            <option value=""<?= $taxcombination->clientCode === null ? ' selected' : ''; ?>>
                            <?php foreach ($this->data['client_codes']->defaults as $code) : ?>
                                <option value="<?= $code->id; ?>"<?= $taxcombination->clientCode?->id === $code->id ? ' selected': ''; ?>><?= $this->printHtml($code->getValue()); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="iItemSalesGroup" class="form-group<?= $taxcombination->taxType === BillTaxType::PURCHASE ? ' vh' : ''; ?>">
                        <label for="iItemSales"><?= $this->getHtml('Item'); ?></label>
                        <select id="iItemSales" name="item_code">
                            <option value=""<?= $taxcombination->itemCode === null ? ' selected' : ''; ?>>
                            <?php foreach ($this->data['item_codes_sales']->defaults as $code) : ?>
                                <option value="<?= $code->id; ?>"<?= $taxcombination->itemCode?->id === $code->id ? ' selected': ''; ?>><?= $this->printHtml($code->getValue()); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="iSupplierGroup" class="form-group<?= $taxcombination->taxType === BillTaxType::SALES ? ' vh' : ''; ?>">
                        <label for="iSupplier"><?= $this->getHtml('Supplier'); ?></label>
                        <select id="iSupplier" name="account_code">
                            <option value=""<?= $taxcombination->supplierCode === null ? ' selected' : ''; ?>>
                            <?php foreach ($this->data['supplier_codes']->defaults as $code) : ?>
                                <option value="<?= $code->id; ?>"<?= $taxcombination->supplierCode?->id === $code->id ? ' selected': ''; ?>><?= $this->printHtml($code->getValue()); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="iItemPurchaseGroup" class="form-group<?= $taxcombination->taxType === BillTaxType::SALES ? ' vh' : ''; ?>">
                        <label for="iItemPurchase"><?= $this->getHtml('Item'); ?></label>
                        <select id="iItemPurchase" name="item_code">
                            <option value=""<?= $taxcombination->itemCode === null ? ' selected' : ''; ?>>
                            <?php foreach ($this->data['item_codes_purchase']->defaults as $code) : ?>
                                <option value="<?= $code->id; ?>"<?= $taxcombination->itemCode->id === $code->id ? ' selected': ''; ?>><?= $this->printHtml($code->getValue()); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="iTaxCode"><?= $this->getHtml('TaxCode'); ?></label>
                        <select id="iTaxCode" name="tax_code">
                            <?php foreach ($this->data['tax_codes'] as $code) : ?>
                                <option value="<?= $code->id; ?>"<?= $taxcombination->taxCode->id === $code->id ? ' selected': ''; ?>><?= $this->printHtml($code->abbr); ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="iPLAccount"><?= $this->getHtml('PLAccount'); ?></label>
                        <input type="text" name="account" id="iPLAccount" value="<?= $this->printHtml($taxcombination->taxAccount1); ?>">
                    </div>

                    <div class="form-group">
                        <label for="iTax1Account"><?= $this->getHtml('Tax1Account'); ?></label>
                        <input type="text" name="tax1" id="iTax1Account" value="<?= $this->printHtml($taxcombination->taxAccount1); ?>">
                    </div>

                    <div class="form-group">
                        <label for="iTax2Account"><?= $this->getHtml('Tax2Account'); ?></label>
                        <input type="text" name="tax2" id="iTax2Account" value="<?= $this->printHtml($taxcombination->taxAccount2); ?>">
                    </div>
                </div>
                <div class="portlet-foot">
                    <?php if ($isNew) : ?>
                        <input id="iCreateSubmit" type="Submit" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                    <?php else : ?>
                        <input id="iSaveSubmit" type="Submit" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </div>
</div>
