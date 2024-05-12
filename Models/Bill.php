<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
use Modules\Sales\Models\SalesRep;
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

    public string $external = '';

    /**
     * Bill type.
     *
     * @var BillType
     * @since 1.0.0
     */
    public BillType $type;

    public bool $isTemplate = false;

    public bool $isArchived = false;

    /**
     * Bill status.
     *
     * @var int
     * @since 1.0.0
     */
    public int $status = BillStatus::DRAFT;

    /**
     * Bill payment status
     *
     * @var int
     * @since 1.0.0
     */
    public int $paymentStatus = BillPaymentStatus::UNPAID;

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

    public ?SalesRep $rep = null;

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
     * Person referring for this order.
     *
     * Usually the sales rep
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $referral;

    /**
     * Net amount.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netProfit;

    /**
     * Costs in net.
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $netCosts;

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
     * Tax amount
     *
     * @var FloatInt
     * @since 1.0.0
     */
    public FloatInt $taxP;

    public ?int $accTaxCode = null;

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

    public ?int $paymentTerms = null;

    public ?int $shippingTerms = null;

    /**
     * Terms text.
     *
     * @var string
     * @since 1.0.0
     */
    public string $termsText = '';

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
    public array $vouchers = [];

    /**
     * Tracking ids for shipping.
     *
     * @var array
     * @since 1.0.0
     */
    public array $trackings = [];

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

    public ?int $accSegment = null;

    public ?int $accSection = null;

    public ?int $accGroup = null;

    public ?int $accType = null;

    public ?string $fiAccount = null;

    // @todo Implement reason for bill (especially useful for credit notes, warehouse bookings)

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->netProfit   = new FloatInt(0);
        $this->netCosts    = new FloatInt(0);
        $this->netSales    = new FloatInt(0);
        $this->grossSales  = new FloatInt(0);
        $this->netDiscount = new FloatInt(0);
        $this->taxP        = new FloatInt(0);

        $this->billDate  = new \DateTime('now');
        $this->createdAt = new \DateTimeImmutable();
        $this->createdBy = new NullAccount();
        $this->referral  = new NullAccount();
        $this->type      = new NullBillType();
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
                '{unit}',
                '{account}',
                '{country}',
            ],
            [
                $this->createdAt->format('Y'),
                $this->createdAt->format('m'),
                $this->createdAt->format('d'),
                $this->id,
                $this->sequence,
                $this->type->id,
                $this->unit,
                $this->accountNumber,
                $this->billCountry,
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

        $this->netProfit->value   += $element->totalProfitNet->value;
        $this->netCosts->value    += $element->totalPurchasePriceNet->value;
        $this->netSales->value    += $element->totalSalesPriceNet->value;
        $this->grossSales->value  += $element->totalSalesPriceGross->value;
        $this->netDiscount->value += $element->totalDiscountP->value;
    }

    /**
     * Validate the correctness of the bill
     *
     * @return bool
     *
     * @todo also consider rounding similarly to recalculatePrices in elements
     *
     * @since 1.0.0
     */
    public function isValid() : bool
    {
        return $this->validateTaxAmountElements()
            && $this->validateProfit()
            && $this->validateGrossElements()
            && $this->validatePriceQuantityElements()
            && $this->validateNetElements()
            && $this->validateNetGross()
            && $this->areElementsValid();
    }

    /**
     * Validate the correctness of the bill elements
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function areElementsValid() : bool
    {
        foreach ($this->elements as $element) {
            if (!$element->isValid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the correctness of the net and gross values
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateNetGross() : bool
    {
        return $this->netSales->value <= $this->grossSales->value;
    }

    /**
     * Validate the correctness of the profit
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateProfit() : bool
    {
        return $this->netSales->value - $this->netCosts->value === $this->netProfit->value;
    }

    /**
     * Validate the correctness of the taxes
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateTax() : bool
    {
        return \abs($this->netSales->value + $this->taxP->value - $this->grossSales->value) === 0;
    }

    /**
     * Validate the correctness of the taxes for the elements
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateTaxAmountElements() : bool
    {
        $taxes = 0;
        foreach ($this->elements as $element) {
            $taxes += $element->taxP->value;
        }

        return $taxes === $this->taxP->value;
    }

    /**
     * Validate the correctness of the net of the elements
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateNetElements() : bool
    {
        $net = 0;
        foreach ($this->elements as $element) {
            $net += $element->totalSalesPriceNet->value;
        }

        return $net === $this->netSales->value;
    }

    /**
     * Validate the correctness of the gross of the elements
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validateGrossElements()
    {
        $gross = 0;
        foreach ($this->elements as $element) {
            $gross += $element->totalSalesPriceGross->value;
        }

        return $gross === $this->grossSales->value;
    }

    /**
     * Validate the correctness of the quantities and total price
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function validatePriceQuantityElements()
    {
        foreach ($this->elements as $element) {
            if ($element->discountQ->value === 0
                && $element->totalDiscountP->value === 0
                && ($element->quantity->value / FloatInt::DIVISOR) * $element->singleSalesPriceNet->value - $element->totalSalesPriceNet->value > 1.0
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'number'      => $this->number,
            'external'    => $this->external,
            'type'        => $this->type,
            'shipTo'      => $this->shipTo,
            'shipFAO'     => $this->shipFAO,
            'shipAddress' => $this->shipAddress,
            'shipCity'    => $this->shipCity,
            'shipZip'     => $this->shipZip,
            'shipCountry' => $this->shipCountry,
            'billTo'      => $this->billTo,
            'billFAO'     => $this->billFAO,
            'billAddress' => $this->billAddress,
            'billCity'    => $this->billCity,
            'billZip'     => $this->billZip,
            'billCountry' => $this->billCountry,
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
    use \Modules\Tag\Models\TagListTrait;
}
