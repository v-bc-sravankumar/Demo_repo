from lib.api_lib import *
from helpers.ui.store_front.checkout_class import *
from helpers.ui.control_panel.shipping_class import *
from helpers.ui.control_panel.payment_class import *
import pprint

def test_enable_feature_flag(browser,url, email, password):
    #setup Shipping
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    #setup Payment
    payment = PaymentClass(browser)
    payment.navigate_to_payment_setting()
    payment.set_authorize_net_payment(browser, transactiontype='Authorize & Capture')
    shippingapi=ShippingApi()
    #get browser cookies
    seleniumCookies= browser.get_cookies()
    requestCookies = {}
    for cookie in seleniumCookies:
        requestCookies[cookie['name']] = cookie['value']
    #US store location
    shippingapi.post_store_location(url, requestCookies, shipping.us_store_location_payload )
    #US country zone
    zoneid=shippingapi.post_shipping_zone(url, requestCookies, shipping.au_shipping_zone_payload)
    #Flat Rate Per Order
    shippingapi.post_shipping_flat_rate__per_order_method(url, requestCookies, shipping.flat_rate_per_order_payload, zoneid)
    checkout=CheckoutClass(browser)
    checkout.go_to_admin(browser, url, email, password)
    if "bigcommerce.com" not in url:
        checkout.set_feature_flag(browser, 'enable', 'OptimizedCheckout')
    payload={"OptimizedCheckoutRampup":100}
    apipath = urlparse.urljoin(url, 'admin/settings/checkout')
    r = requests.post(apipath, data=json.dumps(payload), headers={'content-type': 'application/json'}, cookies=requestCookies, verify=False)
    assert r.status_code==200


def get_session_id(url,cookies):
    apipath=urlparse.urljoin(url, '/checkout')
    payload={}
    result=requests.post(apipath, data=json.dumps(payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    item=result.json()
    id=item['id']

    return id


def post_guest_user(browser,url, session_id, cookies):
    checkout=CheckoutClass(browser)
    #post guest user
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/shopper')
    result = requests.post(apipath, data=json.dumps(checkout.post_guest_email_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    item=result.json()
    assert item['email']==checkout.post_guest_email_payload['email']
    assert item['is_guest']==True

    return item['email']


def post_billing_shipping_address(browser,url,session_id,cookies):
    checkout=CheckoutClass(browser)
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/address')
    result = requests.post(apipath, data=json.dumps(checkout.post_billing_shipping_address_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    address=result.json()
    assert address[0]['type']==checkout.post_shipping_address_payload[0]['type']
    assert address[1]['type']==checkout.post_billing_address_payload[0]['type']
    for subitem in address:
        assert subitem['id'] > 0
        assert subitem['full_name']==checkout.post_billing_shipping_address_payload[0]['full_name']
        assert subitem['address1']==checkout.post_billing_shipping_address_payload[0]['address1']
        assert subitem['address2']==checkout.post_billing_shipping_address_payload[0]['address2']
        assert subitem['city']==checkout.post_billing_shipping_address_payload[0]['city']
        assert subitem['state_code']==checkout.post_billing_shipping_address_payload[0]['state_code']
        assert subitem['zip_postcode']==checkout.post_billing_shipping_address_payload[0]['zip_postcode']
        assert subitem['country_code']==checkout.post_billing_shipping_address_payload[0]['country_code']

    return address


def post_shipping_address(browser,url, session_id, cookies):
    checkout=CheckoutClass(browser)
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/address')
    result = requests.post(apipath, data=json.dumps(checkout.post_shipping_address_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    address=result.json()
    assert address[0]['id'] > 0
    assert address[0]['full_name']==checkout.post_shipping_address_payload[0]['full_name']
    assert address[0]['address1']==checkout.post_shipping_address_payload[0]['address1']
    assert address[0]['address2']==checkout.post_shipping_address_payload[0]['address2']
    assert address[0]['city']==checkout.post_shipping_address_payload[0]['city']
    assert address[0]['state_code']==checkout.post_shipping_address_payload[0]['state_code']
    assert address[0]['zip_postcode']==checkout.post_shipping_address_payload[0]['zip_postcode']
    assert address[0]['country_code']==checkout.post_shipping_address_payload[0]['country_code']
    assert address[0]['type']==checkout.post_shipping_address_payload[0]['type']

    return address


def post_billing_address(browser,url, session_id, cookies):
    checkout=CheckoutClass(browser)
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/address')
    result = requests.post(apipath, data=json.dumps(checkout.post_billing_address_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    address=result.json()
    assert address[0]['id'] > 0
    assert address[0]['full_name']==checkout.post_shipping_address_payload[0]['full_name']
    assert address[0]['address1']==checkout.post_shipping_address_payload[0]['address1']
    assert address[0]['address2']==checkout.post_shipping_address_payload[0]['address2']
    assert address[0]['city']==checkout.post_shipping_address_payload[0]['city']
    assert address[0]['state_code']==checkout.post_shipping_address_payload[0]['state_code']
    assert address[0]['zip_postcode']==checkout.post_shipping_address_payload[0]['zip_postcode']
    assert address[0]['country_code']==checkout.post_shipping_address_payload[0]['country_code']
    assert address[0]['type']==checkout.post_shipping_address_payload[0]['type']

    return address


def get_shipping(url, session_id, cookies):
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/shipping')
    result=requests.get(apipath, headers={'Accept': 'application/json'},cookies=cookies)
    assert result.status_code==200
    shipping=result.json()
    shipping_id= shipping.keys()[0]

    return shipping_id


def post_shipping(url, session_id, cookies):
    shipping_id=get_shipping(url, session_id, cookies)
    shipping_payload={"shipping_quote_id":0,"shipping_address_id":shipping_id}
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/shipping')
    result = requests.post(apipath, data=json.dumps(shipping_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    shipping=result.json()

    return shipping,shipping_id


def post_order(url, session_id, cookies):

    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/order')
    payload={}
    result = requests.post(apipath, data=json.dumps(payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    order=result.json()
    order_id=order['order_id']
    order_token=order['order_token']
    assert order_id>0
    assert order_token>0

    return order_id, order_token


def post_payment(browser,url, session_id, cookies):
    checkout=CheckoutClass(browser)
    apipath=urlparse.urljoin(url, '/checkout/'+session_id+'/order/payment')
    result = requests.post(apipath, data=json.dumps(checkout.post_payment_payload), headers={'Accept': 'application/json'}, cookies=cookies)
    assert result.status_code==200
    payment=result.json()

    return payment


def test_optimized_checkout_api(browser,url,email,password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    payment = PaymentClass(browser)
    payment.navigate_to_payment_setting()
    payment.set_authorize_net_payment(browser, transactiontype='Authorize & Capture')
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(shipping.us_store_location)
    shipping.add_country_zone(shipping.australia_country_zone)
    shipping.open_country_zone("Australia")
    shipping.setup_free_shipping()
    checkout=CheckoutClass(browser)

    if 'https://' in url:
        url = url.replace('https://', 'http://')

    browser.get(urlparse.urljoin(url, 'donatello-brown-leather-handbag-with-shoulder-strap'))
    checkout.wait_until_element_present('.add-to-cart', 'CSS_SELECTOR').click()
    element = checkout.wait_until_element_present('.ProceedToCheckout a', 'CSS_SELECTOR')
    element.click()
    seleniumCookies= browser.get_cookies()
    requestCookies = {}
    for cookie in seleniumCookies:
        requestCookies[cookie['name']] = cookie['value']

    session_id=get_session_id(url,requestCookies)
    guest_user=post_guest_user(browser,url, session_id, requestCookies)
    post_shipping_address(browser,url, session_id, requestCookies)
    post_billing_address(browser,url, session_id, requestCookies)
    shipping, shipping_id=post_shipping(url, session_id, requestCookies)
    order_id, order_token=post_order(url, session_id, requestCookies)
    payment=post_payment(browser,url, session_id, requestCookies)
    print pprint.pformat( payment, indent=4 )

    assert payment['billing_address']['address1']==checkout.post_billing_address_payload[0]['address1']
    assert payment['billing_address']['address2']==checkout.post_billing_address_payload[0]['address2']
    assert payment['billing_address']['city']==checkout.post_billing_address_payload[0]['city']
    assert payment['billing_address']['email']==guest_user
    assert payment['order_id']==order_id
    assert payment['order_token']==order_token
    assert payment['shipping_address']['address1']==checkout.post_shipping_address_payload[0]['address1']
    assert payment['shipping_address']['address2']==checkout.post_shipping_address_payload[0]['address2']
    assert payment['shipping_address']['city']==checkout.post_shipping_address_payload[0]['city']
    assert payment['payment']['providers'][0]['name']=="Authorize.net"
    assert payment['shipping_quotes'][shipping_id][0]['description']=="Free Shipping"

