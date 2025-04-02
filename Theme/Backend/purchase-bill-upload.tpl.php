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

echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <form method="PUT" id="media-uploader" action="<?= UriFactory::build('{/api}purchase/recognition/upload?csrf={$CSRF}'); ?>">
            <section class="portlet">
                <div class="portlet-head"><?= $this->getHtml('Upload'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <ul>
                            <li><?= $this->getHtml('RecognitionUpload1'); ?>
                            <li><?= $this->getHtml('RecognitionUpload2'); ?>
                            <li><?= $this->getHtml('RecognitionUpload3'); ?>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label for="iFiles"><?= $this->getHtml('Files'); ?></label>
                        <input type="file" id="iFiles" name="files" multiple>
                    </div>
                </div>
                <div class="portlet-foot">
                    <input type="submit" id="iMediaCreate" name="mediaCreateButton" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                </div>
            </section>
        </form>
    </div>
</div>