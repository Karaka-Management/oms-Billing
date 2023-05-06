<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\tests\Models;

use Modules\Billing\Models\BillType;
use phpOMS\Localization\BaseStringL11n;

/**
 * @internal
 */
final class BillTypeTest extends \PHPUnit\Framework\TestCase
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
        self::assertEquals(0, $this->type->id);
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

        $this->type->setL11n(new BaseStringL11n('Test2'));
        self::assertEquals('Test2', $this->type->getL11n());
    }
}
