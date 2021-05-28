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
use Modules\Media\Models\NullMedia;
use Modules\SupplierManagement\Models\Supplier;
use phpOMS\Localization\ISO4217CharEnum;
use phpOMS\Localization\Money;

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
     * Bill type.
     *
     * @var int|BillType
     * @since 1.0.0
     */
    public int |

 BillType $type = 0;

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
    private ?\DateTime $send = null;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

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

    public Money $net;

    public Money $gross;

    public Money $costs;

    public Money $profit;

    public Money $insurance;

    public Money $freight;

    private string $currency = ISO4217CharEnum::_EUR;

    public string $info = '';

    public $payment = 0;

    public string $paymentText = '';

    public $terms = 0;

    public string $termsText = '';

    public $shipping = 0;

    public string $shippingText = '';

    private array $vouchers = [];

    private array $trackings = [];

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
        $this->net        = new Money(0);
        $this->gross      = new Money(0);
        $this->costs      = new Money(0);
        $this->profit     = new Money(0);

        $this->createdAt        = new \DateTimeImmutable();
        $this->performanceDate  = new \DateTime();
        $this->createdBy        = new NullAccount();
        $this->referral         = new NullAccount();
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
     * Get Bill number.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getNumber() : string
    {
        $number = $this->number;

        return \str_replace(
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
            $number
        );
    }

    /**
     * Set Bill number.
     *
     * @param string $number Bill number
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setNumber(string $number) : void
    {
        $this->number = $number;
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
     * Set shipping date.
     *
     * @param \DateTime $send Shipping date
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setSend(\DateTime $send) : void
    {
        $this->send = $send;
    }

    /**
     * Get shipping date.
     *
     * @return null|\DateTime
     *
     * @since 1.0.0
     */
    public function getSend() : ?\DateTime
    {
        return $this->send;
    }

    /**
     * Get client.
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set client.
     *
     * @param mixed $client client
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setClient($client) : void
    {
        $this->client = $client;
    }

    /**
     * Set shipping receiver.
     *
     * @param string $ship Shipping receiver
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipTo(string $ship) : void
    {
        $this->shipTo = $ship;
    }

    /**
     * Get shipping receiver.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipTo() : string
    {
        return $this->shipTo;
    }

    /**
     * Set shipping fao.
     *
     * @param string $ship FAO
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipFAO(string $ship) : void
    {
        $this->shipFAO = $ship;
    }

    /**
     * Get shipping fao.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipFAO() : string
    {
        return $this->shipFAO;
    }

    /**
     * Set shipping address.
     *
     * @param string $ship Shipping address
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipAddress(string $ship) : void
    {
        $this->shipAddress = $ship;
    }

    /**
     * Get shipping address.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipAddress() : string
    {
        return $this->shipAddress;
    }

    /**
     * Set shipping city.
     *
     * @param string $ship City
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipCity(string $ship) : void
    {
        $this->shipCity = $ship;
    }

    /**
     * Get shipping city.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipCity() : string
    {
        return $this->shipCity;
    }

    /**
     * Set shipping zip.
     *
     * @param string $ship Zip
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipZip(string $ship) : void
    {
        $this->shipZip = $ship;
    }

    /**
     * Get shipping zip.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipZip() : string
    {
        return $this->shipZip;
    }

    /**
     * Set shipping country.
     *
     * @param string $ship Country
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipCountry(string $ship) : void
    {
        $this->shipCountry = $ship;
    }

    /**
     * Get shipping country.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShipCountry() : string
    {
        return $this->shipCountry;
    }

    /**
     * Set billing receiver.
     *
     * @param string $bill Billing receiver
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillTo(string $bill) : void
    {
        $this->billTo = $bill;
    }

    /**
     * Get billing receiver.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillTo() : string
    {
        return $this->billTo;
    }

    /**
     * Set billing fao.
     *
     * @param string $bill FAO
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillFAO(string $bill) : void
    {
        $this->billFAO = $bill;
    }

    /**
     * Get billing fao.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillFAO() : string
    {
        return $this->billFAO;
    }

    /**
     * Set billing address.
     *
     * @param string $bill Billing address
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillAddress(string $bill) : void
    {
        $this->billAddress = $bill;
    }

    /**
     * Get billing address.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillAddress() : string
    {
        return $this->billAddress;
    }

    /**
     * Set billing city.
     *
     * @param string $bill City
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillCity(string $bill) : void
    {
        $this->billCity = $bill;
    }

    /**
     * Get billing city.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillCity() : string
    {
        return $this->billCity;
    }

    /**
     * Set billing zip.
     *
     * @param string $bill Zip
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillZip(string $bill) : void
    {
        $this->billZip = $bill;
    }

    /**
     * Get billing zip.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillZip() : string
    {
        return $this->billZip;
    }

    /**
     * Set billing country.
     *
     * @param string $bill Country
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setBillCountry(string $bill) : void
    {
        $this->billCountry = $bill;
    }

    /**
     * Get billing country.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBillCountry() : string
    {
        return $this->billCountry;
    }

    /**
     * Get net amount.
     *
     * @return Money
     *
     * @since 1.0.0
     */
    public function getNet() : Money
    {
        return $this->net;
    }

    /**
     * Get gross amount.
     *
     * @return Money
     *
     * @since 1.0.0
     */
    public function getGross() : Money
    {
        return $this->gross;
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
     * Set info.
     *
     * @param string $info Info
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setInfo(string $info) : void
    {
        $this->info = $info;
    }

    /**
     * Get info.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getInfo() : string
    {
        return $this->info;
    }

    /**
     * Set payment term.
     *
     * @param int $payment Payment term
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setPayment(int $payment) : void
    {
        $this->payment = $payment;
    }

    /**
     * Get payment term.
     *
     * @return null|int
     *
     * @since 1.0.0
     */
    public function getPayment() : ?int
    {
        return $this->payment;
    }

    /**
     * Set payment text.
     *
     * @param string $payment Payment text
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setPaymentText(string $payment) : void
    {
        $this->paymentText = $payment;
    }

    /**
     * Get payment text.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getPaymentText() : string
    {
        return $this->paymentText;
    }

    /**
     * Set shipping terms (e.g. inco).
     *
     * @param int $terms Terms
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setTerms(int $terms) : void
    {
        $this->terms = $terms;
    }

    /**
     * Get terms.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getTerms() : ?int
    {
        return $this->terms;
    }

    /**
     * Set terms text.
     *
     * @param string $terms Terms text
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setTermsText(string $terms) : void
    {
        $this->termsText = $terms;
    }

    /**
     * Get terms text.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTermsText() : string
    {
        return $this->termsText;
    }

    /**
     * Set shipping.
     *
     * @param int $shipping Shipping (e.g. incoterm)
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShipping(int $shipping) : void
    {
        $this->shipping = $shipping;
    }

    /**
     * Get shipping.
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getShipping() : ?int
    {
        return $this->shipping;
    }

    /**
     * Set shipping text.
     *
     * @param string $shipping Shipping text
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setShippingText(string $shipping) : void
    {
        $this->shippingText = $shipping;
    }

    /**
     * Get shipping text.
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getShippingText() : string
    {
        return $this->shippingText;
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
     * @todo do same for actual tracking information per trackign id
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
     * @param string $type Media type
     *
     * @return Media
     *
     * @since 1.0.0
     */
    public function getMediaByType(string $type) : Media
    {
        foreach ($this->media as $media) {
            if ($media->type === $type) {
                return $media;
            }
        }

        return new NullMedia();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [];
    }
}
