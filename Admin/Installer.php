<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin;

use Modules\Billing\Models\BillTransferType;
use Modules\Billing\Models\BillType;
use Modules\Billing\Models\BillTypeL11n;
use Modules\Billing\Models\BillTypeL11nMapper;
use Modules\Billing\Models\BillTypeMapper;
use Modules\Media\Models\NullCollection;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;

/**
 * Installer class.
 *
 * @package Modules\Billing\Admin
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;

    /**
     * {@inheritdoc}
     */
    public static function install(ApplicationAbstract $app, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($app, $info, $cfgHandler);

        // Install bill type templates
        $media = \Modules\Media\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Install/Media2.install.json']);

        /** @var int $defaultTemplate */
        $defaultTemplate = (int) \reset($media['upload'][0]);

        self::createOutgoingBillTypes($defaultTemplate);
        self::createIncomingBillTypes($defaultTemplate);
        self::createTransferBillTypes($defaultTemplate);
    }

    /**
     * Install default outgoing bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createOutgoingBillTypes(int $template) : array
    {
        $billType = [];

        // @todo: allow multiple alternative bill templates
        // @todo: implement ordering of templates

        $billType['offer']                = new BillType('Offer');
        $billType['offer']->numberFormat  = '{y}-{id}';
        $billType['offer']->template      = new NullCollection($template);
        $billType['offer']->transferType  = BillTransferType::SALES;
        $billType['offer']->transferStock = false;
        BillTypeMapper::create()->execute($billType['offer']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['offer']->getId(), 'Angebot', ISO639x1Enum::_DE));

        $billType['order_confirmation']                = new BillType('Order Confirmation');
        $billType['order_confirmation']->numberFormat  = '{y}-{id}';
        $billType['order_confirmation']->template      = new NullCollection($template);
        $billType['order_confirmation']->transferType  = BillTransferType::SALES;
        $billType['order_confirmation']->transferStock = false;
        BillTypeMapper::create()->execute($billType['order_confirmation']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['order_confirmation']->getId(), 'Auftragsbestaetigung', ISO639x1Enum::_DE));

        $billType['delivery_note']                = new BillType('Delivery Note');
        $billType['delivery_note']->numberFormat  = '{y}-{id}';
        $billType['delivery_note']->template      = new NullCollection($template);
        $billType['delivery_note']->transferType  = BillTransferType::SALES;
        $billType['delivery_note']->transferStock = true;
        BillTypeMapper::create()->execute($billType['delivery_note']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['delivery_note']->getId(), 'Lieferschein', ISO639x1Enum::_DE));

        $billType['invoice']                = new BillType('Invoice');
        $billType['invoice']->numberFormat  = '{y}-{id}';
        $billType['invoice']->template      = new NullCollection($template);
        $billType['invoice']->transferType  = BillTransferType::SALES;
        $billType['invoice']->transferStock = false;
        BillTypeMapper::create()->execute($billType['invoice']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['invoice']->getId(), 'Rechnung', ISO639x1Enum::_DE));

        $billType['credit_note']                = new BillType('Credit Note');
        $billType['credit_note']->numberFormat  = '{y}-{id}';
        $billType['credit_note']->template      = new NullCollection($template);
        $billType['credit_note']->transferType  = BillTransferType::SALES;
        $billType['credit_note']->transferStock = false;
        BillTypeMapper::create()->execute($billType['credit_note']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['credit_note']->getId(), 'Rechnungskorrektur', ISO639x1Enum::_DE));

        $billType['reverse_invoice']                = new BillType('Credit Note');
        $billType['reverse_invoice']->numberFormat  = '{y}-{id}';
        $billType['reverse_invoice']->template      = new NullCollection($template);
        $billType['reverse_invoice']->transferType  = BillTransferType::SALES;
        $billType['reverse_invoice']->transferStock = false;
        BillTypeMapper::create()->execute($billType['reverse_invoice']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['reverse_invoice']->getId(), 'Gutschrift', ISO639x1Enum::_DE));

        return $billType;
    }

    /**
     * Install default incoming bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createIncomingBillTypes(int $template) : array
    {
        $billType = [];

        $billType['offer']                = new BillType('Offer');
        $billType['offer']->numberFormat  = '{y}-{id}';
        $billType['offer']->template      = new NullCollection($template);
        $billType['offer']->transferType  = BillTransferType::PURCHASE;
        $billType['offer']->transferStock = false;
        BillTypeMapper::create()->execute($billType['offer']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['offer']->getId(), 'Angebot', ISO639x1Enum::_DE));

        $billType['order_confirmation']                = new BillType('Order Confirmation');
        $billType['order_confirmation']->numberFormat  = '{y}-{id}';
        $billType['order_confirmation']->template      = new NullCollection($template);
        $billType['order_confirmation']->transferType  = BillTransferType::PURCHASE;
        $billType['order_confirmation']->transferStock = false;
        BillTypeMapper::create()->execute($billType['order_confirmation']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['order_confirmation']->getId(), 'Auftragsbestaetigung', ISO639x1Enum::_DE));

        $billType['delivery_note']                = new BillType('Delivery Note');
        $billType['delivery_note']->numberFormat  = '{y}-{id}';
        $billType['delivery_note']->template      = new NullCollection($template);
        $billType['delivery_note']->transferType  = BillTransferType::PURCHASE;
        $billType['delivery_note']->transferStock = true;
        BillTypeMapper::create()->execute($billType['delivery_note']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['delivery_note']->getId(), 'Lieferschein', ISO639x1Enum::_DE));

        $billType['invoice']                = new BillType('Invoice');
        $billType['invoice']->numberFormat  = '{y}-{id}';
        $billType['invoice']->template      = new NullCollection($template);
        $billType['invoice']->transferType  = BillTransferType::PURCHASE;
        $billType['invoice']->transferStock = false;
        BillTypeMapper::create()->execute($billType['invoice']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['invoice']->getId(), 'Rechnung', ISO639x1Enum::_DE));

        $billType['credit_note']                = new BillType('Credit Note');
        $billType['credit_note']->numberFormat  = '{y}-{id}';
        $billType['credit_note']->template      = new NullCollection($template);
        $billType['credit_note']->transferType  = BillTransferType::PURCHASE;
        $billType['credit_note']->transferStock = false;
        BillTypeMapper::create()->execute($billType['credit_note']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['credit_note']->getId(), 'Rechnungskorrektur', ISO639x1Enum::_DE));

        $billType['reverse_invoice']                = new BillType('Credit Note');
        $billType['reverse_invoice']->numberFormat  = '{y}-{id}';
        $billType['reverse_invoice']->template      = new NullCollection($template);
        $billType['reverse_invoice']->transferType  = BillTransferType::PURCHASE;
        $billType['reverse_invoice']->transferStock = false;
        BillTypeMapper::create()->execute($billType['reverse_invoice']);
        BillTypeL11nMapper::create()->execute(new BillTypeL11n($billType['reverse_invoice']->getId(), 'Gutschrift', ISO639x1Enum::_DE));

        return $billType;
    }

    /**
     * Install default transfer bill types
     *
     * @return BillType[]
     *
     * @since 1.0.0
     */
    private static function createTransferBillTypes(int $template) : array
    {
        return [];
    }
}
