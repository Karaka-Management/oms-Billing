<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Controller;

use Modules\Auditor\Models\AuditMapper;
use Modules\Billing\Models\BillElementMapper;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\PurchaseBillMapper;
use Modules\Billing\Models\SalesBillMapper;
use Modules\Billing\Models\SettingsEnum;
use Modules\Billing\Models\StockBillMapper;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

/**
 * Billing class.
 *
 * @package Modules\Billing
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/sales-bill-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response);

        $mapperQuery = SalesBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('client')
            ->where('type/transferType', BillTransferType::SALES)
            ->where('type/l11n/language', $response->header->l11n->language)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->data['bills'] = $mapperQuery
                    ->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->where('client', null, '!=')
                    ->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['bills'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->where('client', null, '!=')
                    ->execute();
        } else {
            $view->data['bills'] = $mapperQuery->where('id', 0, '>')
                    ->where('client', null, '!=')
                    ->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesInvoice(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/bill-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response);

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = SalesBillMapper::get()
            ->with('client')
            ->with('elements')
            ->with('files')
            ->with('files/types')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->data['bill'] = $bill;

        /** @var \Modules\Auditor\Models\Audit[] $logsBill */
        $logs = AuditMapper::getAll()
            ->with('createdBy')
            ->where('module', 'Billing')
            ->where('type', StringUtils::intHash(BillMapper::class))
            ->where('ref', $bill->id)
            ->execute();

        if (!empty($bill->elements)) {
            /** @var \Modules\Auditor\Models\Audit[] $logsElements */
            $logsElements = AuditMapper::getAll()
                ->with('createdBy')
                ->where('module', 'Billing')
                ->where('type', StringUtils::intHash(BillElementMapper::class))
                ->where('ref', \array_keys($bill->getElements()), 'IN')
                ->execute();

            $logs = \array_merge($logs, $logsElements);
        }

        $logs = \array_merge($logs, $logsElements);

        $view->data['logs']         = $logs;
        $view->data['media-upload'] = new \Modules\Media\Theme\Backend\Components\Upload\BaseView($this->app->l11nManager, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingSalesInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/bill-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response);

        $billTypes = BillTypeMapper::getAll()
            ->with('l11n')
            ->where('isTemplate', false)
            ->where('transferType', BillTransferType::SALES)
            ->where('l11n/language', $request->header->l11n->language)
            ->execute();

        $view->data['billtypes'] = $billTypes;

        $mediaListView = new \Modules\Media\Theme\Backend\Components\Media\ListView($this->app->l11nManager, $request, $response);
        $mediaListView->setTemplate('/Modules/Media/Theme/Backend/Components/Media/list');
        $view->data['medialist'] = $mediaListView;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/bill-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockInvoiceCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/bill-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005104001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005105001, $request, $response);

        $mapperQuery = PurchaseBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('supplier')
            ->where('type/transferType', BillTransferType::PURCHASE)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->data['bills'] = $mapperQuery
                    ->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['bills'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        } else {
            $view->data['bills'] = $mapperQuery->where('id', 0, '>')
                    ->where('supplier', null, '!=')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoice(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005105001, $request, $response);

        $bill = PurchaseBillMapper::get()
            ->with('elements')
            ->with('files')
            ->with('files/types')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->data['bill'] = $bill;

        /** @var \Model\Setting $previewType */
        $previewType = $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        );

        $view->data['previewType'] = (int) $previewType->content;

        /** @var \Model\Setting $originalType */
        $originalType = $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        );

        $view->data['originalType'] = (int) $originalType->content;

        /** @var \Modules\Auditor\Models\Audit[] $logs */
        $logs = AuditMapper::getAll()
            ->with('createdBy')
            ->where('module', 'Billing')
            ->where('type', StringUtils::intHash(BillMapper::class))
            ->where('ref', $bill->id)
            ->execute();

        if (!empty($bill->elements)) {
            /** @var \Modules\Auditor\Models\Audit[] $logsElements */
            $logsElements = AuditMapper::getAll()
                ->with('createdBy')
                ->where('module', 'Billing')
                ->where('type', StringUtils::intHash(BillElementMapper::class))
                ->where('ref', \array_keys($bill->getElements()), 'IN')
                ->execute();

            $logs = \array_merge($logs, $logsElements);
        }

        $view->data['logs']         = $logs;
        $view->data['media-upload'] = new \Modules\Media\Theme\Backend\Components\Upload\BaseView($this->app->l11nManager, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005106001, $request, $response);

        if ($request->getData('ptype') === 'p') {
            $view->data['bills'] = StockBillMapper::getAll()->where('id', $request->getDataInt('id') ?? 0, '<')->limit(25)->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['bills'] = StockBillMapper::getAll()->where('id', $request->getDataInt('id') ?? 0, '>')->limit(25)->execute();
        } else {
            $view->data['bills'] = StockBillMapper::getAll()->where('id', 0, '>')->limit(25)->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingStockInvoice(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005106001, $request, $response);

        $bill = StockBillMapper::get()->where('id', (int) $request->getData('id'))->execute();

        $view->data['bill']         = $bill;
        $view->data['media-upload'] = new \Modules\Media\Theme\Backend\Components\Upload\BaseView($this->app->l11nManager, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewBillingPurchaseInvoiceUpload(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/purchase-bill-upload');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivatePurchaseBillUpload(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill-upload');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivatePurchaseBillDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response);

        $mapperQuery = PurchaseBillMapper::getAll()
            ->with('type')
            ->with('type/l11n')
            ->with('supplier')
            ->where('type/transferType', BillTransferType::PURCHASE)
            ->where('status', BillStatus::UNPARSED)
            ->sort('id', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->data['bills'] = $mapperQuery
                    ->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['bills'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        } else {
            $view->data['bills'] = $mapperQuery->where('id', 0, '>')
                    ->where('type/l11n/language', $response->header->l11n->language)
                    ->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivateBillingPurchaseInvoice(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Billing/Theme/Backend/user-purchase-bill');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005109001, $request, $response);

        $bill = PurchaseBillMapper::get()
            ->with('elements')
            ->with('files')
            ->with('files/types')
            ->with('notes')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $view->data['bill'] = $bill;

        /** @var \Model\Setting $previewType */
        $previewType = $this->app->appSettings->get(
            names: SettingsEnum::PREVIEW_MEDIA_TYPE,
            module: self::NAME
        );

        $view->data['previewType'] = (int) $previewType->content;

        /** @var \Model\Setting $originalType */
        $originalType = $this->app->appSettings->get(
            names: SettingsEnum::ORIGINAL_MEDIA_TYPE,
            module: self::NAME
        );

        $view->data['originalType'] = (int) $originalType->content;
        $view->data['media-upload'] = new \Modules\Media\Theme\Backend\Components\Upload\BaseView($this->app->l11nManager, $request, $response);

        return $view;
    }
}
