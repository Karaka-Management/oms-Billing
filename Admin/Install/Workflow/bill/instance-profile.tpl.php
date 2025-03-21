<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Workflow
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

echo $this->data['nav']->render();
?>
<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Overview'); ?></label>
            <li><label for="c-tab-1"><?= $this->getHtml('Bill'); ?></label>
            <li><label for="c-tab-1"><?= $this->getHtml('CreditLimit'); ?></label>
            <li><label for="c-tab-1"><?= $this->getHtml('PaymentTerm'); ?></label>
            <li><label for="c-tab-2"><?= $this->getHtml('Elements'); ?></label>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    // create colored list (grey, red, green) of the different steps
                    // grey = no action needed
                    // yellow = action needed
                    // green = approved
                    // red = declined
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-1" name="tabular-2"<?= empty($this->request->uri->fragment) || $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <section class="portlet">
                        <div class="portlet-head">Profile</div>
                        <div class="portlet-body"></div>
                        <div class="portlet-foot"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
