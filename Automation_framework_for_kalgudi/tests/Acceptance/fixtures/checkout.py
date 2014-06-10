from lib.ui_lib import *
class CheckoutData():

    def __init__(self):
    
        self.us_checkout={"FirstName": {"Element":"FormField_4","Value":"Test"},
                 "LastName": {"Element":"FormField_5","Value":"Engineer"},
                 "Company": {"Element":"FormField_6","Value":"Bigcommerce"},
                 "Phone": {"Element":"FormField_7","Value":"0111111111"},
                 "Address1": {"Element":"FormField_8","Value":"4 CORPORATE SQ"},
                 "Adress2": {"Element":"FormField_9","Value":" "},
                 "City": {"Element":"FormField_10","Value":"Sydney"},
                 "Postcode": {"Element":"FormField_13","Value":"10004"},
                 "Country": {"Element":"#FormField_11 option[value='United States']","Value":"United States"},
                 "State": {"Element":"#FormField_12 option[value='New York']","Value":"New York"}

                 }
    
    
        self.au_checkout={"FirstName": {"Element":"FormField_4","Value":"Test"},
                 "LastName": {"Element":"FormField_5","Value":"Engineer"},
                 "Company": {"Element":"FormField_6","Value":"Bigcommerce"},
                 "Phone": {"Element":"FormField_7","Value":"0111111111"},
                 "Address1": {"Element":"FormField_8","Value":"4 CORPORATE SQ"},
                 "Adress2": {"Element":"FormField_9","Value":" "},
                 "City": {"Element":"FormField_10","Value":"Sydney"},
                 "Postcode": {"Element":"FormField_13","Value":"2000"},
                 "Country": {"Element":"#FormField_11 option[value='Australia']","Value":"Australia"},
                 "State": {"Element":"#FormField_12 option[value='New South Wales']","Value":"New South Wales"}

                 }

        self.account_details_new={ "Username": {"Element": "account-details-email", "Value": "test.engineer+"+CommonMethods.generate_random_string()+"@bigcommerce.com"},
                          "Password": {"Element": "account-details-password", "Value": "password1"}

                        }

        self.us_shipping_address={"Name": {"Element": "#address-full-name", "Value": "Test Engineer"},
                         "Address1": {"Element": "#address-address1", "Value": " Test street"},
                         "Address2": {"Element": "#address-address2", "Value": " test street 2"},
                         "Suburb": {"Element": "#address-city", "Value": "New York"},
                         "Zip": {"Element": "#address-zip-post-code", "Value": "10001"},
                         "Phone": {"Element": "#address-phone", "Value": "123456"},
                         "Select": {"Country":{"Element": "#address-country-code option[value='222']","Value":"United States"},
                                    "State": {"Element": "#address-state-code option[value='42']", "Value":"New York"}
                                    }

                            }

        self.au_shipping_address={"Name": {"Element": "#address-full-name", "Value": "Test Engineer"},
                         "Address1": {"Element": "#address-address1", "Value": " Test street"},
                         "Address2": {"Element": "#address-address2", "Value": " test street 2"},
                         "Suburb": {"Element": "#address-city", "Value": "Sydney"},
                         "Zip": {"Element": "#address-zip-post-code", "Value": "2000"},
                         "Phone": {"Element": "#address-phone", "Value": "123456"},
                         "Select": {"Country": {"Element":"#address-country-code option[value='12']","Value":"Australia"},
                                    "State": {"Element":"#address-state-code option[value='1']", "Value":"New South Wales"}
                                    }

                         }


        self.us_billing_address={"Name": {"Element": "#address-full-name", "Value": "Test Engineer"},
                         "Address1": {"Element": "#address-address1", "Value": " Test street"},
                         "Address2": {"Element": "#address-address2", "Value": " test street 2"},
                         "Suburb": {"Element": "#address-city", "Value": "New York"},
                         "Zip": {"Element": "#address-zip-post-code", "Value": "10001"},
                         "Phone": {"Element": "#address-phone", "Value": "123456"},
                         "Select": {"Country":{"Element": "#address-country-code option[value='222']","Value":"United States"},
                                    "State": {"Element": "#address-state-code option[value='42']", "Value":"New York"}
                                    }

                            }

        self.au_billing_address={"Name": {"Element": "#address-full-name", "Value": ""},
                         "Address1": {"Element": "#address-address1", "Value": ""},
                         "Address2": {"Element": "#address-address2", "Value": ""},
                         "Suburb": {"Element": "#address-city", "Value": ""},
                         "Zip": {"Element": "#address-zip-post-code", "Value": ""},
                         "Phone": {"Element": "#address-phone", "Value": ""},
                         "Select": {"Country": {"Element":"#address-country-code option[value='12']","Value":"Australia"},
                                    "State": {"Element":"#address-state-code option[value='1']", "Value":"New South Wales"}
                                    }

                        }

        self.visa_card={ "CardNumber": {"Element": "ccnumber", "Value": "4111111111111111"},
                "CardName": {"Element": "ccname", "Value": "Test Engineer"},
                "CCV": {"Element": "ccv", "Value": "123"},
                "Select": {"PaymentMethod": "#payment-provider-name",
                           "ExMonth": "#cc-0-cc-month option[value='10']",
                           "ExYear": "#cc-0-cc-year option[value='6']"}

                        }


        self.post_guest_email_payload={"email":"test.engineer+"+CommonMethods.generate_random_string()+"@bigcommerce.com"}

        self.post_shipping_address_payload=[{"type":["shipping"],
                                             "full_name":"Test Engineer",
                                             "address1":"Address 1",
                                             "address2":"Address 2",
                                             "city":"Sydney",
                                             "state_code":"NSW",
                                             "zip_postcode":"2000",
                                             "country_code":"AU",
                                             "phone":"0406234567"}]

        self.post_billing_address_payload=[{"type":["billing"],
                                             "full_name":"Test Engineer",
                                             "address1":"Billing Address 1",
                                             "address2":"Billing Address 2",
                                             "city":"North Ryde",
                                             "state_code":"NSW",
                                             "zip_postcode":"2113",
                                             "country_code":"AU",
                                             "phone":"040623345"}]

        self.post_billing_shipping_address_payload=[{"type":["billing", "shipping"],
                                             "full_name":"Test Engineer",
                                             "address1":"BillingShipping Address 1",
                                             "address2":"BillingShipping Address 2",
                                             "city":"North Ryde",
                                             "state_code":"NSW",
                                             "zip_postcode":"2113",
                                             "country_code":"AU",
                                             "phone":"040623345"}]

        self.post_payment_payload={"ccv_check":"123",
                                   "month":"03",
                                   "year":"2020",
                                   "name":"Test Engineer",
                                   "number":"4111111111111111",
                                   "provider":"checkout_authorizenet"}

