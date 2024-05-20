<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Localization\BaseStringL11nType */
$type = $this->data['type'];

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <form id="shippingForm" method="POST" action="<?= UriFactory::build('{/api}bill/shipping?csrf={$CSRF}'); ?>"
                data-ui-container="#shippingTable tbody"
                data-add-form="shippingForm"
                data-add-tpl="#shippingTable tbody .oms-add-tpl-shipping">
                <div class="portlet-head"><?= $this->getHtml('ShippingTerm'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iName"><?= $this->getHtml('Name'); ?></label>
                        <input type="text" name="code" id="iName" value="<?= $this->printHtml($type->title); ?>">
                    </div>
                </div>

                <div class="portlet-foot">
                    <input type="hidden" name="id" value="<?= $type->id; ?>">
                    <input id="iSubmit" name="submit" type="submit" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                </div>
            </form>
        </section>
    </div>
</div>

<div class="row">
    <?= $this->data['l11nView']->render(
        $this->data['l11nValues'],
        [],
        '{/api}bill/shipping/l11n?csrf={$CSRF}'
    );
    ?>
</div>