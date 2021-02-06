<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

$bills = $this->getData('bills') ?? [];

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Invoices'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table id="billList" class="default">
                <thead>
                <tr>
                    <td><?= $this->getHtml('Id', '', ''); ?>
                    <td><?= $this->getHtml('Type'); ?>
                    <td><?= $this->getHtml('ClientID'); ?>
                    <td class="wf-100"><?= $this->getHtml('Client'); ?>
                    <td class="wf-100"><?= $this->getHtml('Address'); ?>
                    <td class="wf-100"><?= $this->getHtml('Postal'); ?>
                    <td class="wf-100"><?= $this->getHtml('City'); ?>
                    <td class="wf-100"><?= $this->getHtml('Country'); ?>
                    <td><?= $this->getHtml('Net'); ?>
                    <td><?= $this->getHtml('Gross'); ?>
                    <td><?= $this->getHtml('Profit'); ?>
                    <td><?= $this->getHtml('Created'); ?>
                <tbody>
                <?php $count = 0; foreach ($bills as $key => $value) :
                    ++$count;
                    $url = UriFactory::build('{/prefix}sales/invoice?{?}&id=' . $value->getId());
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><?= $value->getNumber(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->type->getL11n(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->client->number; ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($value->client->profile->account->name3 . ' ' . $value->client->profile->account->name2 . ' ' . $value->client->profile->account->name1); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->client->mainAddress->address; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->client->mainAddress->postal; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->client->mainAddress->city; ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->client->mainAddress->getCountry(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->net->getCurrency(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->gross->getCurrency(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->profit->getCurrency(); ?></a>
                        <td><a href="<?= $url; ?>"><?= $value->createdAt->format('Y-m-d'); ?></a>
                <?php endforeach; ?>
                <?php if ($count === 0) : ?>
                    <tr><td colspan="9" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

