from lib.ui_lib import *


def test_hps_controlpanel(browser, url, email, password):
    pytest.skip("HPS store creation fails in bamboo")
    login = CommonMethods(browser)
    login.go_to_admin(browser, url, email, password)

    panel = browser.find_elements_by_class_name('panel-inline')
    #Checking menu items under 'Billing'
    browser.find_element_by_link_text('Billing').click()
    billing = panel[0]
    assert billing.is_displayed()
    billingoptions = billing.text
    assert 'Account Summary' in billingoptions 
    assert 'Account Details' in billingoptions 
    assert 'Invoices' not in billingoptions 
    assert 'Upgrade Account' not in billingoptions 
    assert 'Purchase History' not in billingoptions 
    #Checking menu items under Help
    browser.find_element_by_link_text('Help').click()
    help = panel[1]
    assert help.is_displayed()
    helpoptions = help.text
    assert 'Support Portal' in helpoptions
    assert 'Bigcommerce University' in helpoptions
    assert 'Online Training' not in helpoptions
    #Checking HPS support text
    browser.find_element_by_link_text('Customer Support').click()
    supporttxt = browser.find_element_by_css_selector('.support-pillar.no-border').text
    assert 'Hours of operation' in supporttxt
    assert 'Telephone' in supporttxt
    assert 'Email' in supporttxt 
