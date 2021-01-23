<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Billing\Admin;

use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Billing\Models\BillTypeL11n;
use Modules\Billing\Models\BillTypeL11nMapper;
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

        self::createBillTypes();
    }

    /**
     * Install default bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createBillTypes() : array
    {
        $billType = [];

        $billType['offer'] = new BillType('Offer');
        BillTypeMapper::create($billType['offer']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['offer']->getId(), 'Angebot', ISO639x1Enum::_DE));

        $billType['order_confirmation'] = new BillType('Order Confirmation');
        BillTypeMapper::create($billType['order_confirmation']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['order_confirmation']->getId(), 'Auftragsbestaetigung', ISO639x1Enum::_DE));

        $billType['delivery_note'] = new BillType('Delivery Note');
        BillTypeMapper::create($billType['delivery_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['delivery_note']->getId(), 'Lieferschein', ISO639x1Enum::_DE));

        $billType['invoice'] = new BillType('Invoice');
        BillTypeMapper::create($billType['invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['invoice']->getId(), 'Rechnung', ISO639x1Enum::_DE));

        $billType['credit_note'] = new BillType('Credit Note');
        BillTypeMapper::create($billType['credit_note']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['credit_note']->getId(), 'Rechnungskorrektur', ISO639x1Enum::_DE));

        $billType['reverse_invoice'] = new BillType('Credit Note');
        BillTypeMapper::create($billType['reverse_invoice']);
        BillTypeL11nMapper::create(new BillTypeL11n($billType['reverse_invoice']->getId(), 'Gutschrift', ISO639x1Enum::_DE));

        return $billType;
    }
}
