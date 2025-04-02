# Taxes

Taxes for cusstomers/suppliers and items are automatically calculated based on specific indicators/tax codes that always work in combination with each other. Visually you can think about it as a matrix where both customer/supplier indicator and item tax code result in the respective taxes.

**Visualization**

|                         | Item sales_tax_code 1 | Item sales_tax_code 2 | Item sales_tax_code 2 |
| ----------------------- | --------------------- | --------------------- | --------------------- |
| Client sales_tax_code 1 | 19 % (domestic)       | 7 % (domestic)        | 19 % (domestic)       |
| Client sales_tax_code 2 | 0 % (intra-community) | 0 % (intra-community) | 0 % (intra-community) |
| Client sales_tax_code 2 | 0 % (third country)   | 0 % (third country)   | 0 % (third country)   |

These indicators/tax codes are completely custom and you can define them as you see fit.

## Items

Every item that you want to sell must have an attribute set called **Sales tax code** (`sales_tax_code`).

Every item that you want to purchase must have an attribute set called **Purchase tax code** (`purchase_tax_code`).

You can define these attributes when viewing a item. For more details please refer to the [Item Management]({/}?id=ItemManagement) documentation.

By default the Item Management module has already defined some sales and purchase tax codes that you can use. Of course you can create additional sales and purchase tax codes as you see fit.

## Clients

Every client/customer that you want to create invoices for must have an attribute set called **Sales tax code** (`sales_tax_code`). You can define this attribute when viewing a client. For more details please refer to the [Client Management]({/}?id=ClientManagement) documentation.

By default the Client Management module has already defined some sales tax codes that you can use. Of course you can create additional sales tax codes as you see fit. However, the default tax codes should be already sufficient to handle your use cases.

### Germany

Most German companies only need `DE` for domestic sales, `INT` for third country sales, `EU` for intra-community sales (B2B).

Most small businesses only need `DE_S` for domestic sales, `INT` for third country sales, `EU_S` for intra-community sales (B2B).

## Suppliers

Every supplier that you want to create invoices for must have an attribute set called **Purchase tax code** (`purchase_tax_code`). You can define this attribute when viewing a supplier. For more details please refer to the [Supplier Management]({/}?id=SupplierManagement) documentation.

By default the Supplier Management module has already defined some purchase tax codes that you can use. Of course you can create additional purchase tax codes as you see fit.

## Tax Codes

Tax codes define a single code that specifies how high the taxes are. These tax codes can also be used in accounting when creating a new entry and are therefore managed by the Finance module.

For more details please refer to the [Finance]({/}?id=Finance) module.

## Tax Combinations

After you defined the item sales/purchase tax code and client/purchase tax code you have to create the possible combinations that can occur. The tax combinations can be viewed and edited under `Finance >> Tax Combinations`

![Tax combination](Modules/Billing/Docs/Help/img/taxes/taxes_combination.png)

**Example visual representation**

The following example still follows the same structure as mentioned in the beginning but filled with actual values.

|     | GENERAL            | REDUCED          | SERVICE            |
| --- | ------------------ | ---------------- | ------------------ |
| DE  | DE_M19 (= 19% tax) | DE_M7 (= 7% tax) | DE_M19 (= 19% tax) |
| EU  | EU_S0 (= 0% tax)   | EU_S0 (= 0% tax) | EU_S0 (= 0% tax)   |
| INT | S0 (= 0% tax)      | S0 (= 0% tax)    | S0 (= 0% tax)      |

> Please note that despite effectively the equal tax amount (0 %) for EU and INT you probably have to use different tax codes due to your local tax laws and accounting practices.

> Sales and purchase combinations are separate, which means if you want to sell and purchase an item you will have to create at least one combination using sales tax codes and one using purchase tax codes.

### Actual combination

![Tax combinations](Modules/Billing/Docs/Help/img/taxes/taxes_combinations.png)

## Steps

1. Create additional item sales tax codes and purchase tax codes in the Item Management module as you see fit.
2. Create additional sales tax codes in the Client Management module as you see fit.
3. Create additional purchase tax codes in the Supplier Management module as you see fit.
4. Create additional tax codes if necessary in the Finance module.
5. Create additional tax code combinations in the Finance module as you see fit
