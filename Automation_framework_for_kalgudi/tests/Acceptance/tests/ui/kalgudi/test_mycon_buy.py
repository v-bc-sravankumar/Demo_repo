from helpers.ui.kalgudi.connects_class import *

def test_send_buy_email(browser, url, username, password):
    SBE = ConnectsBuyClass(browser)
    SBE.login(url,browser, username, password)       
    SBE.create_SendBuy(browser)
    SBE.logout(browser)

