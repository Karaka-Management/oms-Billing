<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\PurchaseBillMapper;
use Modules\Billing\Models\SalesBillMapper;
use Modules\Billing\Models\SettingsEnum;
use Modules\Billing\Models\StockBillMapper;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Localization\ISO3166CharEnum;
use phpOMS\Localization\ISO3166NameEnum;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/sales-bill-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        $mapperQuery = SalesBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('client')
            ->where('type/transferType', BillTransferType::SALES)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                $mapperQuery
                    ->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->where('client', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->where('client', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } else {
            $view->setData('bills',
                $mapperQuery->where('id', 0, '>')
                    ->where('client', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesInvoice(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/sales-bill');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        $bill = SalesBillMapper::get()
            ->with('elements')
            ->with('media')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->setData('bill', $bill);

        $previewType = (int) $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $view->setData('previewType', $previewType);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/invoice-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/invoice-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/invoice-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005105001, $request, $response));

        $mapperQuery = PurchaseBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('supplier')
            ->where('type/transferType', BillTransferType::PURCHASE)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                $mapperQuery
                    ->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } else {
            $view->setData('bills',
                $mapperQuery->where('id', 0, '>')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoice(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005105001, $request, $response));

        $bill = PurchaseBillMapper::get()
            ->with('elements')
            ->with('media')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->setData('bill', $bill);

        $previewType = (int) $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $view->setData('previewType', $previewType);

        $originalType = (int) $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $view->setData('originalType', $originalType);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005106001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                StockBillMapper::getAll()->where('id', (int) ($request->getData('id') ?? 0), '<')->limit(25)->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                StockBillMapper::getAll()->where('id', (int) ($request->getData('id') ?? 0), '>')->limit(25)->execute()
            );
        } else {
            $view->setData('bills',
                StockBillMapper::getAll()->where('id', 0, '>')->limit(25)->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockInvoice(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005106001, $request, $response));

        $bill = StockBillMapper::get()->where('id', (int) $request->getData('id'))->execute();

        $view->setData('bill', $bill);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewRegionAnalysis(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Resources/chartjs/Chartjs/chart.css');
        $head->addAsset(AssetType::JSLATE, 'Resources/chartjs/Chartjs/chart.js');
        $head->addAsset(AssetType::JSLATE, 'Modules/ClientManagement/Controller.js', ['type' => 'module']);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/region-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001602001, $request, $response));

        $monthlySalesCosts = [];
        for ($i = 1; $i < 13; ++$i) {
            $monthlySalesCosts[] = [
                'net_sales' => $sales = \mt_rand(1200000000, 2000000000),
                'net_costs' => (int) ($sales * \mt_rand(25, 55) / 100),
                'year'      => 2020,
                'month'     => $i,
            ];
        }

        $view->addData('monthlySalesCosts', $monthlySalesCosts);

        /////
        $currentCustomerRegion = [
            'Europe'  => (int) (\mt_rand(200, 400) / 4),
            'America' => (int) (\mt_rand(200, 400) / 4),
            'Asia'    => (int) (\mt_rand(200, 400) / 4),
            'Africa'  => (int) (\mt_rand(200, 400) / 4),
            'CIS'     => (int) (\mt_rand(200, 400) / 4),
            'Other'   => (int) (\mt_rand(200, 400) / 4),
        ];

        $view->addData('currentCustomerRegion', $currentCustomerRegion);

        $annualCustomerRegion = [];
        for ($i = 1; $i < 11; ++$i) {
            $annualCustomerRegion[] = [
                'year'    => 2020 - 10 + $i,
                'Europe'  => $a = (int) (\mt_rand(200, 400) / 4),
                'America' => $b = (int) (\mt_rand(200, 400) / 4),
                'Asia'    => $c = (int) (\mt_rand(200, 400) / 4),
                'Africa'  => $d = (int) (\mt_rand(200, 400) / 4),
                'CIS'     => $e = (int) (\mt_rand(200, 400) / 4),
                'Other'   => $f = (int) (\mt_rand(200, 400) / 4),
                'Total'   => $a + $b + $c + $d + $e + $f,
            ];
        }

        $view->addData('annualCustomerRegion', $annualCustomerRegion);

        /////
        $monthlySalesCustomer = [];
        for ($i = 1; $i < 13; ++$i) {
            $monthlySalesCustomer[] = [
                'net_sales' => $sales = \mt_rand(1200000000, 2000000000),
                'customers' => \mt_rand(200, 400),
                'year'      => 2020,
                'month'     => $i,
            ];
        }

        $view->addData('monthlySalesCustomer', $monthlySalesCustomer);

        $annualSalesCustomer = [];
        for ($i = 1; $i < 11; ++$i) {
            $annualSalesCustomer[] = [
                'net_sales' => $sales = \mt_rand(1200000000, 2000000000) * 12,
                'customers' => \mt_rand(200, 400) * 6,
                'year'      => 2020 - 10 + $i,
            ];
        }

        $view->addData('annualSalesCustomer', $annualSalesCustomer);

        /////
        $monthlyCustomerRetention = [];
        for ($i = 1; $i < 10; ++$i) {
            $monthlyCustomerRetention[] = [
                'customers' => \mt_rand(200, 400),
                'year'      => \date('y') - 9 + $i,
            ];
        }

        $view->addData('monthlyCustomerRetention', $monthlyCustomerRetention);

        /////
        $currentCustomerRegion = [
            'Europe'  => (int) (\mt_rand(200, 400) / 4),
            'America' => (int) (\mt_rand(200, 400) / 4),
            'Asia'    => (int) (\mt_rand(200, 400) / 4),
            'Africa'  => (int) (\mt_rand(200, 400) / 4),
            'CIS'     => (int) (\mt_rand(200, 400) / 4),
            'Other'   => (int) (\mt_rand(200, 400) / 4),
        ];

        $view->addData('currentCustomerRegion', $currentCustomerRegion);

        $annualCustomerRegion = [];
        for ($i = 1; $i < 11; ++$i) {
            $annualCustomerRegion[] = [
                'year'    => 2020 - 10 + $i,
                'Europe'  => $a = (int) (\mt_rand(200, 400) / 4),
                'America' => $b = (int) (\mt_rand(200, 400) / 4),
                'Asia'    => $c = (int) (\mt_rand(200, 400) / 4),
                'Africa'  => $d = (int) (\mt_rand(200, 400) / 4),
                'CIS'     => $e = (int) (\mt_rand(200, 400) / 4),
                'Other'   => $f = (int) (\mt_rand(200, 400) / 4),
                'Total'   => $a + $b + $c + $d + $e + $f,
            ];
        }

        $view->addData('annualCustomerRegion', $annualCustomerRegion);

        /////
        $currentCustomersRep = [];
        for ($i = 1; $i < 13; ++$i) {
            $currentCustomersRep['Rep ' . $i] = [
                'customers' => (int) (\mt_rand(200, 400) / 12),
            ];
        }

        \uasort($currentCustomersRep, function($a, $b) {
            return $b['customers'] <=> $a['customers'];
        });

        $view->addData('currentCustomersRep', $currentCustomersRep);

        $annualCustomersRep = [];
        for ($i = 1; $i < 13; ++$i) {
            $annualCustomersRep['Rep ' . $i] = [];

            for ($j = 1; $j < 11; ++$j) {
                $annualCustomersRep['Rep ' . $i][] = [
                    'customers' => (int) (\mt_rand(200, 400) / 12),
                    'year'      => 2020 - 10 + $j,
                ];
            }
        }

        $view->addData('annualCustomersRep', $annualCustomersRep);

        /////
        $currentCustomersCountry = [];
        for ($i = 1; $i < 51; ++$i) {
            $country                                           = ISO3166NameEnum::getRandom();
            $currentCustomersCountry[\substr($country, 0, 20)] = [
                'customers' => (int) (\mt_rand(200, 400) / 12),
            ];
        }

        \uasort($currentCustomersCountry, function($a, $b) {
            return $b['customers'] <=> $a['customers'];
        });

        $view->addData('currentCustomersCountry', $currentCustomersCountry);

        $annualCustomersCountry = [];
        for ($i = 1; $i < 51; ++$i) {
            $countryCode                                          = ISO3166CharEnum::getRandom();
            $countryName                                          = ISO3166NameEnum::getByName('_' . $countryCode);
            $annualCustomersCountry[\substr($countryName, 0, 20)] = [];

            for ($j = 1; $j < 11; ++$j) {
                $annualCustomersCountry[\substr($countryName, 0, 20)][] = [
                    'customers' => (int) (\mt_rand(200, 400) / 12),
                    'year'      => 2020 - 10 + $j,
                    'name'      => $countryName,
                    'code'      => $countryCode,
                ];
            }
        }

        $view->addData('annualCustomersCountry', $annualCustomersCountry);

        /////
        $customerGroups = [];
        for ($i = 1; $i < 7; ++$i) {
            $customerGroups['Group ' . $i] = [
                'customers' => (int) (\mt_rand(200, 400) / 12),
            ];
        }

        $view->addData('customerGroups', $customerGroups);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillAnalysis(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Resources/chartjs/Chartjs/chart.css');
        $head->addAsset(AssetType::JSLATE, 'Resources/chartjs/Chartjs/chart.js');
        $head->addAsset(AssetType::JSLATE, 'Modules/ClientManagement/Controller.js', ['type' => 'module']);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/bill-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001602001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSalesRepAnalysis(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Resources/chartjs/Chartjs/chart.css');
        $head->addAsset(AssetType::JSLATE, 'Resources/chartjs/Chartjs/chart.js');
        $head->addAsset(AssetType::JSLATE, 'Modules/ClientManagement/Controller.js', ['type' => 'module']);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/rep-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001602001, $request, $response));

        /////
        $currentCustomerRegion = [
            'Europe'  => (int) (\mt_rand(200, 400) / 4),
            'America' => (int) (\mt_rand(200, 400) / 4),
            'Asia'    => (int) (\mt_rand(200, 400) / 4),
            'Africa'  => (int) (\mt_rand(200, 400) / 4),
            'CIS'     => (int) (\mt_rand(200, 400) / 4),
            'Other'   => (int) (\mt_rand(200, 400) / 4),
        ];

        $view->addData('currentCustomerRegion', $currentCustomerRegion);

        $annualCustomerRegion = [];
        for ($i = 1; $i < 11; ++$i) {
            $annualCustomerRegion[] = [
                'year'    => 2020 - 10 + $i,
                'Europe'  => $a = (int) (\mt_rand(200, 400) / 4),
                'America' => $b = (int) (\mt_rand(200, 400) / 4),
                'Asia'    => $c = (int) (\mt_rand(200, 400) / 4),
                'Africa'  => $d = (int) (\mt_rand(200, 400) / 4),
                'CIS'     => $e = (int) (\mt_rand(200, 400) / 4),
                'Other'   => $f = (int) (\mt_rand(200, 400) / 4),
                'Total'   => $a + $b + $c + $d + $e + $f,
            ];
        }

        $view->addData('annualCustomerRegion', $annualCustomerRegion);

         /////
        $currentCustomersRep = [];
        for ($i = 1; $i < 13; ++$i) {
            $currentCustomersRep['Rep ' . $i] = [
                'customers' => (int) (\mt_rand(200, 400) / 12),
            ];
        }

        \uasort($currentCustomersRep, function($a, $b) {
            return $b['customers'] <=> $a['customers'];
        });

        $view->addData('currentCustomersRep', $currentCustomersRep);

        $annualCustomersRep = [];
        for ($i = 1; $i < 13; ++$i) {
            $annualCustomersRep['Rep ' . $i] = [];

            for ($j = 1; $j < 11; ++$j) {
                $annualCustomersRep['Rep ' . $i][] = [
                    'customers' => (int) (\mt_rand(200, 400) / 12),
                    'year'      => 2020 - 10 + $j,
                ];
            }
        }

        $view->addData('annualCustomersRep', $annualCustomersRep);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoiceUpload(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-upload');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivatePurchaseBillUpload(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill-upload');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivatePurchaseBillDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill-dashboard');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response));

        $mapperQuery = PurchaseBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('supplier')
            ->where('type/transferType', BillTransferType::PURCHASE)
            ->where('status', BillStatus::UNPARSED)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                $mapperQuery
                    ->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        } else {
            $view->setData('bills',
                $mapperQuery->where('id', 0, '>')
                    ->where('type/l11n/language', $response->getLanguage())
                    ->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivateBillingPurchaseInvoice(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response));

        $bill = PurchaseBillMapper::get()
            ->with('elements')
            ->with('media')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->setData('bill', $bill);

        $previewType = (int) $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $view->setData('previewType', $previewType);

        $originalType = (int) $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        )->content;

        $view->setData('originalType', $originalType);

        return $view;
    }
}
