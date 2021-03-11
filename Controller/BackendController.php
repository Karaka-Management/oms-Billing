<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Billing\Models\BillTypeL11n;
use Modules\Billing\Models\PurchaseBillMapper;
use Modules\Billing\Models\SalesBillMapper;
use Modules\Billing\Models\StockBillMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
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

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                SalesBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getSalesBeforePivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                SalesBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getSalesAfterPivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } else {
            $view->setData('bills',
                SalesBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getSalesAfterPivot(0, limit: 25, depth: 3)
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

        $bill = SalesBillMapper::get((int) $request->getData('id'));

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
    public function viewBillingPurchaseList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                PurchaseBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getPurchaseBeforePivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                PurchaseBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getPurchaseAfterPivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } else {
            $view->setData('bills',
                PurchaseBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getPurchaseAfterPivot(0, limit: 25, depth: 3)
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
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response));

        $bill = PurchaseBillMapper::get((int) $request->getData('id'));

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
    public function viewBillingStockList(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005106001, $request, $response));

        if ($request->getData('ptype') === 'p') {
            $view->setData('bills',
                StockBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getStockBeforePivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('bills',
                StockBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getStockAfterPivot((int) ($request->getData('id') ?? 0), limit: 25, depth: 3)
            );
        } else {
            $view->setData('bills',
                StockBillMapper::with('language', $response->getLanguage(), [BillTypeL11n::class])
                    ::getStockAfterPivot(0, limit: 25, depth: 3)
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

        $bill = StockBillMapper::get((int) $request->getData('id'));

        $view->setData('bill', $bill);

        return $view;
    }
}
