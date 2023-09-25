<?php
/**
 * Jingga
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

use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillElement;
use Modules\Billing\Models\BillStatus;
use Modules\Billing\Models\NullBillType;
use phpOMS\Localization\ISO4217CharEnum;

/**
 * @internal
 */
final class BillTest extends \PHPUnit\Framework\TestCase
{
    private Bill $bill;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->bill = new Bill();
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->bill->id);
        self::assertEquals('', $this->bill->number);
        self::assertEquals('', $this->bill->referralName);
        self::assertEquals('', $this->bill->info);
        self::assertInstanceOf('\Modules\Billing\Models\NullBillType', $this->bill->type);
        self::assertInstanceOf('\DateTimeImmutable', $this->bill->createdAt);
        self::assertNull($this->bill->performanceDate);
        self::assertNull($this->bill->send);
        self::assertNull($this->bill->client);
        self::assertNull($this->bill->supplier);
        self::assertEquals([], $this->bill->getVouchers());
        self::assertEquals([], $this->bill->getTrackings());
        self::assertInstanceOf('\Modules\Media\Models\NullMedia', $this->bill->getFileByType(0));

        self::assertEquals('', $this->bill->shipTo);
        self::assertEquals('', $this->bill->shipFAO);
        self::assertEquals('', $this->bill->shipAddress);
        self::assertEquals('', $this->bill->shipCity);
        self::assertEquals('', $this->bill->shipZip);
        self::assertEquals('', $this->bill->shipCountry);

        self::assertEquals('', $this->bill->billTo);
        self::assertEquals('', $this->bill->billFAO);
        self::assertEquals('', $this->bill->billAddress);
        self::assertEquals('', $this->bill->billCity);
        self::assertEquals('', $this->bill->billZip);
        self::assertEquals('', $this->bill->billCountry);

        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->netSales);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->grossSales);

        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->netProfit);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->grossProfit);

        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->netCosts);
        self::assertInstanceOf('\phpOMS\Stdlib\Base\FloatInt', $this->bill->grossCosts);

        self::assertEquals(0, $this->bill->payment);
        self::assertEquals('', $this->bill->paymentText);
        self::assertEquals(0, $this->bill->terms);
        self::assertEquals('', $this->bill->termsText);
        self::assertEquals(0, $this->bill->shipping);
        self::assertEquals('', $this->bill->shippingText);
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testNumberRendering() : void
    {
        $this->bill->type->numberFormat = '{y}{m}{d}-{id}';
        self::assertEquals(\date('Y') . \date('m') . \date('d') . '-0', $this->bill->getNumber());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testStatusInputOutput() : void
    {
        $this->bill->setStatus(BillStatus::ACTIVE);
        self::assertEquals(BillStatus::ACTIVE, $this->bill->getStatus());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testCurrencyInputOutput() : void
    {
        $this->bill->setCurrency(ISO4217CharEnum::_USD);
        self::assertEquals(ISO4217CharEnum::_USD, $this->bill->getCurrency());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testVoucherInputOutput() : void
    {
        $this->bill->addVoucher('TEST');
        self::assertEquals(['TEST'], $this->bill->getVouchers());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testTrackingInputOutput() : void
    {
        $this->bill->addTracking('TEST');
        self::assertEquals(['TEST'], $this->bill->getTrackings());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testElementInputOutput() : void
    {
        $this->bill->addElement(new BillElement());
        self::assertCount(1, $this->bill->getElements());
    }

    /**
     * @covers Modules\Billing\Models\Bill
     * @group module
     */
    public function testSerialize() : void
    {
        $this->bill->number            = '123456';
        $this->bill->type              = new NullBillType(2);
        $this->bill->shipTo            = 'To';
        $this->bill->shipFAO           = 'FAO';
        $this->bill->shipAddress       = 'Address';
        $this->bill->shipCity          = 'City';
        $this->bill->shipZip           = 'Zip';
        $this->bill->shipCountry       = 'Country';
        $this->bill->billTo            = 'To';
        $this->bill->billFAO           = 'FAO';
        $this->bill->billAddress       = 'Address';
        $this->bill->billCity          = 'City';
        $this->bill->billZip           = 'Zip';
        $this->bill->billCountry       = 'Country';

        self::assertEquals(
            [
                'id'                => 0,
                'number'            => '123456',
                'type'              => $this->bill->type,
                'shipTo'            => 'To',
                'shipFAO'           => 'FAO',
                'shipAddress'       => 'Address',
                'shipCity'          => 'City',
                'shipZip'           => 'Zip',
                'shipCountry'       => 'Country',
                'billTo'            => 'To',
                'billFAO'           => 'FAO',
                'billAddress'       => 'Address',
                'billCity'          => 'City',
                'billZip'           => 'Zip',
                'billCountry'       => 'Country',
            ],
            $this->bill->jsonSerialize()
        );
    }
}
