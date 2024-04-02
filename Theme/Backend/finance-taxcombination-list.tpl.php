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

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View               $this
 * @var \Modules\Tag\Models\CostCenter[] $taxcombination
 */
$taxcombination = $this->data['taxcombination'];

$previous = empty($taxcombination) ? '{/base}/finance/tax/combination/list' : '{/base}/finance/tax/combination/list?{?}&offset=' . \reset($taxcombination)->id . '&ptype=p';
$next     = empty($taxcombination) ? '{/base}/finance/tax/combination/list' : '{/base}/finance/tax/combination/list?{?}&offset=' . \end($taxcombination)->id . '&ptype=n';

echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('TaxCombinations'); ?><i class="g-icon download btn end-xs">download</i></div>
            <table class="default sticky">
            <thead>
            <tr>
                <td><?= $this->getHtml('ID', '0', '0'); ?>
                <td><?= $this->getHtml('Client'); ?>
                <td><?= $this->getHtml('Supplier'); ?>
                <td class="wf-100"><?= $this->getHtml('Item'); ?>
                <td><?= $this->getHtml('TaxCode'); ?>
                <td><?= $this->getHtml('Tax'); ?>
                <td><?= $this->getHtml('PL'); ?>
            <tbody>
            <?php $count = 0;
            foreach ($taxcombination as $key => $value) : ++$count;
            $url = UriFactory::build('{/base}/finance/tax/combination/view?{?}&id=' . $value->id); ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>">
                        <?= $value->id; ?></a>
                    <td><a href="<?= $url; ?>">
                        <?= $this->printHtml($value->clientCode->getValue()); ?></a>
                    <td><a href="<?= $url; ?>">
                        <?= $this->printHtml($value->supplierCode->getValue()); ?></a>
                    <td><a href="<?= $url; ?>">
                        <?= $this->printHtml($value->itemCode->getValue()); ?></a>
                    <td><a href="<?= $url; ?>">
                        <?= $this->printHtml($value->taxCode->abbr); ?></a>
                    <td><a href="<?= $url; ?>">
                        <?= $value->taxCode->percentageInvoice / 10000; ?> %</a>
                    <td><a href="<?= $url; ?>">
                        <?= $this->printHtml($value->account); ?></a>
            <?php endforeach; ?>
            <?php if ($count === 0) : ?>
                <tr><td colspan="7" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
        </table>
        <!--
        <div class="portlet-foot">
            <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
            <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
        </div>
        -->
    </div>
</div>
