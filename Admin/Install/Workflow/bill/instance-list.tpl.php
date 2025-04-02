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

use phpOMS\Uri\UriFactory;

$instances = $this->data['instances'];

$previous = empty($instances) ? 'workflows/instance/list' : '{/base}/workflows/instance/list?{?}&offset=' . \reset($instances)->id . '&ptype=p';
$next     = empty($instances) ? 'workflows/instance/list' : '{/base}/workflows/instance/list?{?}&offset=' . \end($instances)->id . '&ptype=n';

echo $this->data['nav']->render();
?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Instances'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table id="helperList" class="default sticky">
                <thead>
                <tr>
                    <td><label class="checkbox" for="helperList-0">
                            <input type="checkbox" id="helperList-0" name="helperselect">
                            <span class="checkmark"></span>
                        </label>
                    <td>
                </tr>
                <tbody>
            </table>
            </div>
            <!--
            <div class="portlet-foot">
                <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
            </div>
            -->
        </section>
    </div>
</div>
