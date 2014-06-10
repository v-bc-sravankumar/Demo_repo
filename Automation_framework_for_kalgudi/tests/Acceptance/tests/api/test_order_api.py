from fixtures.order import *

# JSON Payload
# Create Order via API


def test_post_order(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, post_order_payload)
    newdata = json.loads(result.text)
    state['order_id'] = newdata['id']
    assert newdata['billing_address']['first_name'] == BILLING_FIRST_NAME
    assert newdata['billing_address']['last_name'] == BILLING_LAST_NAME
    assert newdata['billing_address']['company'] == BILLING_COMPANY
    assert newdata['billing_address']['street_1'] == BILLING_STREET_ADD1
    assert newdata['billing_address']['street_2'] == BILLING_STREET_ADD2
    assert newdata['billing_address']['city'] == BILLING_CITY
    assert newdata['billing_address']['state'] == BILLING_STATE
    assert newdata['billing_address']['zip'] == BILLING_POSTCODE
    assert newdata['billing_address']['country'] == "Australia"
    assert newdata['billing_address']['country_iso2'] == "AU"
    assert newdata['billing_address']['phone'] == BILLING_PHONE
    assert newdata['billing_address']['email'] == EMAIL
    assert newdata['order_is_digital'] == False
    assert float (newdata['discount_amount']) >= 5


def test_get_order_shipping_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shippingaddresses')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    state['shipping_id'] = newdata[0]['id']
    assert newdata[0]['order_id'] == state['order_id']
    assert newdata[0]['first_name'] == SHIPPING_FIRST_NAME
    assert newdata[0]['last_name'] == SHIPPING_LAST_NAME
    assert newdata[0]['company'] == SHIPPING_COMPANY
    assert newdata[0]['street_1'] == SHIPPING_STREET_ADD1
    assert newdata[0]['street_2'] == SHIPPING_STREET_ADD2
    assert newdata[0]['city'] == SHIPPING_CITY
    assert newdata[0]['zip'] == SHIPPING_POSTCODE
    assert newdata[0]['country'] == "Australia"
    assert newdata[0]['country_iso2'] == "AU"
    assert newdata[0]['phone'] == SHIPPING_PHONE


def test_get_order_by_ID(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['order_id']


def test_get_orders(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/')
    basic_auth_get(api, username, auth_token)


def test_count_Orders(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_get_order_products(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/products')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    state['product_order_id'] = newdata[0]['id']
    assert newdata[0]['order_id'] == state['order_id']
    assert newdata[0]['product_id'] == 75
    applied_discounts = newdata[0]['applied_discounts']
    assert applied_discounts[0]['id'] == "manual-discount"
    assert float(applied_discounts[0]['amount']) >= 5
    assert newdata[1]['product_id'] == 74

# Retreive a specific product for an order


def test_get_product_from_order(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/products/' + str(state['product_order_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['product_order_id']
    assert newdata['order_id'] == state['order_id']
    assert newdata['product_id'] == 75

# Products count


def test_count_products_in_order(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/products/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


# Update Order Status
def test_put_order_status(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    result = basic_auth_put(api, username, auth_token, put_order_status_payload)
    newdata = json.loads(result.text)
    newdata['status_id'] == "2"
    newdata['status'] == "Shipped"
    newdata['is_deleted'] == False

def test_put_order_products(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    payload = {
                       'products': [{
                                   'id': state['product_order_id'],
                                'product_id': 75,
                                'quantity': 5
                            }]
                    }
    basic_auth_put(api, username, auth_token, payload)

    # Verify product updated
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/products')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert result.status_code == 200
    assert newdata[0]['id'] == state['product_order_id']
    assert newdata[0]['order_id'] == state['order_id']
    assert newdata[0]['product_id'] == 75
    assert newdata[0]['quantity'] == 5


def test_put_order_billing_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    result = basic_auth_put(api, username, auth_token, put_order_billing_address_payload)
    newdata = json.loads(result.text)
    assert result.status_code == 200
    assert newdata['id'] == state['order_id']
    assert newdata['billing_address']['first_name'] == UPDATE_BILLING_FIRST_NAME
    assert newdata['billing_address']['last_name'] == UPDATE_BILLING_LAST_NAME
    assert newdata['billing_address']['company'] == UPDATE_BILLING_COMPANY
    assert newdata['billing_address']['street_1'] == UPDATE_BILLING_STREET_ADD1
    assert newdata['billing_address']['street_2'] == UPDATE_BILLING_STREET_ADD2
    assert newdata['billing_address']['city'] == UPDATE_BILLING_CITY
    assert newdata['billing_address']['state'] == UPDATE_BILLING_STATE
    assert newdata['billing_address']['zip'] == UPDATE_BILLING_POSTCODE
    assert newdata['billing_address']['phone'] == UPDATE_BILLING_PHONE


def test_put_order_shipping_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    payload = {
                                    'shipping_addresses': [
                                            {
                                                'id': state['shipping_id'],
                                                'first_name': UPDATE_FIRST_NAME,
                                                'last_name': UPDATE_LAST_NAME,
                                                'company': UPDATE_COMPANY,
                                                'street_1': UPDATE_STREET_ADD1,
                                                'street_2': UPDATE_STREET_ADD2,
                                                'city': UPDATE_CITY,
                                                'state': UPDATE_STATE,
                                                'zip': UPDATE_POSTCODE,
                                                'country': "Australia",
                                                'country_iso2': "AU",
                                                'phone': UPDATE_PHONE,
                                                'email': EMAIL
                                            }
                                        ]
                                    }
    result = basic_auth_put(api, username, auth_token, payload)
    # Verify Shipping updated
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shippingaddresses/' + str(state['shipping_id']))
    shipping_result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(shipping_result.text)
    assert newdata['id'] == state['shipping_id']
    assert newdata['first_name'] == UPDATE_FIRST_NAME
    assert newdata['last_name'] == UPDATE_LAST_NAME
    assert newdata['company'] == UPDATE_COMPANY
    assert newdata['street_1'] == UPDATE_STREET_ADD1
    assert newdata['street_2'] == UPDATE_STREET_ADD2
    assert newdata['city'] == UPDATE_CITY
    assert newdata['state'] == UPDATE_STATE
    assert newdata['zip'] == UPDATE_POSTCODE
    assert newdata['country'] == "Australia"
    assert newdata['country_iso2'] == "AU"
    assert newdata['phone'] == UPDATE_PHONE
    assert newdata['email'] == EMAIL


# Create Shipments
def test_post_shipment(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shipments/')
    post_shipment_payload.update({"order_address_id": state['shipping_id'],
                              "items": [{ "order_product_id": state['product_order_id'],
                              "quantity":1}]}
                             )
    result = basic_auth_post(api, username, auth_token, post_shipment_payload)
    newdata = json.loads(result.text)
    state['shipment_id'] = newdata['id']
    assert newdata['id'] == state['shipment_id']
    assert newdata['order_id'] == state['order_id']
    assert newdata['order_address_id'] == state['shipping_id']
    items = newdata['items']
    assert items[0]['order_product_id'] == state['product_order_id']
    assert items[0]['quantity'] == 1

# Get Shipments using ID
def test_get_shipment_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shipments/' + str(state['shipment_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['shipment_id']
    assert newdata['order_id'] == state['order_id']

# Update Shipments tracking number & comments
def test_put_shipment(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shipments/' + str(state['shipment_id']))
    result = basic_auth_put(api, username, auth_token, put_shipment_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['shipment_id']
    assert newdata['order_id'] == state['order_id']
    assert newdata['order_address_id'] == state['shipping_id']
    assert newdata['tracking_number'] == "Test Update"
    assert newdata['comments'] == "Shipment updated Test Automation script"

# Delete Shipments using ID
def test_delete_shipment(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/shipments/' + str(state['shipment_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


# Delete Order via API
def test_delete_order(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

#validations
def test_required_fields_order(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, invalid_customer_id_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'customer_id' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_date_created_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'date_created' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_items_shipped_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'items_shipped' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_order_is_digital_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'order_is_digital' is invalid."
    assert newdata[0]['status'] == 400

#billing address validations
def test_required_fields_billing_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, invalid_billing_address_email_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'email' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_country_billing_address_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'billing_address.country' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']
    assert a['invalid_reason'] == "Billing address country not supplied."
    result = basic_auth_post(api, username, auth_token, invalid_billing_address_country_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'country' is invalid."
    assert newdata[0]['status'] == 400

# Shipping Address validations
def test_required_shipping_address_fields(auth_token, url, username, state):
    api=urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, without_shipping_country_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'country' is invalid."
    assert newdata[0]['status'] == 400
    result=basic_auth_post(api, username, auth_token, without_shipping_country_iso2_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'country_iso2' is invalid."
    assert newdata[0]['status'] == 400
    result=basic_auth_post(api, username, auth_token, invalid_shipping_email_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'email' is invalid."
    assert newdata[0]['status'] == 400

#Shipping Product validations
def test_required_shipping_product_fields(auth_token, url, username, state):
    api=urlparse.urljoin(url,'api/v2/orders')
    result = basic_auth_post(api, username, auth_token,without_shipping_product_id_payload,1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'products.product_id, products.name' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_shipping_product_quantity_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'quantity' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_shipping_product_price_inc_tax_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'products.price_inc_tax' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_shipping_product_price_ex_tax_payload, 1)
    newdata=json.loads(result.text)
    assert newdata[0]['message'] == "The field 'products.price_ex_tax' is invalid."
    assert newdata[0]['status'] == 400


# #BIG-5853 API: Orders API allows empty arrays on the 'products' field
def test_bug_big_5853(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, product_with_empty_array_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == 'The required field \'products\' was not supplied.'



# BIG-5668 - API V2 - 409 on 'status_id' update
# Two stores are returning "Error (409): Quantities of one or more products are out of stock
# or did not meet quantity requirements." for a PUT request to the /orders/order_id resource with only the 'status_id' specified.
def test_bug_big_5668(auth_token, url, username, state):

    # Create a product, Set order_quantity_minimum = 2
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, product_with_quantity_payload )
    newdata = json.loads(result.text)
    product_id = newdata['id']

    # Create Order with Quantity = 2
    api = urlparse.urljoin(url, 'api/v2/orders')
    product_with_2_quantity_payload.update({
                                             'products': [{
                                                            'product_id': product_id,
                                                            'quantity': 2
                                                        }]
                                            })
    result = basic_auth_post(api, username, auth_token, product_with_2_quantity_payload)
    newdata = json.loads(result.text)
    order_id = newdata['id']

    # Update status ID to verify it returns 200
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id))
    payload = {'status_id': "6"}
    basic_auth_put(api, username, auth_token, payload)


    # Verify quantity in Order & get product_order_id to update quantity later
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id) + '/products')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    product_order_id = newdata[0]['id']
    assert newdata[0]['order_id'] == order_id
    assert newdata[0]['product_id'] == product_id
    assert newdata[0]['quantity'] == 2


    # Update quantity to 5 & Get product to verify its updated to 5
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id))
    payload = {
                   'products': [{
                               'id': product_order_id,
                            'product_id': product_id,
                            'quantity': 5
                        }]
                }
    basic_auth_put(api, username, auth_token, payload)
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id) + '/products')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    product_order_id = newdata[0]['id']
    assert newdata[0]['order_id'] == order_id
    assert newdata[0]['product_id'] == product_id
    assert newdata[0]['quantity'] == 5


    # Update quantity to below minimum quantity & verify test fails with status code 409 & return error message
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id))
    payload = {
                   'products': [{
                               'id': product_order_id,
                            'product_id': product_id,
                            'quantity': 1
                        }]
                }
    result = basic_auth_put(api, username, auth_token, payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == 'Quantities of one or more products are out of stock or did not meet quantity requirements.'

    # Verify product quantity is not updated.
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id) + '/products')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    product_order_id = newdata[0]['id']
    assert newdata[0]['order_id'] == order_id
    assert newdata[0]['product_id'] == product_id
    assert newdata[0]['quantity'] == 5

    # Delete Order
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(order_id))
    basic_auth_delete(api, username, auth_token)

    # Delete Product
    api = urlparse.urljoin(url, 'api/v2/products/' + str(product_id))
    basic_auth_delete(api, username, auth_token)

def test_min_max_date_filter(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')

    # first order
    result = basic_auth_post(api, username, auth_token, post_order_payload)
    newdata = json.loads(result.text)
    first_order_id = newdata['id']
    first_order_date_modified = newdata['date_modified'].replace("+", "%2b")

    # second order
    # Intentionally added a sleep
    time.sleep(1)
    result = basic_auth_post(api, username, auth_token, post_order_payload)
    newdata = json.loads(result.text)
    second_order_id = newdata['id']
    second_order_date_modified = newdata['date_modified'].replace("+", "%2b")

    # third order
    # Intentionally added a sleep
    time.sleep(2)
    result = basic_auth_post(api, username, auth_token, post_order_payload)
    newdata = json.loads(result.text)
    third_order_id = newdata['id']
    third_order_date_modified = newdata['date_modified'].replace("+", "%2b")

    get_orders_by_min_max_date_filter(url, username,
                                        first_order_date_modified,
                                        second_order_date_modified,
                                        third_order_date_modified,
                                        first_order_id, second_order_id, third_order_id,
                                        auth_token
                                        )

    get_orders_by_min_max_date_filter(url, username,
                                        convert_date_to_isoformat(first_order_date_modified),
                                        convert_date_to_isoformat(second_order_date_modified),
                                        convert_date_to_isoformat(third_order_date_modified),
                                        first_order_id, second_order_id, third_order_id,
                                        auth_token
                                        )
    # Remove all orders
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(first_order_id))
    basic_auth_delete(api, username, auth_token)

    api = urlparse.urljoin(url, 'api/v2/orders/' + str(second_order_id))
    basic_auth_delete(api, username, auth_token)

    api = urlparse.urljoin(url, 'api/v2/orders/' + str(third_order_id))
    basic_auth_delete(api, username, auth_token)


def get_orders_by_min_max_date_filter(url, username,
                                        first_order_date_modified,
                                        second_order_date_modified,
                                        third_order_date_modified,
                                        first_order_id, second_order_id, third_order_id,
                                        auth_token):
    # get order with min_date
    api = urlparse.urljoin(url, 'api/v2/orders/count?min_date_modified=' + first_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    count = newdata['count']

    api = urlparse.urljoin(url, 'api/v2/orders?min_date_modified=' + first_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert first_order_id and second_order_id and third_order_id in get_all_order_ids(count, newdata)

    # get order with max_date
    api = urlparse.urljoin(url, 'api/v2/orders/count?max_date_modified=' + third_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    count = newdata['count']

    api = urlparse.urljoin(url, 'api/v2/orders?max_date_modified=' + third_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert first_order_id and second_order_id and third_order_id in get_all_order_ids(count, newdata)

    # get order with 2nd order modified date & max_date
    api = urlparse.urljoin(url, 'api/v2/orders/count?min_date_modified=' + second_order_date_modified + '&max_date_modified=' + third_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    count = newdata['count']

    api = urlparse.urljoin(url, 'api/v2/orders?min_date_modified=' + second_order_date_modified + '&max_date_modified=' + third_order_date_modified)
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert second_order_id and third_order_id in get_all_order_ids(count, newdata)
    assert first_order_id not in get_all_order_ids(count, newdata)

def get_all_order_ids(count, newdata):
    i = 0
    order_ids = []
    while i < count:
        order_ids.append(newdata[i]['id'])
        i = i + 1
    return order_ids

def convert_date_to_isoformat(date_to_convert):
    date = (parser.parse(date_to_convert.replace("%2b", "+")))
    return date.isoformat().replace("+", "%2b")

# XML Payload
# Create Order via API
def test_post_order_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, post_order_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['order_id_xml'] = newdata.find('id').text
    assert newdata.find('billing_address/first_name').text == BILLING_FIRST_NAME
    assert newdata.find('billing_address/last_name').text == BILLING_LAST_NAME
    assert newdata.find('billing_address/company').text == BILLING_COMPANY
    assert newdata.find('billing_address/street_1').text == BILLING_STREET_ADD1
    assert newdata.find('billing_address/street_2').text == BILLING_STREET_ADD2
    assert newdata.find('billing_address/city').text == BILLING_CITY
    assert newdata.find('billing_address/state').text == BILLING_STATE
    assert newdata.find('billing_address/zip').text == BILLING_POSTCODE
    assert newdata.find('billing_address/country').text == "Australia"
    assert newdata.find('billing_address/country_iso2').text == "AU"
    assert newdata.find('billing_address/phone').text == BILLING_PHONE
    assert newdata.find('billing_address/email').text == EMAIL
    assert newdata.find('order_is_digital').text == "false"
    assert float (newdata.find('discount_amount').text) >= 5


def test_get_order_shipping_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/shippingaddresses')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['shipping_id'] = newdata[0].find('id').text
    assert newdata[0].find('order_id').text == state['order_id_xml']
    assert newdata[0].find('first_name').text == SHIPPING_FIRST_NAME
    assert newdata[0].find('last_name').text == SHIPPING_LAST_NAME
    assert newdata[0].find('company').text == SHIPPING_COMPANY
    assert newdata[0].find('street_1').text == SHIPPING_STREET_ADD1
    assert newdata[0].find('street_2').text == SHIPPING_STREET_ADD2
    assert newdata[0].find('city').text == SHIPPING_CITY
    assert newdata[0].find('zip').text == SHIPPING_POSTCODE
    assert newdata[0].find('country').text == "Australia"
    assert newdata[0].find('country_iso2').text == "AU"
    assert newdata[0].find('phone').text == SHIPPING_PHONE


def test_get_order_by_ID_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['order_id_xml']


def test_get_orders_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_count_orders_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_get_order_products_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/products')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_order_id_xml'] = newdata[0].find('id').text
    assert newdata[0].find('order_id').text == state['order_id_xml']
    assert newdata[0].find('product_id').text == "75"
    assert newdata[0].find('applied_discounts/discount/id').text == "manual-discount"
    assert newdata[0].find('applied_discounts/discount/amount').text >= 5
    assert newdata[1].find('product_id').text == "74"

# Retreive a specific product for an order


def test_get_product_from_order_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/products/' + str(state['product_order_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['product_order_id_xml']
    assert newdata.find('order_id').text == state['order_id_xml']
    assert newdata.find('product_id').text == "75"

# Products count


def test_count_products_in_order_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/products/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0

# Update Order Status


def test_put_order_status_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_order_status_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    newdata.find('status_id').text == "2"
    newdata.find('status').text == "Shipped"
    newdata.find('is_deleted').text == "false"


def test_put_order_products_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    payload = {
                       'products': [{
                                   'id': state['product_order_id_xml'],
                                'product_id': 75,
                                'quantity': 5
                            }]
                    }
    basic_auth_put(api, username, auth_token, payload, payload_format = 'xml')
    # Verify product updated
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/products')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert result.status_code == 200
    assert newdata[0].find('id').text == state['product_order_id_xml']
    assert newdata[0].find('order_id').text == state['order_id_xml']
    assert newdata[0].find('product_id').text == "75"
    assert newdata[0].find('quantity').text == "5"


def test_put_order_billing_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_order_billing_address_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert result.status_code == 200
    assert newdata.find('id').text == state['order_id_xml']
    assert newdata.find('billing_address/first_name').text == UPDATE_BILLING_FIRST_NAME
    assert newdata.find('billing_address/last_name').text == UPDATE_BILLING_LAST_NAME
    assert newdata.find('billing_address/company').text == UPDATE_BILLING_COMPANY
    assert newdata.find('billing_address/street_1').text == UPDATE_BILLING_STREET_ADD1
    assert newdata.find('billing_address/street_2').text == UPDATE_BILLING_STREET_ADD2
    assert newdata.find('billing_address/city').text == UPDATE_BILLING_CITY
    assert newdata.find('billing_address/state').text == UPDATE_BILLING_STATE
    assert newdata.find('billing_address/zip').text == UPDATE_BILLING_POSTCODE
    assert newdata.find('billing_address/phone').text == UPDATE_BILLING_PHONE


def test_put_order_shipping_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    payload = {
                                       'shipping_addresses': [
                                               {
                                                'id': state['shipping_id'],
                                                'first_name': UPDATE_FIRST_NAME,
                                                'last_name': UPDATE_LAST_NAME,
                                                'company': UPDATE_COMPANY,
                                                'street_1': UPDATE_STREET_ADD1,
                                                'street_2': UPDATE_STREET_ADD2,
                                                'city': UPDATE_CITY,
                                                'state': UPDATE_STATE,
                                                'zip': UPDATE_POSTCODE,
                                                'country': "Australia",
                                                'country_iso2': "AU",
                                                'phone': UPDATE_PHONE,
                                                'email': EMAIL
                                               }
                                           ]
                                    }
    result = basic_auth_put(api, username, auth_token, payload, payload_format = 'xml')
    # Verify Shipping updated
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']) + '/shippingaddresses/' + str(state['shipping_id']))
    shipping_result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(shipping_result.text)
    assert newdata.find('id').text == state['shipping_id']
    assert newdata.find('first_name').text == UPDATE_FIRST_NAME
    assert newdata.find('last_name').text == UPDATE_LAST_NAME
    assert newdata.find('company').text == UPDATE_COMPANY
    assert newdata.find('street_1').text == UPDATE_STREET_ADD1
    assert newdata.find('street_2').text == UPDATE_STREET_ADD2
    assert newdata.find('city').text == UPDATE_CITY
    assert newdata.find('state').text == UPDATE_STATE
    assert newdata.find('zip').text == UPDATE_POSTCODE
    assert newdata.find('country').text == "Australia"
    assert newdata.find('country_iso2').text == "AU"
    assert newdata.find('phone').text == UPDATE_PHONE
    assert newdata.find('email').text == EMAIL

# Delete Order via API


def test_delete_order_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')

#validations xml


def test_required_fields_order_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, invalid_customer_id_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'customer_id' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_date_created_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'date_created' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_items_shipped_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'items_shipped' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_order_is_digital_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'order_is_digital' is invalid."
    assert newdata[0].find('status').text == "400"

#billing address validations


def test_required_fields_billing_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, invalid_billing_address_email_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'email' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_country_billing_address_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'billing_address.country' is invalid."
    assert newdata[0].find('status').text == "400"
    assert newdata[0].find('details/invalid_reason').text == "Billing address country not supplied."
    result = basic_auth_post(api, username, auth_token, invalid_billing_address_country_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'country' is invalid."
    assert newdata[0].find('status').text == "400"

# Shipping Address validations


def test_required_shipping_address_fields_xml_payload(auth_token, url, username, state):
    api=urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token,without_shipping_country_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'country' is invalid."
    assert newdata[0].find('status').text == "400"
    result=basic_auth_post(api,username, auth_token,without_shipping_country_iso2_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'country_iso2' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_shipping_email_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'email' is invalid."
    assert newdata[0].find('status').text == "400"

# Shipping Product validations


def test_required_shipping_product_fields_xml_payload(auth_token, url, username, state):
    api=urlparse.urljoin(url,'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, without_shipping_product_id_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'products.product_id, products.name' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_shipping_product_quantity_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'quantity' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_shipping_product_price_inc_tax_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'products.price_inc_tax' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_shipping_product_price_ex_tax_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'products.price_ex_tax' is invalid."
    assert newdata[0].find('status').text == "400"
