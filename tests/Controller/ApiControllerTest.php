<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use Modules\Billing\tests\Controller\Api\ApiBillControllerTrait;
use Modules\Billing\tests\Controller\Api\ApiPurchaseControllerTrait;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\DataStorage\Cache\CachePool;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Module\ModuleAbstract;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\Utils\TestUtils;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\TestDox('Modules\tests\Billing\Controller\ApiControllerTest: Billing api controller')]
final class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    protected ApplicationAbstract $app;

    /**
     * @var \Modules\Billing\Controller\ApiController
     */
    protected ModuleAbstract $module;

    /**
     * @var \Modules\Billing\Controller\ApiController
     */
    protected ModuleAbstract $modulePurchase;

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
        $this->app->unitId         = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../../Modules/');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->l11nManager    = new L11nManager();
        $this->app->cachePool    = new CachePool();
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../../Web/Api/Hooks.php');

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission       = new AccountPermission();
        $permission->unit = 1;
        $permission->app  = 2;
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

        $this->module         = $this->app->moduleManager->get('Billing', 'ApiBill');
        $this->modulePurchase = $this->app->moduleManager->get('Billing', 'ApiPurchase');

        TestUtils::setMember($this->module, 'app', $this->app);
        TestUtils::setMember($this->modulePurchase, 'app', $this->app);
    }

    use ApiBillControllerTrait;
    use ApiPurchaseControllerTrait;
}
