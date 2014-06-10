from lib.ui_lib import *


def test_eig_controlpanel(browser, url, email, password):
    pytest.skip("EIG store creation fails in bamboo")
    login = CommonMethods(browser)
    login.go_to_admin(browser, url, email, password)

    panel = browser.find_elements_by_class_name('panel-inline')
    #Checking menu items under 'Setup & Tools'
    browser.find_element_by_link_text('Setup & Tools').click()
    setup = panel[2]
    setupoptions = setup.text
    assert 'SSL Certificate' not in setupoptions
    assert 'Email Accounts' not in setupoptions
    #Checking menu items under 'Billing'
    browser.find_element_by_link_text('Billing').click()
    billing = panel[0]
    billingoptions = billing.text
    assert 'Invoices' not in billingoptions
    assert 'Upgrade Account' not in billingoptions
    assert 'Purchase History' not in billingoptions
    #Checking whether menu items under 'Marketing'
    browser.find_element_by_link_text('Marketing').click()
    marketing = panel[7]
    marketingoptions = marketing.text
    assert 'Abandoned Cart Notifications' not in marketingoptions
    #Checking whether menu items under 'Analytics'
    browser.find_element_by_link_text('Analytics').click()
    analytics = panel[8]
    analyticoptions = analytics.text
    assert 'Abandoned Cart' not in analyticoptions
    #Checking EIG support text
    browser.find_element_by_link_text('Customer Support').click()
    supporttxt = browser.find_element_by_css_selector('.support-pillar.no-border').text
    assert 'Hours of operation' in supporttxt
    assert 'Telephone' in supporttxt
    assert 'Forum' in supporttxt
    #Checking for Upgrade plan text in Storage & Transfer page
    browser.find_element_by_link_text('Analytics').click()
    browser.find_element_by_link_text('Storage & Transfer').click()
    assert (browser.find_element_by_class_name('Heading1').text == 'Storage & Transfer')
    assert 'Upgrade your plan' not in browser.find_element_by_id('div0').text
    browser.find_element_by_link_text('Transfer').click()
    assert 'Upgrade your plan' not in browser.find_element_by_id('div1').text

