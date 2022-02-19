<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\ClientManagement
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

/* @todo: single month/quarter/fiscal year/calendar year */
/* @todo: time range (<= 12 month = monthly view; else annual view/comparison) */

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->getData('nav')->render();
?>

<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-2"><?= $this->getHtml('General'); ?></label></li>
            <li><label for="c-tab-1"><?= $this->getHtml('Filter'); ?></label></li>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <form>
                           <div class="portlet-head"><?= $this->getHtml('Filter'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iId"><?= $this->getHtml('Client'); ?></label>
                                    <input type="text" id="iName1" name="name1">
                                </div>

                                <div class="form-group">
                                    <div class="input-control">
                                        <label for="iDecimalPoint"><?= $this->getHtml('BaseTime'); ?></label>
                                        <input id="iDecimalPoint" name="settings_decimal" type="text" value="" placeholder=".">
                                    </div>

                                    <div class="input-control">
                                        <label for="iThousandSep"><?= $this->getHtml('ComparisonTime'); ?></label>
                                        <input id="iThousandSep" name="settings_thousands" type="text" value="" placeholder=",">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-control">
                                        <label for="iDecimalPoint"><?= $this->getHtml('Attribute'); ?></label>
                                        <input id="iDecimalPoint" name="settings_decimal" type="text" value="" placeholder=".">
                                    </div>

                                    <div class="input-control">
                                        <label for="iThousandSep"><?= $this->getHtml('Value'); ?></label>
                                        <input id="iThousandSep" name="settings_thousands" type="text" value="" placeholder=",">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="iId"><?= $this->getHtml('Region'); ?></label>
                                    <input type="text" id="iName1" name="name1">
                                </div>

                                <div class="form-group">
                                    <label for="iId"><?= $this->getHtml('Country'); ?></label>
                                    <input type="text" id="iName1" name="name1">
                                </div>

                                <div class="form-group">
                                    <label for="iId"><?= $this->getHtml('Rep'); ?></label>
                                    <input type="text" id="iName1" name="name1">
                                </div>
                            </div>
                            <div class="portlet-foot"><input id="iSubmitGeneral" name="submitGeneral" type="submit" value="<?= $this->getHtml('Save', '0', '0'); ?>"></div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-2" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales per Rep - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customersRep = $this->getData('currentCustomersRep'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "horizontalBar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customersRep as $name => $rep) {
                                                            $temp[] = $name;
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Customers'); ?>",
                                                        "type": "horizontalBar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customersRep as $values) {
                                                                    $temp[] = ((int) $values['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Customers per rep"
                                                }
                                            }
                                    }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-rep-current" type="checkbox">
                                <label for="more-customer-rep-current">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Rep
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customersRep as $rep => $values) : $sum += $values['customers']; ?>
                                            <tr>
                                                <td><?= $rep; ?>
                                                <td><?= $values['customers']; ?>
                                        <?php endforeach; ?>
                                            <tr>
                                                <td>Total
                                                <td><?= $sum; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales per Rep - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customersRep = $this->getData('annualCustomersRep'); ?>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Rep
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year) : ?>
                                                <td><?= $year; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                            $sum = [];
                                        foreach ($customersRep as $rep => $annual) : ?>
                                            <tr>
                                                <td><?= $rep; ?>
                                                <?php foreach ($annual as $year => $values) :
                                                    $sum[$values['year']] = ($sum[$values['year']] ?? 0) + $values['customers']; ?>
                                                <td><?= $values['customers']; ?>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td>Total
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year) : ?>
                                                <td><?= $sum[$year]; ?>
                                            <?php endforeach; ?>
                                </table>
                    </section>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Profit per Rep - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customersRep = $this->getData('currentCustomersRep'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "horizontalBar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customersRep as $name => $rep) {
                                                            $temp[] = $name;
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Customers'); ?>",
                                                        "type": "horizontalBar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customersRep as $values) {
                                                                    $temp[] = ((int) $values['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Customers per rep"
                                                }
                                            }
                                    }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-rep-current" type="checkbox">
                                <label for="more-customer-rep-current">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Rep
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customersRep as $rep => $values) : $sum += $values['customers']; ?>
                                            <tr>
                                                <td><?= $rep; ?>
                                                <td><?= $values['customers']; ?>
                                        <?php endforeach; ?>
                                            <tr>
                                                <td>Total
                                                <td><?= $sum; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Profit per Rep - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customersRep = $this->getData('annualCustomersRep'); ?>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Rep
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year) : ?>
                                                <td><?= $year; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                            $sum = [];
                                        foreach ($customersRep as $rep => $annual) : ?>
                                            <tr>
                                                <td><?= $rep; ?>
                                                <?php foreach ($annual as $year => $values) :
                                                    $sum[$values['year']] = ($sum[$values['year']] ?? 0) + $values['customers']; ?>
                                                <td><?= $values['customers']; ?>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td>Total
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year) : ?>
                                                <td><?= $sum[$year]; ?>
                                            <?php endforeach; ?>
                                </table>
                    </section>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales / Rep
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <table class="default">
                            <thead>
                                <tr>
                                    <td>Rep
                                    <td>Sales PY
                                    <td>Sales B
                                    <td>Sales A
                                    <td>Diff PY
                                    <td>Diff B
                            <tbody>
                        </table>
                   </section>
                </div>
            </div>
        </div>
    </div>
</div>