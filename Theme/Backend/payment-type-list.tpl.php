<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\ItemManagement
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$types = $this->data['types'];

echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('PaymentTerms'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table id="iContractTypeList" class="default sticky">
                <thead>
                <tr>
                    <td><?= $this->getHtml('ID', '0', '0'); ?>
                        <label for="iContractTypeList-sort-1">
                            <input type="radio" name="iContractTypeList-sort" id="iContractTypeList-sort-1">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="iContractTypeList-sort-2">
                            <input type="radio" name="iContractTypeList-sort" id="iContractTypeList-sort-2">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Name'); ?>
                        <label for="iContractTypeList-sort-2">
                            <input type="radio" name="iContractTypeList-sort" id="iContractTypeList-sort-2">
                            <i class="sort-asc g-icon">expand_less</i>
                        </label>
                        <label for="iContractTypeList-sort-3">
                            <input type="radio" name="iContractTypeList-sort" id="iContractTypeList-sort-3">
                            <i class="sort-desc g-icon">expand_more</i>
                        </label>
                        <label>
                            <i class="filter g-icon">filter_alt</i>
                        </label>
                    <tbody>
                <?php
                $count = 0;
                foreach ($types as $key => $value) : ++$count;
                    $url = UriFactory::build('{/base}/bill/payment/view?{?}&id=' . $value->id);
                ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>"><?= $value->id; ?></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->getL11n()); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="2" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            </div>
        </section>
    </div>
</div>
