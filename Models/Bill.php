<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\ClientManagement\Models\Client;
use Modules\Media\Models\Collection;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Stdlib\Base\FloatInt;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Bill implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Sequence.
     *
     * Incrementing value depending on multiple columns e.g.:
     *      id & unit
     *      id & unit & type
     *      id & unit & year
     *
     * @var int
     * @since 1.0.0
     */
    public int $sequence = 0;

    public int $unit = 0;

    public int $source = 0;

    /**
     * Number ID.
     *
     * @var string
     * @since 1.0.0
     */
    public string $number = '';

    /**
     * Bill type.
     *
     * @var BillType
     * @since 1.0.0
     */
    public BillType $type;

    public ?Collection $template = null;

    /**
     * Bill status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = BillStatus::DRAFT;

    private int $paymentStatus = BillPaymentStatus::UNPAID;

    /**
     * Bill created at.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Bill created at.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $billDate = null;

    /**
     * Bill created at.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $performanceDate = null;

    /**
     * Bill send at.
     *
     * @var null|\DateTime
     * @since 1.0.0
     */
    public ?\DateTime $send = null;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    public ?Client $client = null;

    public ?Supplier $supplier = null;

    public string $language = ISO639x1Enum::_EN;

    public string $accountNumber = '';

    /**
     * Receiver.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipTo = '';

    /**
     * For the attention of.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipFAO = '';

    /**
     * Shipping address.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipAddress = '';

    /**
     * Shipping city.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipCity = '';

    /**
     * Shipping zip.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipZip = '';

    /**
     * Shipping country.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shipCountry = '';

    /**
     * Billing.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billTo = '';

    /**
     * Billing for the attention of.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billFAO = '';

    /**
     * Billing address.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billAddress = '';

    /**
     * Billing city.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billCity = '';

    /**
     * Billing zip.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billZip = '';

    /**
     * Billing country.
     *
     * @var string
     * @since 1.0.0
     */
    public string $billCountry = '';

    public string $billEmail = '';

    /**
     * Person refering for this order.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $referral;

    public string $referralName = '';

    /**
     * Net amount.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netProfit;

    /**
     * Gross amount.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $grossProfit;

    /**
     * Costs in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netCosts;

    /**
     * Profit in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $grossCosts;

    /**
     * Costs in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netSales;

    /**
     * Profit in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $grossSales;

    /**
     * Costs in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netDiscount;

    /**
     * Profit in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $grossDiscount;

    /**
     * Insurance fees in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $insurance;

    /**
     * Freight in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $freight;

    /**
     * Currency.
     *
     * @var string
     * @since 1.0.0
     */
    public string $currency = ISO4217CharEnum::_EUR;

    /**
     * Info text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $header = '';

    /**
     * Info text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $footer = '';

    /**
     * Info text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $info = '';

    /**
     * Payment type.
     *
     * @var int
     * @since 1.0.0
     */
    public int $payment = 0;

    /**
     * Payment text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $paymentText = '';

    /**
     * Terms.
     *
     * @var int
     * @since 1.0.0
     */
    public int $terms = 0;

    /**
     * Terms text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $termsText = '';

    /**
     * Shipping.
     *
     * @var int
     * @since 1.0.0
     */
    public int $shipping = 0;

    /**
     * Shipping text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $shippingText = '';

    /**
     * Vouchers used.
     *
     * @var array
     * @since 1.0.0
     */
    private array $vouchers = [];

    /**
     * Tracking ids for shipping.
     *
     * @var array
     * @since 1.0.0
     */
    private array $trackings = [];

    /**
     * Bill elements / bill lines.
     *
     * @var BillElement[]
     * @since 1.0.0
     */
    public array $elements = [];

    /**
     * Reference to other Bill (delivery note/credit note etc).
     *
     * @var int
     * @since 1.0.0
     */
    public int $reference = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->netProfit     = new FloatInt(0);
        $this->grossProfit   = new FloatInt(0);
        $this->netCosts      = new FloatInt(0);
        $this->grossCosts    = new FloatInt(0);
        $this->netSales      = new FloatInt(0);
        $this->grossSales    = new FloatInt(0);
        $this->netDiscount   = new FloatInt(0);
        $this->grossDiscount = new FloatInt(0);

        $this->billDate  = new \DateTime('now');
        $this->createdAt = new \DateTimeImmutable();
        $this->createdBy = new NullAccount();
        $this->referral  = new NullAccount();
        $this->type      = new NullBillType();
    }

    /**
     * Get id.
     *
     * @return int Model id
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Build the invoice number.
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function buildNumber() : void
    {
        $this->number = \str_replace(
            [
                '{y}',
                '{m}',
                '{d}',
                '{id}',
                '{sequence}',
                '{type}',
            ],
            [
                $this->createdAt->format('Y'),
                $this->createdAt->format('m'),
                $this->createdAt->format('d'),
                $this->id,
                $this->sequence,
                $this->type->id,
            ],
            $this->type->numberFormat
        );
    }

    /**
     * Get Bill number.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getNumber() : string
    {
        if (empty($this->number)) {
            $this->buildNumber();
        }

        return $this->number;
    }

    /**
     * Get status
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status Status
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }

    /**
     * Get paymentStatus
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getPaymentStatus() : int
    {
        return $this->paymentStatus;
    }

    /**
     * Set paymentStatus
     *
     * @param int $paymentStatus Status
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setPaymentStatus(int $paymentStatus) : void
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * Set currency.
     *
     * @param string $currency Currency
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setCurrency(string $currency) : void
    {
        $this->currency = $currency;
    }

    /**
     * Get currency.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getCurrency() : string
    {
        return $this->currency;
    }

    /**
     * Get vouchers.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getVouchers() : array
    {
        return $this->vouchers;
    }

    /**
     * Add voucher.
     *
     * @param string $voucher Voucher code
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addVoucher(string $voucher) : void
    {
        $this->vouchers[] = $voucher;
    }

    /**
     * Get tracking ids for shipment.
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getTrackings() : array
    {
        return $this->trackings;
    }

    /**
     * Add tracking id.
     *
     * @param string $tracking Tracking id
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addTracking(string $tracking) : void
    {
        $this->trackings[] = $tracking;
    }

    /**
     * Get Bill elements.
     *
     * @return BillElement[]
     *
     * @since 1.0.0
     */
    public function getElements() : array
    {
        return $this->elements;
    }

    /**
     * Add Bill element.
     *
     * @param BillElement $element Bill element
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addElement(BillElement $element) : void
    {
        $this->elements[] = $element;

        $this->netProfit->add($element->totalProfitNet->getInt());
        $this->grossProfit->add($element->totalProfitGross->getInt());
        $this->netCosts->add($element->totalPurchasePriceNet->getInt());
        $this->grossCosts->add($element->totalPurchasePriceGross->getInt());
        $this->netSales->add($element->totalSalesPriceNet->getInt());
        $this->grossSales->add($element->totalSalesPriceGross->getInt());
        $this->netDiscount->add($element->totalDiscountP->getInt());

        // @todo: Discount might be in quantities
        $this->grossDiscount->add((int) ($element->taxR->getInt() * $element->totalDiscountP->getInt() / 10000));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'           => $this->id,
            'number'       => $this->number,
            'type'         => $this->type,
            'shipTo'       => $this->shipTo,
            'shipFAO'      => $this->shipFAO,
            'shipAddress'  => $this->shipAddress,
            'shipCity'     => $this->shipCity,
            'shipZip'      => $this->shipZip,
            'shipCountry'  => $this->shipCountry,
            'billTo'       => $this->billTo,
            'billFAO'      => $this->billFAO,
            'billAddress'  => $this->billAddress,
            'billCity'     => $this->billCity,
            'billZip'      => $this->billZip,
            'billCountry'  => $this->billCountry,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }

    use \Modules\Attribute\Models\AttributeHolderTrait;
    use \Modules\Editor\Models\EditorDocListTrait;
    use \Modules\Media\Models\MediaListTrait;
}
