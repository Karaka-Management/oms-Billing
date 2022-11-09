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

use phpOMS\Uri\UriFactory;

$bills = $this->getData('bills') ?? [];

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Bills'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <div class="slider">
            <table id="billList" class="default sticky">
                <thead>
                <tr>
                    <td><label class="checkbox" for="iBillSelect-">
                            <input type="checkbox" id="iBillSelect-" name="billselect">
                            <span class="checkmark"></span>
                        </label>
                    <td><?= $this->getHtml('ID', '0', '0'); ?>
                        <label for="billList-sort-1">
                            <input type="radio" name="billList-sort" id="billList-sort-1">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-2">
                            <input type="radio" name="billList-sort" id="billList-sort-2">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Type'); ?>
                        <label for="billList-sort-3">
                            <input type="radio" name="billList-sort" id="billList-sort-3">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-4">
                            <input type="radio" name="billList-sort" id="billList-sort-4">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('ClientID'); ?>
                        <label for="billList-sort-5">
                            <input type="radio" name="billList-sort" id="billList-sort-5">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-6">
                            <input type="radio" name="billList-sort" id="billList-sort-6">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Client'); ?>
                        <label for="billList-sort-7">
                            <input type="radio" name="billList-sort" id="billList-sort-7">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-8">
                            <input type="radio" name="billList-sort" id="billList-sort-8">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Address'); ?>
                        <label for="billList-sort-9">
                            <input type="radio" name="billList-sort" id="billList-sort-9">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-10">
                            <input type="radio" name="billList-sort" id="billList-sort-10">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Postal'); ?>
                        <label for="billList-sort-11">
                            <input type="radio" name="billList-sort" id="billList-sort-11">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-12">
                            <input type="radio" name="billList-sort" id="billList-sort-12">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('City'); ?>
                        <label for="billList-sort-13">
                            <input type="radio" name="billList-sort" id="billList-sort-13">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-14">
                            <input type="radio" name="billList-sort" id="billList-sort-14">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td class="wf-100"><?= $this->getHtml('Country'); ?>
                        <label for="billList-sort-15">
                            <input type="radio" name="billList-sort" id="billList-sort-15">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-16">
                            <input type="radio" name="billList-sort" id="billList-sort-16">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Net'); ?>
                        <label for="billList-sort-7">
                            <input type="radio" name="billList-sort" id="billList-sort-7">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-18">
                            <input type="radio" name="billList-sort" id="billList-sort-18">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Profit'); ?>
                        <label for="billList-sort-21">
                            <input type="radio" name="billList-sort" id="billList-sort-21">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-22">
                            <input type="radio" name="billList-sort" id="billList-sort-22">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                    <td><?= $this->getHtml('Created'); ?>
                        <label for="billList-sort-23">
                            <input type="radio" name="billList-sort" id="billList-sort-23">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="billList-sort-24">
                            <input type="radio" name="billList-sort" id="billList-sort-24">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                <tbody>
                <?php $count = 0; foreach ($bills as $key => $value) :
                    ++$count;
                    $url = UriFactory::build('sales/bill?{?}&id=' . $value->getId());
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><label class="checkbox" for="iBillSelect-<?= $key; ?>">
                                    <input type="checkbox" id="iBillSelect-<?= $key; ?>" name="billselect">
                                    <span class="checkmark"></span>
                                </label>
                        <td><a href="<?= $url; ?>"><?= $value->getNumber(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->type->getL11n(); ?></a>
                        <td><a class="content" href="<?= $client = UriFactory::build('sales/client/profile?{?}&id=' . $value->client->getId()); ?>"><?= $value->client->number; ?></a>
                        <td><a class="content" href="<?= $client; ?>"><?= $this->printHtml($value->billTo); ?></a>
                        <td><a href="<?= $url;
                         ?>"><?= $value->billAddress; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->billZip; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->billCity; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->billCountry; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->netSales->getCurrency(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->netProfit->getCurrency(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->createdAt->format('Y-m-d'); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="12" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </div>
</div>
