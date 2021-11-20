<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;
use Modules\ClientManagement\Models\Client;
use Modules\Media\Models\Media;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\Money;
use Modules\Editor\Models\EditorDoc;
use Mpdf\Tag\P;

/**
 * Bill class.
 *
 * @package Modules\Billing\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
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
    protected int $id = 0;

    /**
     * Number ID.
     *
     * @var string
     * @since 1.0.0
     */
    public string $number = '';

    /**
     * Number format ID.
     *
     * @var string
     * @since 1.0.0
     */
    public string $numberFormat = '';

    /**
     * Bill type.
     *
     * @var int|BillType
     * @since 1.0.0
     */
    public int | BillType $type = 0;

    /**
     * Bill status.
     *
     * @var int
     * @since 1.0.0
     */
    private int $status = BillStatus::DRAFT;

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
     * @var \DateTime
     * @since 1.0.0
     */
    public \DateTime $performanceDate;

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

    /**
     * Files.
     *
     * @var EditorDoc[]
     * @since 1.0.0
     */
    private array $notes = [];

    public ?Client $client = null;

    public ?Supplier $supplier = null;

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
     * @var Money
     * @since 1.0.0
     */
    public Money $netProfit;

    /**
     * Gross amount.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $grossProfit;

    /**
     * Costs in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $netCosts;

    /**
     * Profit in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $grossCosts;

    /**
     * Costs in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $netSales;

    /**
     * Profit in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $grossSales;

    /**
     * Costs in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $netDiscount;

    /**
     * Profit in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $grossDiscount;

    /**
     * Insurance fees in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $insurance;

    /**
     * Freight in net.
     *
     * @var Money
     * @since 1.0.0
     */
    public Money $freight;

    /**
     * Currency.
     *
     * @var string
     * @since 1.0.0
     */
    private string $currency = ISO4217CharEnum::_EUR;

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
    private array $elements = [];

    /**
     * Reference to other Bill (delivery note/credit note etc).
     *
     * @var int
     * @since 1.0.0
     */
    private int $reference = 0;

    /**
     * Media files
     *
     * @var array
     * @since 1.0.0
     */
    protected array $media = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->netProfit    = new Money(0);
        $this->grossProfit  = new Money(0);
        $this->netCosts    = new Money(0);
        $this->grossCosts  = new Money(0);
        $this->netSales    = new Money(0);
        $this->grossSales  = new Money(0);
        $this->netDiscount    = new Money(0);
        $this->grossDiscount  = new Money(0);

        $this->createdAt       = new \DateTimeImmutable();
        $this->performanceDate = new \DateTime();
        $this->createdBy       = new NullAccount();
        $this->referral        = new NullAccount();
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
        $this->number =  \str_replace(
            [
                '{y}',
                '{m}',
                '{d}',
                '{id}',
                '{type}',
            ],
            [
                $this->createdAt->format('Y'),
                $this->createdAt->format('m'),
                $this->createdAt->format('d'),
                $this->id,
                \is_int($this->type) ? $this->type : $this->type->getId(),
            ],
            $this->numberFormat
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
        return $this->number;
    }

    /**
     * Get type
     *
     * @return int | BillType
     *
     * @since 1.0.0
     */
    public function getType() : int | BillType
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param int|BillType $type Type
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setType(int | BillType $type) : void
    {
        $this->type = $type;
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
     * @return array
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
     * @param mixed $element Bill element
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addElement($element) : void
    {
        $this->elements[] = $element;
    }

    /**
     * Add note to item
     *
     * @param EditorDoc $note Note
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addNote(EditorDoc $note) : void
    {
        $this->notes[] = $note;
    }

    /**
     * Get notes
     *
     * @return EditorDoc[]
     *
     * @since 1.0.0
     */
    public function getNotes() : array
    {
        return $this->notes;
    }

    /**
     * Get all media
     *
     * @return Media[]
     *
     * @since 1.0.0
     */
    public function getMedia() : array
    {
        return $this->media;
    }

    /**
     * Add media
     *
     * @param Media $media Media to add
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addMedia(Media $media) : void
    {
        $this->media[] = $media;
    }

    /**
     * Get media file by type
     *
     * @param null|int $type Media type
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getMediaByType(int $type = null) : array
    {
        $files = [];
        foreach ($this->media as $file) {
            if ($file->type === $type) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'number'      => $this->number,
            'numberFormat'      => $this->numberFormat,
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
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
