class ShippingData():
    
    australia_country_zone={"Country":{"Element":".test-country-code option[value='12']","Value":"Australia"}}
    
    us_country_zone={"Country":{"Element":".test-country-code option[value='222']","Value":"United States"}}
    
    uk_country_zone={"Country":{"Element":".test-country-code option[value='221']","Value":"United Kingdom"}}
    
    au_store_location={"CompanyName": {"Element":".test-company_name input","Value":"Bigcommerce"},
                       "Street":{"Element":".test-street input","Value":"Smail st"},
                       "City":  {"Element":".test-city input","Value":"Sydney"},
                       "Postcode": {"Element":".test-postcode input","Value":"2000"},
                       "Country": {"Element":".test-country option[value='12']","Value":"Australia"},
                       "State": {"Element":".test-state option[value='1']","Value":"New South Wales"}
                       }
    us_store_location={"CompanyName": {"Element":".test-company_name input","Value":"Bigcommerce"},
                       "Street":{"Element":".test-street input","Value":"Smail st"},
                       "City":  {"Element":".test-city input","Value":"New York"},
                       "Postcode": {"Element":".test-postcode input","Value":"10001"},
                       "Country": {"Element":".test-country option[value='222']","Value":"United States"},
                       "State": {"Element":".test-state option[value='42']","Value":"New York"}
                       }
    
    uk_store_location={"CompanyName": {"Element":".test-company_name input","Value":"Bigcommerce"},
                       "Street":{"Element":".test-street input","Value":"Smail st"},
                       "City":  {"Element":".test-city input","Value":"London"},
                       "Postcode": {"Element":".test-postcode input","Value":"EC1V 9HQ"},
                       "Country": {"Element":".test-country option[value='221']","Value":"United Kingdom"},
                       "State": {"Element":".test-state input","Value":"London"}
                       }
    
    canada_store_location={"CompanyName": {"Element":".test-company_name input","Value":"Bigcommerce"},
                       "Street":{"Element":".test-street input","Value":"Smail st"},
                       "City":  {"Element":".test-city input","Value":"Edmonton"},
                       "Postcode": {"Element":".test-postcode input","Value":"T5A0A7"},
                       "Country": {"Element":".test-country option[value='37']","Value":"Canada"},
                       "State": {"Element":".test-state option[value='0']","Value":"Alberta"}
                       }
    
    flat_rate_per_order_10={"DisplayName": {"Element":".test-display-name input","Value":"Flat Rate Per Order"},
                            "Rate":{"Element":".test-rate input","Value":"10.00"},
                            "Option": {"Element":".test-flatrate-option option[value='1']","Value":"per order"}
                            }
    
    flat_rate_per_item_10={"DisplayName": {"Element":".test-display-name input","Value":"Flat Rate Per Item"},
                            "Rate":{"Element":".test-rate input","Value":"10.00"},
                            "Option": {"Element":".test-flatrate-option option[value='0']","Value":"per order"}
                            }
    
    fedex={"Key": {"Element":"input[name='key']","Value":"J3Tr7gzUull0qzmZ"},
           "Password": {"Element":"input[name='password']","Value":"Ipkr0QnO2OEpxr2j4AMGpnXoA"},
           "AccountNumber": {"Element":"input[name='accountno']","Value":"510087968"},
           "MeterNumber": {"Element":"input[name='meterno']","Value":"118505486"},
           "Select": {"ServiceTypeAll":".multi-select-toolbar a",
                     "Dropofftype":"select[name='dropofftype'] option[value='3']",
                     "Packagingtype":"select[name='packagingtype'] option[value='2']",
                     "Ratetype":"select[name='ratetype'] option[value='1']",
                     "Destinationtype":"select[name='destinationtype'] option[value='1']",
                     "Testmode":"select[name='testmode'] option[value='1']"
                     }
           }
    
    
    au_post={"APIKey": {"Element":"input[name='auth_key']","Value":"28744ed5982391881611cca6cf5c2409"},
             
             "Select": {"ServiceTypeAll":".multi-select-toolbar a",
                        "TestMode":"div#test_mode label"
                        }             
            
            }
    
    usps={"Username": {"Element":"input[name='username']","Value":"562BIGCO3953"},
                             
           "Select": {"Server":"select[name='servertype'] option[value='1']",
                      "InternationalPackagesize":"select[name='internationalpackagesize'] option[value='1']",
                     "FirstClassMailInternational":"label[for='FirstClassMailIntl']+div .multi-select-toolbar a"
                     }
           }
    royal_mail={"DeliveryTypeAll":".multi-select-toolbar a",
                "PackingMethod":"select[name='packingmethod'] option[value='0']"                                   
            
            }
    

    # API payloads:

    us_store_location_payload={"company_name":"Bigcommerce",
                        "street":"Smail st",
                        "city":"New York",
                        "country_code":"US",
                        "state_code":"NY",
                        "postcode":"10001"
                        }

    au_store_location_payload={"company_name":"Bigcommerce",
                        "street":"Smail st",
                        "city":"New York",
                        "country_code":"AU",
                        "state_code":"NSW",
                        "postcode":"2000"
                        }

    ca_store_location_payload={"company_name":"Bigcommerce",
                        "street":"Smail st",
                        "city":"Alberta",
                        "country_code":"CA",
                        "state_code":"AB",
                        "postcode":"T5A0A7"
                        }


    uk_store_location_payload={"company_name":"Bigcommerce",
                        "street":"Smail st",
                        "city":"London",
                        "country_code":"GB",
                        "state_code":"",
                        "postcode":"EC1V 9HQ"
                        }

    us_shipping_zone_payload={"name":"United States",
                              "type":"country",
                              "enabled":True,
                              "default":False,
                              "zonevendorid":0,
                              "free_shipping":True,
                              "zonefreeshippingexcludefixed":0,
                              "zonefreeshippingtotal":0,
                              "handling_fee":0,
                              "handling_separate":False,
                              "zonehandlingtype":"none",
                              "locations":[{"locationcountryid":"0",
                                            "locationtype":"country",
                                            "locationvalue":"United States",
                                            "locationvalueid":226,
                                            "locationvendorid":"0"}],
                              "methods":[]}

    au_shipping_zone_payload={"name":"Australia",
                           "type":"country",
                           "enabled":True,
                           "default":False,
                           "zonevendorid":0,
                           "free_shipping":True,
                           "zonefreeshippingexcludefixed":0,
                           "zonefreeshippingtotal":0,
                           "handling_fee":0,
                           "handling_separate":False,
                           "zonehandlingtype":"none",
                           "locations":[{"locationcountryid":"0",
                                         "locationtype":"country",
                                         "locationvalue":"Australia",
                                         "locationvalueid":13,
                                         "locationvendorid":"0"}],
                           "methods":[]
                        }

    flat_rate_per_order_payload={"enabled":True,
                     "fixed_type":"flatrate",
                     "name":"Flat Rate Per Order",
                     "handling_fee":0,
                     "options":{"is_setup":"1",
                                "peritemcost":"0",
                                "shippingcost":10}
                                }