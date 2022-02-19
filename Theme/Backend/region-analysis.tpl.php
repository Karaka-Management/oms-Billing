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

use phpOMS\Localization\Money;

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
            <li><label for="c-tab-3"><?= $this->getHtml('Region'); ?></label></li>
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
                            Sales per Region - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('currentCustomerRegion'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                        "type": "pie",
                                        "data": {
                                            "labels": [
                                                    "Europe", "America", "Asia", "Africa", "CIS", "Other"
                                                ],
                                            "datasets": [{
                                                "data": [
                                                    <?= (int) ($customerRegion['Europe'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['America'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Asia'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Africa'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['CIS'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Other'] ?? 0); ?>
                                                ],
                                                "backgroundColor": [
                                                    "rgb(255, 99, 132)",
                                                    "rgb(255, 159, 64)",
                                                    "rgb(255, 205, 86)",
                                                    "rgb(75, 192, 192)",
                                                    "rgb(54, 162, 235)",
                                                    "rgb(153, 102, 255)"
                                                ]
                                            }]
                                        },
                                        "options": {
                                            "title": {
                                                "display": false,
                                                "text": "Customers per Region - Currently"
                                            }
                                        }
                                }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-region" type="checkbox">
                                <label for="more-customer-region">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customerRegion as $region => $values) : $sum += $values; ?>
                                            <tr>
                                                <td><?= $region; ?>
                                                <td><?= $values; ?>
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
                            Sales per Region - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('annualCustomerRegion'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "line",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerRegion as $annual) {
                                                            $temp[] = (string) $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Total'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Total'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(139, 139, 139)",
                                                        "backgroundColor": "rgb(139, 139, 139)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Europe'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Europe'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('America'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['America'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 159, 64)",
                                                        "backgroundColor": "rgb(255, 159, 64)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Asia'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Asia'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 205, 86)",
                                                        "backgroundColor": "rgb(255, 205, 86)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Africa'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Africa'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(75, 192, 192)",
                                                        "backgroundColor": "rgb(75, 192, 192)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('CIS'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['CIS'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Other'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Other'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(153, 102, 255)",
                                                        "backgroundColor": "rgb(153, 102, 255)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Sales per Region - Annual"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-region-annual" type="checkbox">
                                <label for="more-customer-region-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year => $values) : ?>
                                                <td><?= $values; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                        $regions = [
                                            'Europe',
                                            'America',
                                            'Asia',
                                            'Africa',
                                            'CIS',
                                            'Other',
                                            'Total',
                                        ];
                                        foreach ($regions as $region) : ?>
                                                <tr>
                                                    <td><?= $region; ?>
                                                <?php foreach ($customerRegion as $year => $annual) : ?>
                                                    <td><?= $annual[$region] ?? 0; ?>
                                        <?php endforeach; endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Profit per Region - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('currentCustomerRegion'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                        "type": "pie",
                                        "data": {
                                            "labels": [
                                                    "Europe", "America", "Asia", "Africa", "CIS", "Other"
                                                ],
                                            "datasets": [{
                                                "data": [
                                                    <?= (int) ($customerRegion['Europe'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['America'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Asia'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Africa'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['CIS'] ?? 0); ?>,
                                                    <?= (int) ($customerRegion['Other'] ?? 0); ?>
                                                ],
                                                "backgroundColor": [
                                                    "rgb(255, 99, 132)",
                                                    "rgb(255, 159, 64)",
                                                    "rgb(255, 205, 86)",
                                                    "rgb(75, 192, 192)",
                                                    "rgb(54, 162, 235)",
                                                    "rgb(153, 102, 255)"
                                                ]
                                            }]
                                        },
                                        "options": {
                                            "title": {
                                                "display": false,
                                                "text": "Customers per Region - Currently"
                                            }
                                        }
                                }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-region" type="checkbox">
                                <label for="more-customer-region">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customerRegion as $region => $values) : $sum += $values; ?>
                                            <tr>
                                                <td><?= $region; ?>
                                                <td><?= $values; ?>
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
                            Profit per Region - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('annualCustomerRegion'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "line",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerRegion as $annual) {
                                                            $temp[] = (string) $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Total'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Total'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(139, 139, 139)",
                                                        "backgroundColor": "rgb(139, 139, 139)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Europe'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Europe'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('America'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['America'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 159, 64)",
                                                        "backgroundColor": "rgb(255, 159, 64)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Asia'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Asia'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 205, 86)",
                                                        "backgroundColor": "rgb(255, 205, 86)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Africa'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Africa'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(75, 192, 192)",
                                                        "backgroundColor": "rgb(75, 192, 192)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('CIS'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['CIS'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Other'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Other'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(153, 102, 255)",
                                                        "backgroundColor": "rgb(153, 102, 255)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Sales per Region - Annual"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>

                            <div class="more-container">
                                <input id="more-customer-region-annual" type="checkbox">
                                <label for="more-customer-region-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year => $values) : ?>
                                                <td><?= $values; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                        $regions = [
                                            'Europe',
                                            'America',
                                            'Asia',
                                            'Africa',
                                            'CIS',
                                            'Other',
                                            'Total',
                                        ];
                                        foreach ($regions as $region) : ?>
                                                <tr>
                                                    <td><?= $region; ?>
                                                <?php foreach ($customerRegion as $year => $annual) : ?>
                                                    <td><?= $annual[$region] ?? 0; ?>
                                        <?php endforeach; endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales / Region
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <table class="default">
                            <thead>
                                <tr>
                                    <td>Country
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

        <input type="radio" id="c-tab-3" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-3' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <form>
                           <div class="portlet-head"><?= $this->getHtml('Filter'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iId"><?= $this->getHtml('Region'); ?></label>
                                    <input type="text" id="iName1" name="name1">
                                </div>
                            </div>
                            <div class="portlet-foot"><input id="iSubmitGeneral" name="submitGeneral" type="submit" value="<?= $this->getHtml('Analyse'); ?>"></div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales / Profit - Monthly
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $salesCustomer = $this->getData('monthlySalesCustomer'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "bar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($salesCustomer as $monthly) {
                                                            $temp[] = $monthly['month'] . '/' . \substr((string) $monthly['year'], -2);
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Profit'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $monthly) {
                                                                    $temp[] = ((int) $monthly['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-2",
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Sales'); ?>",
                                                        "type": "bar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $monthly) {
                                                                    $temp[] = ((int) $monthly['net_sales']) / 1000;
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-1",
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
                                                    "text": "Sales / Profit"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        },
                                                        {
                                                            "id": "axis-2",
                                                            "display": true,
                                                            "position": "right",
                                                            "scaleLabel": {
                                                                "display": true,
                                                                "labelString": "<?= $this->getHtml('Profit'); ?>"
                                                            },
                                                            "gridLines": {
                                                                "display": false
                                                            }
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                            <div class="more-container">
                                <input id="more-scustomer-sales" type="checkbox">
                                <label for="more-scustomer-sales">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Month
                                            <td>Sales
                                            <td>Profit
                                            <td>Profit %
                                    <tbody>
                                        <?php
                                            $sum1 = 0;
                                            $sum2 = 0;
                                        foreach ($salesCustomer as $values) :
                                            $sum1 += ((int) $values['net_sales']) / 1000;
                                            $sum2 += ((int) $values['customers']);
                                        ?>
                                            <tr>
                                                <td><?= $values['month'] . '/' . \substr((string) $values['year'], -2); ?>
                                                <td><?= (new Money(((int) $values['net_sales']) / 1000))->getCurrency(); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                        <?php endforeach; ?>
                                            <tr>
                                                <td>Total
                                                <td><?= (new Money($sum1))->getCurrency(); ?>
                                                <td><?= (int) ($sum2 / 12); ?>
                                                <td><?= (int) ($sum2 / 12); ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales / Profit - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $salesCustomer = $this->getData('annualSalesCustomer'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-customer-annual" data-chart='{
                                            "type": "bar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($salesCustomer as $annual) {
                                                            $temp[] = $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Profit'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $annual) {
                                                                    $temp[] = ((int) $annual['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-2",
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Sales'); ?>",
                                                        "type": "bar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $annual) {
                                                                    $temp[] = ((int) $annual['net_sales']) / 1000;
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-1",
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
                                                    "text": "Sales / Profit"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        },
                                                        {
                                                            "id": "axis-2",
                                                            "display": true,
                                                            "position": "right",
                                                            "scaleLabel": {
                                                                "display": true,
                                                                "labelString": "<?= $this->getHtml('Profit'); ?>"
                                                            },
                                                            "gridLines": {
                                                                "display": false
                                                            }
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                            <div class="more-container">
                                <input id="more-scustomer-sales-annual" type="checkbox">
                                <label for="more-scustomer-sales-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Year
                                            <td>Sales
                                            <td>Profit
                                            <td>Profit %
                                    <tbody>
                                        <?php
                                        foreach ($salesCustomer as $values) :
                                        ?>
                                            <tr>
                                                <td><?= (string) $values['year']; ?>
                                                <td><?= (new Money(((int) $values['net_sales']) / 1000))->getCurrency(); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                        <?php endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Sales per Attribute - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerGroups = $this->getData('customerGroups'); ?>
                        <div class="portlet-body">
                            <div class="form-group">
                                <label for="iOname"><?= $this->getHtml('Attribute'); ?></label>
                                <select id="iOname" name="settings_1000000009">
                                    <option>Attribute to Analyse
                                </select>
                            </div>

                            <div>
                            <canvas id="sales-region" data-chart='{
                                        "type": "pie",
                                        "data": {
                                            "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerGroups as $name => $groups) {
                                                            $temp[] = $name;
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                            "datasets": [{
                                                "data": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerGroups as $values) {
                                                            $temp[] = ((int) $values['customers']);
                                                        }
                                                    ?>
                                                    <?= \implode(',', $temp); ?>
                                                ],
                                                "backgroundColor": [
                                                    "rgb(255, 99, 132)",
                                                    "rgb(255, 159, 64)",
                                                    "rgb(255, 205, 86)",
                                                    "rgb(75, 192, 192)",
                                                    "rgb(54, 162, 235)",
                                                    "rgb(153, 102, 255)"
                                                ]
                                            }]
                                        },
                                        "options": {
                                            "title": {
                                                "display": false,
                                                "text": "Customers per group"
                                            }
                                        }
                                }'></canvas>
                            </div>

                            <div class="more-container">
                                <input id="more-customer-attribute-current" type="checkbox">
                                <label for="more-customer-attribute-current">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Groups
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customerGroups as $groups => $values) : $sum += $values['customers']; ?>
                                            <tr>
                                                <td><?= $groups; ?>
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
                            Sales per Attribute - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('annualCustomerRegion'); ?>
                        <div class="portlet-body">
                            <div class="form-group">
                                <label for="iOname"><?= $this->getHtml('Attribute'); ?></label>
                                <select id="iOname" name="settings_1000000009">
                                    <option>Attribute to Analyse
                                </select>
                            </div>

                            <div>
                            <canvas id="sales-region" data-chart='{
                                            "type": "line",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerRegion as $annual) {
                                                            $temp[] = (string) $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Total'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Total'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(139, 139, 139)",
                                                        "backgroundColor": "rgb(139, 139, 139)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Europe'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Europe'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('America'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['America'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 159, 64)",
                                                        "backgroundColor": "rgb(255, 159, 64)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Asia'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Asia'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 205, 86)",
                                                        "backgroundColor": "rgb(255, 205, 86)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Africa'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Africa'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(75, 192, 192)",
                                                        "backgroundColor": "rgb(75, 192, 192)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('CIS'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['CIS'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Other'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Other'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(153, 102, 255)",
                                                        "backgroundColor": "rgb(153, 102, 255)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Customers per Region - Annual"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                                </div>

                            <div class="more-container">
                                <input id="more-customer-attribute-annual" type="checkbox">
                                <label for="more-customer-attribute-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year => $values) : ?>
                                                <td><?= $values; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                        $regions = [
                                            'Europe',
                                            'America',
                                            'Asia',
                                            'Africa',
                                            'CIS',
                                            'Other',
                                            'Total',
                                        ];
                                        foreach ($regions as $region) : ?>
                                                <tr>
                                                    <td><?= $region; ?>
                                                <?php foreach ($customerRegion as $year => $annual) : ?>
                                                    <td><?= $annual[$region] ?? 0; ?>
                                        <?php endforeach; endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Profit per Attribute - Current
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerGroups = $this->getData('customerGroups'); ?>
                        <div class="portlet-body">
                            <div class="form-group">
                                <label for="iOname"><?= $this->getHtml('Attribute'); ?></label>
                                <select id="iOname" name="settings_1000000009">
                                    <option>Attribute to Analyse
                                </select>
                            </div>

                            <div>
                            <canvas id="sales-region" data-chart='{
                                        "type": "pie",
                                        "data": {
                                            "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerGroups as $name => $groups) {
                                                            $temp[] = $name;
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                            "datasets": [{
                                                "data": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerGroups as $values) {
                                                            $temp[] = ((int) $values['customers']);
                                                        }
                                                    ?>
                                                    <?= \implode(',', $temp); ?>
                                                ],
                                                "backgroundColor": [
                                                    "rgb(255, 99, 132)",
                                                    "rgb(255, 159, 64)",
                                                    "rgb(255, 205, 86)",
                                                    "rgb(75, 192, 192)",
                                                    "rgb(54, 162, 235)",
                                                    "rgb(153, 102, 255)"
                                                ]
                                            }]
                                        },
                                        "options": {
                                            "title": {
                                                "display": false,
                                                "text": "Customers per group"
                                            }
                                        }
                                }'></canvas>
                            </div>

                            <div class="more-container">
                                <input id="more-customer-attribute-current" type="checkbox">
                                <label for="more-customer-attribute-current">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Groups
                                            <td>Customer count
                                    <tbody>
                                        <?php
                                            $sum = 0;
                                        foreach ($customerGroups as $groups => $values) : $sum += $values['customers']; ?>
                                            <tr>
                                                <td><?= $groups; ?>
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
                            Profit per Attribute - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $customerRegion = $this->getData('annualCustomerRegion'); ?>
                        <div class="portlet-body">
                            <div class="form-group">
                                <label for="iOname"><?= $this->getHtml('Attribute'); ?></label>
                                <select id="iOname" name="settings_1000000009">
                                    <option>Attribute to Analyse
                                </select>
                            </div>

                            <div>
                            <canvas id="sales-region" data-chart='{
                                            "type": "line",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($customerRegion as $annual) {
                                                            $temp[] = (string) $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Total'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Total'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(139, 139, 139)",
                                                        "backgroundColor": "rgb(139, 139, 139)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Europe'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Europe'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('America'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['America'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 159, 64)",
                                                        "backgroundColor": "rgb(255, 159, 64)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Asia'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Asia'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 205, 86)",
                                                        "backgroundColor": "rgb(255, 205, 86)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Africa'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Africa'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(75, 192, 192)",
                                                        "backgroundColor": "rgb(75, 192, 192)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('CIS'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['CIS'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(54, 162, 235)",
                                                        "backgroundColor": "rgb(54, 162, 235)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Other'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($customerRegion as $annual) {
                                                                    $temp[] = ((int) ($annual['Other'] ?? 0));
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "fill": false,
                                                        "borderColor": "rgb(153, 102, 255)",
                                                        "backgroundColor": "rgb(153, 102, 255)",
                                                        "tension": 0.0
                                                    }
                                                ]
                                            },
                                            "options": {
                                                "title": {
                                                    "display": false,
                                                    "text": "Customers per Region - Annual"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                                </div>

                            <div class="more-container">
                                <input id="more-customer-attribute-annual" type="checkbox">
                                <label for="more-customer-attribute-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Region
                                            <?php foreach ([2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020] as $year => $values) : ?>
                                                <td><?= $values; ?>
                                            <?php endforeach; ?>
                                    <tbody>
                                        <?php
                                        $regions = [
                                            'Europe',
                                            'America',
                                            'Asia',
                                            'Africa',
                                            'CIS',
                                            'Other',
                                            'Total',
                                        ];
                                        foreach ($regions as $region) : ?>
                                                <tr>
                                                    <td><?= $region; ?>
                                                <?php foreach ($customerRegion as $year => $annual) : ?>
                                                    <td><?= $annual[$region] ?? 0; ?>
                                        <?php endforeach; endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Invoices / Articles - Monthly
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $salesCustomer = $this->getData('monthlySalesCustomer'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-region" data-chart='{
                                            "type": "bar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($salesCustomer as $monthly) {
                                                            $temp[] = $monthly['month'] . '/' . \substr((string) $monthly['year'], -2);
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Articles'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $monthly) {
                                                                    $temp[] = ((int) $monthly['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-2",
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Sales'); ?>",
                                                        "type": "bar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $monthly) {
                                                                    $temp[] = ((int) $monthly['net_sales']) / 1000;
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-1",
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
                                                    "text": "Sales / Articles"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        },
                                                        {
                                                            "id": "axis-2",
                                                            "display": true,
                                                            "position": "right",
                                                            "scaleLabel": {
                                                                "display": true,
                                                                "labelString": "<?= $this->getHtml('Articles'); ?>"
                                                            },
                                                            "gridLines": {
                                                                "display": false
                                                            }
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                            <div class="more-container">
                                <input id="more-scustomer-sales" type="checkbox">
                                <label for="more-scustomer-sales">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Month
                                            <td>Sales
                                            <td>Articles
                                    <tbody>
                                        <?php
                                            $sum1 = 0;
                                            $sum2 = 0;
                                        foreach ($salesCustomer as $values) :
                                            $sum1 += ((int) $values['net_sales']) / 1000;
                                            $sum2 += ((int) $values['customers']);
                                        ?>
                                            <tr>
                                                <td><?= $values['month'] . '/' . \substr((string) $values['year'], -2); ?>
                                                <td><?= (new Money(((int) $values['net_sales']) / 1000))->getCurrency(); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                        <?php endforeach; ?>
                                            <tr>
                                                <td>Total
                                                <td><?= (new Money($sum1))->getCurrency(); ?>
                                                <td><?= (int) ($sum2 / 12); ?>
                                                <td><?= (int) ($sum2 / 12); ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-xs-12 col-lg-6">
                    <section class="portlet">
                        <div class="portlet-head">
                            Invoices / Articles - Annual
                            <?php include __DIR__ . '/../../../../Web/Backend/Themes/popup-export-data.tpl.php'; ?>
                        </div>
                        <?php $salesCustomer = $this->getData('annualSalesCustomer'); ?>
                        <div class="portlet-body">
                            <canvas id="sales-customer-annual" data-chart='{
                                            "type": "bar",
                                            "data": {
                                                "labels": [
                                                    <?php
                                                        $temp = [];
                                                        foreach ($salesCustomer as $annual) {
                                                            $temp[] = $annual['year'];
                                                        }
                                                    ?>
                                                    <?= '"' . \implode('", "', $temp) . '"'; ?>
                                                ],
                                                "datasets": [
                                                    {
                                                        "label": "<?= $this->getHtml('Articles'); ?>",
                                                        "type": "line",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $annual) {
                                                                    $temp[] = ((int) $annual['customers']);
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-2",
                                                        "fill": false,
                                                        "borderColor": "rgb(255, 99, 132)",
                                                        "backgroundColor": "rgb(255, 99, 132)",
                                                        "tension": 0.0
                                                    },
                                                    {
                                                        "label": "<?= $this->getHtml('Sales'); ?>",
                                                        "type": "bar",
                                                        "data": [
                                                            <?php
                                                                $temp = [];
                                                                foreach ($salesCustomer as $annual) {
                                                                    $temp[] = ((int) $annual['net_sales']) / 1000;
                                                                }
                                                            ?>
                                                            <?= \implode(',', $temp); ?>
                                                        ],
                                                        "yAxisID": "axis-1",
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
                                                    "text": "Sales / Articles"
                                                },
                                                "scales": {
                                                    "yAxes": [
                                                        {
                                                            "id": "axis-1",
                                                            "display": true,
                                                            "position": "left"
                                                        },
                                                        {
                                                            "id": "axis-2",
                                                            "display": true,
                                                            "position": "right",
                                                            "scaleLabel": {
                                                                "display": true,
                                                                "labelString": "<?= $this->getHtml('Articles'); ?>"
                                                            },
                                                            "gridLines": {
                                                                "display": false
                                                            }
                                                        }
                                                    ]
                                                }
                                            }
                                    }'></canvas>
                            <div class="more-container">
                                <input id="more-scustomer-sales-annual" type="checkbox">
                                <label for="more-scustomer-sales-annual">
                                    <span>Data</span>
                                    <i class="fa fa-chevron-right expand"></i>
                                </label>
                                <div>
                                <table class="default">
                                    <thead>
                                        <tr>
                                            <td>Year
                                            <td>Sales
                                            <td>Articles
                                    <tbody>
                                        <?php
                                        foreach ($salesCustomer as $values) :
                                        ?>
                                            <tr>
                                                <td><?= (string) $values['year']; ?>
                                                <td><?= (new Money(((int) $values['net_sales']) / 1000))->getCurrency(); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                                <td><?= ((int) $values['customers']); ?>
                                        <?php endforeach; ?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

    </div>
</div>