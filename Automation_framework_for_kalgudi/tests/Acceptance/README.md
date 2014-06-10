# Introduction

This file has the Framework structure which would be future proof, easy to locate already written methods.

For reference -  [here is the capability domain document](https://intranet.bigcommerce.com/display/ENGINEERING/2013/11/12/International+Schema+Rescue%3A+Customers+and+Shopping)

## Framework Structure

###  High level overview:

* Acceptance
  * fixtures
     * api
     * ui
        * brand.py
        * product.py
        * .
  * **helpers**

    *contains all the common methods, which can be consumed by test scripts, (line of code which is common in two scripts is the helper candidate.)*

     * **api**
        * **api.py**

            *contains api GET, POST, DELETE etc related common scripts*
     * **ui**
        * **admin**

             contains admin portal scripts distributed under their capability domain
            * login_and_logout.py
            * customers_and_shopping.py
            * orders.py
            * product_catalog.py
            * shipping.py
            * payments.py
            * tax.py
            * onboarding.py
            * billing.py
            * platform.py
            * geography.py
            * design_and_themes.py
            * store_settings.py
            * web_content.py
            * marketing.py
            * inventory.py

        * **store_front**

            *contains front panel portal scripts distributed under possible capability domains*

            * login_and_logout.py
            * shopping.py
            * __ TODO.py :  It's little tricky to categorise this section. Need your inputs on this. __
            * ...

   * **lib**
        * headers.py
        * includes.py
        * junit.py
        * **valid_tag_list.py**
           This file contains all capability domains listed as **@tags** and those tags can be used in the `test_*.py`. When we do this we can select some group of scripts out of all.
           Possible tags could be
           1. tax, payments, orders, i18n etc.
           2. smoke, regresssion, sanity.
           3. High, Medium, Low.

   * reports
   * sceeenshot_on_failure
   * api_token.py
   * conftest.py
   * reports.py
   * tests
        * api
            * test_brands_api.py
            * test_*.py
            * ..
            * .

        * ui

           All the tests should fall under any of these categories. (Please suggest more possible one's !!)
            * customers_and_shipping
            * ana
            * orders
            * product_catalog
                * test_product_crud.py
                * test_product_with_large_image.py
                * test_product_with_no_image.py
                * test_product_deleted_in_control_panel_and_store_front.py
            * shipping
            * payments
            * tax
            * onboarding
            * billing
            * platform
            * geography
            * design_and_themes
            * store_settings
            * web_content
            * marketing
            * inventory
            * integrated apps
            * scenarios

                It would hold the `test_*.py` which are scenario based and are combination of above categories. It could contain
                1. Mostly used work flows by the store customer.
                2. Mostly used flows by the merchants.
                3. And many more...



## Coming up ..

##### Naming conventions
##### Sample example scripts
##### Best practices
##### Guide to run all tests or selected tests






