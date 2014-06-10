from lib.ui_lib import *
class AccountData():

    def __init__(self):


        self.create_account_details={"Email": {"Element":"FormField_1","Value":"test.engineer+"+CommonMethods.generate_random_string()+"@bigcommerce.com"},
                       "Password":{"Element":"FormField_2","Value":"password1"},
                       "ConfirmPassword":  {"Element":"FormField_3","Value":"password1"},
                       "FirstName": {"Element":"FormField_4","Value":"Test"},
                       "LastName": {"Element":"FormField_5","Value":"Engineer"},
                       "Company": {"Element":"FormField_6","Value":"Bigcommerce"},
                       "Phone": {"Element":"FormField_7","Value":" 12 Ryde streeet"},
                       "Address1": {"Element":"FormField_8","Value":""},
                       "Postcode": {"Element":"FormField_13","Value":"2113"},
                       "City": {"Element":"FormField_10","Value":"North Ryde"},
                       "Select": {"Country": {"Element":"FormField_11","Value":"Australia"},
                                    "State": {"Element":"FormField_12", "Value":"New South Wales"}
                                    }

                       }
        self.au_customer_address={
                       "FirstName": {"Element":"FormField_4","Value":"Test"},
                       "LastName": {"Element":"FormField_5","Value":"Engineer"},
                       "Company": {"Element":"FormField_6","Value":"Bigcommerce"},
                       "Phone": {"Element":"FormField_7","Value":" 12345"},
                       "Address1": {"Element":"FormField_8","Value":"12 Ryde streeet"},
                       "Postcode": {"Element":"FormField_13","Value":"2113"},
                       "City": {"Element":"FormField_10","Value":"North Ryde"},
                       "Select": {"Country": {"Element":"#FormField_11 option[value='Australia']","Value":"Australia"},
                                    "State": {"Element":"#FormField_12 option[value='New South Wales']", "Value":"New South Wales"}
                                    }

                       }

        self.us_customer_address={
                       "FirstName": {"Element":"FormField_4","Value":"Test"},
                       "LastName": {"Element":"FormField_5","Value":"Engineer"},
                       "Company": {"Element":"FormField_6","Value":"Bigcommerce"},
                       "Phone": {"Element":"FormField_7","Value":" 12345"},
                       "Address1": {"Element":"FormField_8","Value":"12 Ryde streeet"},
                       "Postcode": {"Element":"FormField_13","Value":"10004"},
                       "City": {"Element":"FormField_10","Value":"New York"},
                       "Select": {"Country": {"Element":"#FormField_11 option[value='United States']","Value":"United States"},
                                    "State": {"Element":"#FormField_12 option[value='New York']", "Value":"New York"}
                                    }

                       }