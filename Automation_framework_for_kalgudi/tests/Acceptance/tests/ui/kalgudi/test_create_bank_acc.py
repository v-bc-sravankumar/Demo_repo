from helpers.ui.kalgudi.account_class import *
#from helpers.ui.control_panel.account_class import *


def test_create_bank_account(browser, url, username, password):
    Bankacc = AccClass(browser)
    Bankacc.login(url,browser, username, password)    
    Bankacc.create_BankAccount(browser)
    Bankacc.logout(browser)

