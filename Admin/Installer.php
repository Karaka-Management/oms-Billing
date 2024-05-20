<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Billing\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Billing\Admin;

use Modules\Billing\Models\BillTransferType;
use Modules\ClientManagement\Models\Attribute\ClientAttributeTypeMapper;
use Modules\ItemManagement\Models\Attribute\ItemAttributeTypeMapper;
use Modules\SupplierManagement\Models\Attribute\SupplierAttributeTypeMapper;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;

/**
 * Installer class.
 *
 * @package Modules\Billing\Admin
 * @license OMS License 2.2
 * @link    https://jingga.app
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

        /* Bill types */
        $fileContent = \file_get_contents(__DIR__ . '/Install/types.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $types */
        $types = \json_decode($fileContent, true);
        if ($types === false) {
            return;
        }

        self::createBillTypes($app, $types, $defaultTemplate);

        /* Tax types */
        $fileContent = \file_get_contents(__DIR__ . '/Install/Taxes/taxes.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $taxes */
        $taxes = \json_decode($fileContent, true);
        if ($taxes === false) {
            return;
        }

        self::createTaxCombination($app, $taxes);

        /* Attributes */
        $fileContent = \file_get_contents(__DIR__ . '/Install/attributes.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $attributes */
        $attributes = \json_decode($fileContent, true);
        if ($attributes === false) {
            return;
        }

        $attrTypes  = self::createBillAttributeTypes($app, $attributes);
        $attrValues = self::createBillAttributeValues($app, $attrTypes, $attributes);

        /* Payment terms */
        $fileContent = \file_get_contents(__DIR__ . '/Install/paymentterms.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $terms */
        $terms            = \json_decode($fileContent, true);
        $paymentTypeArray = self::createPaymentTerms($app, $terms);

        /* Shipping terms */
        $fileContent = \file_get_contents(__DIR__ . '/Install/shippingterms.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $terms */
        $terms             = \json_decode($fileContent, true);
        $shippingTypeArray = self::createShippingTerms($app, $terms);
    }

    /**
     * Install default attribute types
     *
     * @param ApplicationAbstract $app        Application
     * @param array               $attributes Attribute definition
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createBillAttributeTypes(ApplicationAbstract $app, array $attributes) : array
    {
        /** @var array $billAttrType */
        $billAttrType = [];

        /** @var \Modules\Billing\Controller\ApiAttributeController $module */
        $module = $app->moduleManager->get('Billing', 'ApiAttribute');

        /** @var array $attribute */
        foreach ($attributes as $attribute) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $attribute['name'] ?? '');
            $request->setData('title', \reset($attribute['l11n']));
            $request->setData('language', \array_keys($attribute['l11n'])[0] ?? 'en');
            $request->setData('is_required', $attribute['is_required'] ?? false);
            $request->setData('repeatable', $attribute['repeatable'] ?? false);
            $request->setData('internal', $attribute['internal'] ?? false);
            $request->setData('custom', $attribute['is_custom_allowed'] ?? false);
            $request->setData('validation_pattern', $attribute['validation_pattern'] ?? '');
            $request->setData('datatype', (int) $attribute['value_type']);

            $module->apiBillAttributeTypeCreate($request, $response);

            $responseData = $response->getData('');

            if (!\is_array($responseData)) {
                continue;
            }

            $billAttrType[$attribute['name']] = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $isFirst = true;
            foreach ($attribute['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $billAttrType[$attribute['name']]['id']);

                $module->apiBillAttributeTypeL11nCreate($request, $response);
            }
        }

        return $billAttrType;
    }

    /**
     * Create default attribute values for types
     *
     * @param ApplicationAbstract                                                                                                                                                              $app          Application
     * @param array                                                                                                                                                                            $billAttrType Attribute types
     * @param array<array{name:string, l11n?:array<string, string>, is_required?:bool, is_custom_allowed?:bool, validation_pattern?:string, value_type?:string, values?:array<string, mixed>}> $attributes   Attribute definition
     *
     * @return array<string, array>
     *
     * @since 1.0.0
     */
    private static function createBillAttributeValues(ApplicationAbstract $app, array $billAttrType, array $attributes) : array
    {
        /** @var array<string, array> $billAttrValue */
        $billAttrValue = [];

        /** @var \Modules\Billing\Controller\ApiAttributeController $module */
        $module = $app->moduleManager->get('Billing', 'ApiAttribute');

        foreach ($attributes as $attribute) {
            if (!isset($attribute['values'])) {
                continue;
            }

            $billAttrValue[$attribute['name']] = [];

            /** @var array $value */
            foreach ($attribute['values'] as $value) {
                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('value', $value['value'] ?? '');
                $request->setData('unit', $value['unit'] ?? '');
                $request->setData('default', true); // always true since all defined values are possible default values
                $request->setData('type', $billAttrType[$attribute['name']]['id']);

                if (isset($value['l11n']) && !empty($value['l11n'])) {
                    $request->setData('title', \reset($value['l11n']));
                    $request->setData('language', \array_keys($value['l11n'])[0] ?? 'en');
                }

                $module->apiBillAttributeValueCreate($request, $response);

                $responseData = $response->getData('');
                if (!\is_array($responseData)) {
                    continue;
                }

                $attrValue = \is_array($responseData['response'])
                    ? $responseData['response']
                    : $responseData['response']->toArray();

                $billAttrValue[$attribute['name']][] = $attrValue;

                $isFirst = true;
                foreach (($value['l11n'] ?? []) as $language => $l11n) {
                    if ($isFirst) {
                        $isFirst = false;
                        continue;
                    }

                    $response = new HttpResponse();
                    $request  = new HttpRequest();

                    $request->header->account = 1;
                    $request->setData('title', $l11n);
                    $request->setData('language', $language);
                    $request->setData('value', $attrValue['id']);

                    $module->apiBillAttributeValueL11nCreate($request, $response);
                }
            }
        }

        return $billAttrValue;
    }

    /**
     * Create tax combinations (item & client/supplier)
     *
     * @param ApplicationAbstract $app   Application
     * @param array               $taxes Tax data
     *
     * @return array<string, array>
     *
     * @since 1.0.0
     */
    private static function createTaxCombination(ApplicationAbstract $app, array $taxes) : array
    {
        $result = [];

        /** @var \Modules\Billing\Controller\ApiTaxController $module */
        $module = $app->moduleManager->get('Billing', 'ApiTax');

        /** @var \Modules\Attribute\Models\AttributeType $itemAttributeSales */
        $itemAttributeSales = ItemAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'sales_tax_code')
            ->execute();

        /** @var \Modules\Attribute\Models\AttributeType $itemAttributePurchase */
        $itemAttributePurchase = ItemAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'purchase_tax_code')
            ->execute();

        /** @var \Modules\Attribute\Models\AttributeType $clientAttributeSales */
        $clientAttributeSales = ClientAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'sales_tax_code')
            ->execute();

        /** @var \Modules\Attribute\Models\AttributeType $supplierAttributeSales */
        $supplierAttributeSales = SupplierAttributeTypeMapper::get()
            ->with('defaults')
            ->where('name', 'purchase_tax_code')
            ->execute();

        foreach ($taxes as $tax) {
            $itemValue = $tax['type'] === 1
                ? $itemAttributeSales->getDefaultByValue($tax['item_code'])
                : $itemAttributePurchase->getDefaultByValue($tax['item_code']);

            $accountValue = $tax['type'] === 1
                ? $clientAttributeSales->getDefaultByValue($tax['account_code'])
                : $supplierAttributeSales->getDefaultByValue($tax['account_code']);

            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('tax_type', $tax['type']);
            $request->setData('tax_code', $tax['tax_code']);
            $request->setData('item_code', $itemValue->id);
            $request->setData('account_code', $accountValue->id);
            $request->setData('account', $tax['account'] ?? null);

            $module->apiTaxCombinationCreate($request, $response);

            $responseData = $response->getData('');
            if (!\is_array($responseData)) {
                continue;
            }

            $result = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $results[] = $result;
        }

        return $result;
    }

    /**
     * Install default bill types
     *
     * @param ApplicationAbstract $app      Application
     * @param array               $types    Bill types
     * @param int                 $template Default template
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createBillTypes(ApplicationAbstract $app, array $types, int $template) : array
    {
        $billTypes = [];

        /** @var \Modules\Billing\Controller\ApiBillTypeController $module */
        $module = $app->moduleManager->get('Billing', 'ApiBillType');

        foreach ($types as $type) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $type['name'] ?? '');
            $request->setData('title', \reset($type['l11n']));
            $request->setData('language', \array_keys($type['l11n'])[0] ?? 'en');
            $request->setData('number_format', $type['numberFormat'] ?? '{id}');
            $request->setData('sign', $type['sign'] ?? 1);
            $request->setData('email', $type['email'] ?? false);
            $request->setData('transfer_stock', $type['transferStock'] ?? false);
            $request->setData('is_template', $type['isTemplate'] ?? false);
            $request->setData('is_accounting', $type['isAccounting'] ?? false);
            $request->setData('transfer_type', $type['transferType'] ?? BillTransferType::SALES);
            $request->setData('template', $template);

            $module->apiBillTypeCreate($request, $response);

            $responseData = $response->getData('');
            if (!\is_array($responseData)) {
                continue;
            }

            $billType = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $billTypes[] = $billType;

            $isFirst = true;
            foreach ($type['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $billType['id']);

                $module->apiBillTypeL11nCreate($request, $response);
            }
        }

        return $billTypes;
    }

    /**
     * Install default payment terms
     *
     * @param ApplicationAbstract $app   Application
     * @param array               $types Payment term definitions
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createPaymentTerms(ApplicationAbstract $app, array $types) : array
    {
        /** @var array<string, array> $paymentTerms */
        $paymentTerms = [];

        /** @var \Modules\Billing\Controller\ApiController $module */
        $module = $app->moduleManager->get('Billing', 'Api');

        /** @var array $type */
        foreach ($types as $type) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $type['name'] ?? '');
            $request->setData('title', \reset($type['l11n']));

            $module->apiPaymentTermCreate($request, $response);

            $responseData = $response->getData('');
            if (!\is_array($responseData)) {
                continue;
            }

            $paymentTerms[$type['name']] = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $isFirst = true;
            foreach ($type['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $paymentTerms[$type['name']]['id']);

                $module->apiPaymentTermL11nCreate($request, $response);
            }
        }

        return $paymentTerms;
    }

    /**
     * Install default shipping terms
     *
     * @param ApplicationAbstract $app   Application
     * @param array               $types Shipping term definitions
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createShippingTerms(ApplicationAbstract $app, array $types) : array
    {
        /** @var array<string, array> $shippingTerms */
        $shippingTerms = [];

        /** @var \Modules\Billing\Controller\ApiController $module */
        $module = $app->moduleManager->get('Billing', 'Api');

        /** @var array $type */
        foreach ($types as $type) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $type['name'] ?? '');
            $request->setData('title', \reset($type['l11n']));

            $module->apiShippingTermCreate($request, $response);

            $responseData = $response->getData('');
            if (!\is_array($responseData)) {
                continue;
            }

            $shippingTerms[$type['name']] = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $isFirst = true;
            foreach ($type['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $shippingTerms[$type['name']]['id']);

                $module->apiShippingTermL11nCreate($request, $response);
            }
        }

        return $shippingTerms;
    }
}
