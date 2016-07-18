Notes
=====
- Other modules with items that can be sold, should contain a column in their tables for taxtype_id, 
  which will link to the ciniki_tax_types table.

- Tax rates can be applied to one or many tax types.

- Tax types can have one or more tax rates applied.

- When no taxes are to be applied, and tax type of Non-taxable should be created,
  with any name the business wants, and not have any tax rates assigned to it.

- default tax rates should be stored in the ciniki_tax_settings table with
  the tax type.

- tax rates cannot be removed if they have been used on any invoices.

- tax types cannont be removed if they have been used on any invoices, or are still 
  in use by other modules.

- tax types can be set to a non-working status, which will hide them from being 
  assigned to any new items.

- Tax Types are used to map which types of taxes are applicable to which products
    - example would be food/non-food products
    - Germany: food (7%), non-food (19%)
    - if taxtypes for a invoice item is 0, then no taxes are applied.

- The tax types should be easy to understand names like Food/Groceries/Books/Clothing/Services/etc.
    - tax type names should not be the taxes themselves.  This is an abstraction layer.

- it's better to more than less tax types
    - there are over 1000 tax codes in avalara

- The reason to separate taxes and taxtypes, is to allow businesses to setup their
  business products once with the tax types, and then alter taxes in the future.

- If a region introduces a new tax, it can be added to the taxes table, linked to tax types
    without having to update all the business products/services.

- In the future, more rules can be applied to taxes using the sapos_taxes table 
  and shipping destination if required.


An example for in Ontario, Canada:
    Taxtypes:
        1: Non-Taxable
        2: Taxable
    Taxes:
        Tax: HST (13%), taxtypes: 2

    Product taxtypes:
        groceries: taxtype=1 (no taxes)
        t-shirts: taxtype=2 (apply HST)

An example for Germany  
    Taxtypes:
        1: Food
        2: Non-Food

    Taxes:
        Food (7%), taxtypes: 1
        Non-Food (19%), taxtypes: 2

    Product taxtypes:
        groceries: 1 (apply 7%)
        Book: 2 (apply 19%)

An example of Quebec, Canada:
    Taxtypes:
        1: Non-taxable
        2: taxable
    
    Taxes:
        1: GST (5%), taxtypes: 2
        2: QST (9.975%), taxtypes: 2

    Tax type rates
        tax type 1: no rates
        tax type 2: 1,2 (has two taxes for this type)

    Product taxtypes:
        groceries: 1 (no taxes)
        t-shirts: 2 (apply GST and QST)
