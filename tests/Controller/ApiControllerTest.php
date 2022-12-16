<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Module\ModuleAbstract;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\Uri\HttpUri;
use phpOMS\Utils\RnG\DateTime;
use phpOMS\Utils\TestUtils;

/**
 * @testdox Modules\tests\Billing\Controller\ApiControllerTest: Billing api controller
 *
 * @internal
 */
final class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    protected ApplicationAbstract $app;

    /**
     * @var \Modules\Billing\Controller\ApiController
     */
    protected ModuleAbstract $module;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->app = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $this->app->dbPool         = $GLOBALS['dbpool'];
        $this->app->orgId          = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../../Modules/');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../../Web/Api/Hooks.php');

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission = new AccountPermission();
        $permission->setUnit(1);
        $permission->setApp('backend');
        $permission->setPermission(
            PermissionType::READ
            | PermissionType::CREATE
            | PermissionType::MODIFY
            | PermissionType::DELETE
            | PermissionType::PERMISSION
        );

        $account->addPermission($permission);

        $this->app->accountManager->add($account);
        $this->app->router = new WebRouter();

        $this->module = $this->app->moduleManager->get('Billing');

        TestUtils::setMember($this->module, 'app', $this->app);
    }

    /**
     * Tests bill, bill element and bill pdf archive create
     *
     * @covers Modules\Billing\Controller\ApiController
     * @group module
     */
    public function testBillClientCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;

        $request->setData('client', 1);
        $request->setData('address', null);
        $request->setData('type', 1);
        $request->setData('status', null); // null = system settings, value = individual input
        $request->setData('performancedate', DateTime::generateDateTime(new \DateTime('2015-01-01'), new \DateTime('now'))->format('Y-m-d H:i:s'));
        $request->setData('sales_referral', null); // who these sales belong to
        $request->setData('shipping_terms', 1); // e.g. incoterms
        $request->setData('shipping_type', 1);
        $request->setData('shipping_cost', null);
        $request->setData('insurance_type', 1);
        $request->setData('insurance_cost', null); // null = system settings, value = individual input
        $request->setData('info', null); // null = system settings, value = individual input
        $request->setData('currency', null); // null = system settings, value = individual input
        $request->setData('payment', null); // null = system settings, value = individual input
        $request->setData('payment_terms', null); // null = system settings, value = individual input

        $this->module->apiBillCreate($request, $response);

        $bId = $response->get('')['response']->getId();
        self::assertGreaterThan(0, $bId);

        for ($k = 0; $k < 10; ++$k) {
            $response = new HttpResponse();
            $request  = new HttpRequest(new HttpUri(''));

            $request->header->account = 1;

            $iId = \mt_rand(0, 10);

            $request->setData('bill', $bId);
            $request->setData('item', $iId === 0 ? null : $iId);

            if ($iId === 0) {
                // @todo: add text
            }

            $request->setData('quantity', \mt_rand(1, 11));
            $request->setData('tax', null);
            $request->setData('text', $iId === 0 ? 'Some test text' : null);

            // discounts
            if (\mt_rand(1, 100) < 31) {
                $request->setData('discount_percentage', \mt_rand(5, 30));
            }

            $this->module->apiBillElementCreate($request, $response);
            self::assertGreaterThan(0, $response->get('')['response']->getId());
        }

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('bill', $bId);

        $this->module->apiBillPdfArchiveCreate($request, $response);

        $result = $response->get('');
        self::assertGreaterThan(0, $result === null ? -1 : $result['response']?->getId());
    }

    /**
     * Tests bill, bill element and bill pdf archive create
     *
     * @covers Modules\Billing\Controller\ApiController
     * @group module
     */
    public function testBillSupplierCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;

        $request->setData('supplier', 1);
        $request->setData('address', null);
        $request->setData('type', 1);
        $request->setData('status', null); // null = system settings, value = individual input
        $request->setData('performancedate', DateTime::generateDateTime(new \DateTime('2015-01-01'), new \DateTime('now'))->format('Y-m-d H:i:s'));
        $request->setData('sales_referral', null); // who these sales belong to
        $request->setData('shipping_terms', 1); // e.g. incoterms
        $request->setData('shipping_type', 1);
        $request->setData('shipping_cost', null);
        $request->setData('insurance_type', 1);
        $request->setData('insurance_cost', null); // null = system settings, value = individual input
        $request->setData('info', null); // null = system settings, value = individual input
        $request->setData('currency', null); // null = system settings, value = individual input
        $request->setData('payment', null); // null = system settings, value = individual input
        $request->setData('payment_terms', null); // null = system settings, value = individual input

        $this->module->apiBillCreate($request, $response);

        $bId = $response->get('')['response']->getId();
        self::assertGreaterThan(0, $bId);

        for ($k = 0; $k < 10; ++$k) {
            $response = new HttpResponse();
            $request  = new HttpRequest(new HttpUri(''));

            $request->header->account = 1;

            $iId = \mt_rand(0, 10);

            $request->setData('bill', $bId);
            $request->setData('item', $iId === 0 ? null : $iId);

            if ($iId === 0) {
                // @todo: add text
            }

            $request->setData('quantity', \mt_rand(1, 11));
            $request->setData('tax', null);
            $request->setData('text', $iId === 0 ? 'Some test text' : null);

            // discounts
            if (\mt_rand(1, 100) < 31) {
                $request->setData('discount_percentage', \mt_rand(5, 30));
            }

            $this->module->apiBillElementCreate($request, $response);
            self::assertGreaterThan(0, $response->get('')['response']->getId());
        }

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('bill', $bId);

        $this->module->apiBillPdfArchiveCreate($request, $response);

        $result = $response->get('');
        self::assertGreaterThan(0, $result === null ? -1 : $result['response']?->getId());
    }

    /**
     * @covers Modules\Billing\Controller\ApiController
     * @group module
     */
    public function testBillCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiBillCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers Modules\Billing\Controller\ApiController
     * @group module
     */
    public function testBillElementCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiBillElementCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }
}
