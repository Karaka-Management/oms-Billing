<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Admin;

use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeL11n;
use Modules\Billing\Models\BillTypeL11nMapper;
use Modules\Billing\Models\BillTypeMapper;
use phpOMS\Config\SettingsInterface;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;

/**
 * Installer class.
 *
 * @package Modules\Billing\Admin
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * {@inheritdoc}
     */
    public static function install(DatabasePool $dbPool, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($dbPool, $info, $cfgHandler);

        self::createOutgoingBillTypes();
        self::createIncomingBillTypes();
        self::createTransferBillTypes();
    }

    /**
     * Install default outgoing bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createOutgoingBillTypes() : array
    {
        $billType = [];

        $billType['offer']                = new BillType('Offer');
        $billType['offer']->transferType  = BillTransferType::SALES;
        $billType['offer']->transferStock = false;
        BillTypeMapper::create($billType['offer']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['offer']->getId(), 'Angebot', ISO639x1Enum::_DE));

        $billType['order_confirmation']                = new BillType('Order Confirmation');
        $billType['order_confirmation']->transferType  = BillTransferType::SALES;
        $billType['order_confirmation']->transferStock = false;
        BillTypeMapper::create($billType['order_confirmation']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['order_confirmation']->getId(), 'Auftragsbestaetigung', ISO639x1Enum::_DE));

        $billType['delivery_note']                = new BillType('Delivery Note');
        $billType['delivery_note']->transferType  = BillTransferType::SALES;
        $billType['delivery_note']->transferStock = true;
        BillTypeMapper::create($billType['delivery_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['delivery_note']->getId(), 'Lieferschein', ISO639x1Enum::_DE));

        $billType['invoice']                = new BillType('Invoice');
        $billType['invoice']->transferType  = BillTransferType::SALES;
        $billType['invoice']->transferStock = false;
        BillTypeMapper::create($billType['invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['invoice']->getId(), 'Rechnung', ISO639x1Enum::_DE));

        $billType['credit_note']                = new BillType('Credit Note');
        $billType['credit_note']->transferType  = BillTransferType::SALES;
        $billType['credit_note']->transferStock = false;
        BillTypeMapper::create($billType['credit_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['credit_note']->getId(), 'Rechnungskorrektur', ISO639x1Enum::_DE));

        $billType['reverse_invoice']                = new BillType('Credit Note');
        $billType['reverse_invoice']->transferType  = BillTransferType::SALES;
        $billType['reverse_invoice']->transferStock = false;
        BillTypeMapper::create($billType['reverse_invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['reverse_invoice']->getId(), 'Gutschrift', ISO639x1Enum::_DE));

        return $billType;
    }

    /**
     * Install default incoming bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createIncomingBillTypes() : array
    {
        $billType = [];

        $billType['offer']                = new BillType('Offer');
        $billType['offer']->transferType  = BillTransferType::PURCHASE;
        $billType['offer']->transferStock = false;
        BillTypeMapper::create($billType['offer']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['offer']->getId(), 'Angebot', ISO639x1Enum::_DE));

        $billType['order_confirmation']                = new BillType('Order Confirmation');
        $billType['order_confirmation']->transferType  = BillTransferType::PURCHASE;
        $billType['order_confirmation']->transferStock = false;
        BillTypeMapper::create($billType['order_confirmation']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['order_confirmation']->getId(), 'Auftragsbestaetigung', ISO639x1Enum::_DE));

        $billType['delivery_note']                = new BillType('Delivery Note');
        $billType['delivery_note']->transferType  = BillTransferType::PURCHASE;
        $billType['delivery_note']->transferStock = true;
        BillTypeMapper::create($billType['delivery_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['delivery_note']->getId(), 'Lieferschein', ISO639x1Enum::_DE));

        $billType['invoice']                = new BillType('Invoice');
        $billType['invoice']->transferType  = BillTransferType::PURCHASE;
        $billType['invoice']->transferStock = false;
        BillTypeMapper::create($billType['invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['invoice']->getId(), 'Rechnung', ISO639x1Enum::_DE));

        $billType['credit_note']                = new BillType('Credit Note');
        $billType['credit_note']->transferType  = BillTransferType::PURCHASE;
        $billType['credit_note']->transferStock = false;
        BillTypeMapper::create($billType['credit_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['credit_note']->getId(), 'Rechnungskorrektur', ISO639x1Enum::_DE));

        $billType['reverse_invoice']                = new BillType('Credit Note');
        $billType['reverse_invoice']->transferType  = BillTransferType::PURCHASE;
        $billType['reverse_invoice']->transferStock = false;
        BillTypeMapper::create($billType['reverse_invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['reverse_invoice']->getId(), 'Gutschrift', ISO639x1Enum::_DE));

        return $billType;
    }

    /**
     * Install default transfer bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createTransferBillTypes() : array
    {
        return [];
    }
}
