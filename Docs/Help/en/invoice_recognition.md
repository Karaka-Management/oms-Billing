# Invoice recognition

When you upload a invoice the application will automatically try to recognize as many aspects of the invoice as possible using the supplier and item information you provide in the [Supplier Management]({/}?id=SupplierManagement) and [Item Management]({/}?id=ItemManagement). 

## Suppliers

Even though the application tries to automatically match the supplier against the suppliers in the datbase using various techniques it may be necessary to provide additional information to improve the matching process.

If you open a supplier you can define the attribute **Bill match pattern** (`bill_match_pattern`) where you can store a unique text identifier for this supplier that appears on all of his invoices. This text identifier will then be used to match invoices against this supplier.

Examples of such identifiers can be telephone numbers, an email address that is printed on the invoice or the name of the CEO.

> By default the matching algorithm will use the supplier name, supplier address and banking information (e.g. IBAN)

## Items

Items are recognized by their `name1` and `name2` localization (both must match). The matching process also only consideres items that have a price defined from the respective supplier. That price can be 0 though. 

If the item name on the supplier invoice is different from your `name1` and `name2` defined name you can define the item attribute **Bill match pattern** (`bill_match_pattern`) where you can store a text identifier/name for this specific item. This text identifier will then be used to match the supplier item name against your items.
