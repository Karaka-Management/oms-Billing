<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeL11n;

/**
 * @internal
 */
class BillTypeTest extends \PHPUnit\Framework\TestCase
{
    private BillType $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->type = new BillType();
    }

    /**
     * @covers Modules\Billing\Models\BillType
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->type->getId());
        self::assertTrue($this->type->transferStock);
    }

    /**
     * @covers Modules\Billing\Models\BillType
     * @group module
     */
    public function testL11nInputOutput() : void
    {
        $this->type->setL11n('Test1');
        self::assertEquals('Test1', $this->type->getL11n());

        $this->type->setL11n(new BillTypeL11n(0, 'Test2'));
        self::assertEquals('Test2', $this->type->getL11n());
    }
}
