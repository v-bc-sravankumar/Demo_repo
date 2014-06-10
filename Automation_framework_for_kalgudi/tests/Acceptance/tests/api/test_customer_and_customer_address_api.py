from fixtures.customer import *

# JSON Payload


def test_post_customer(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers')
    result = basic_auth_post(api, username, auth_token, post_customer_payload)
    newdata = json.loads(result.text)
    state['customer_id'] = newdata['id']
    assert newdata['id'] == state['customer_id']
    assert newdata['company'] == COMPANY
    assert newdata['first_name'] == FIRST_NAME
    assert newdata['last_name'] == LAST_NAME
    assert newdata['email'] == EMAIL
    assert newdata['phone'] == PHONE
    assert newdata['store_credit'] == "10.0000"
    assert newdata['registration_ip_address'] == "1.1.1.1"
    assert newdata['notes'] == "Automation Testing created this customer"
    assert newdata['addresses']['resource'] == "/customers/" + str(state['customer_id']) + "/addresses"
    assert "/api/v2/customers/" + str(state['customer_id']) + "/addresses.json" in newdata['addresses']['url']

# Validation for Mandatory Fields and Invalid Data for customer


def test_required_fields_customer(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers')
    result = basic_auth_post(api, username, auth_token, required_firstName_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'first_name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_lastName_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'last_name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_email_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'email' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_email_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'email' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_password_confirmation_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'password_confirmation' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_customer_group_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'customer_group_id' is invalid."
    assert newdata[0]['status'] == 400

# Validation for Mandatory Fields and Invalid Data for customer Address


def test_required_fields_customer_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']) + '/addresses')
    result = basic_auth_post(api, username, auth_token, required_firstName_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'first_name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_lastName_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'last_name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_street1_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'street_1' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_city_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'city' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_state_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'state' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_zip_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'zip' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_country_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'country' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, required_phone_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'phone' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_country_payload_address,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'country' is invalid."
    assert newdata[0]['status'] == 400


def test_get_customer_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['customer_id']
    assert newdata['company'] == COMPANY
    assert newdata['first_name'] == FIRST_NAME
    assert newdata['last_name'] == LAST_NAME
    assert newdata['email'] == EMAIL
    assert newdata['phone'] == PHONE


def test_get_customers(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers/')
    basic_auth_get(api, username, auth_token)


def test_put_customer(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']))
    result = basic_auth_put(api, username, auth_token, put_customer_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['customer_id']
    assert newdata['company'] == UPDATE_COMPANY
    assert newdata['first_name'] == UPDATE_FIRST_NAME
    assert newdata['last_name'] == UPDATE_LAST_NAME
    assert newdata['email'] == UPDATE_EMAIL
    assert newdata['phone'] == UPDATE_PHONE
    assert newdata['store_credit'] == "1.0000"
    assert newdata['registration_ip_address'] == "2.2.2.2"
    assert newdata['notes'] == "Automation Testing Updated this customer"
    assert newdata['addresses']['resource'] == "/customers/" + str(state['customer_id']) + "/addresses"
    assert "/api/v2/customers/" + str(state['customer_id']) + "/addresses.json" in newdata['addresses']['url']


def test_post_customer_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']) + '/addresses')
    post_address_payload.update({"customer_id": state['customer_id']})
    result = basic_auth_post(api, username, auth_token, post_address_payload)
    newdata = json.loads(result.text)
    state['address_id'] = newdata['id']
    assert newdata['customer_id'] == state['customer_id']
    assert newdata['first_name'] == FIRST_NAME
    assert newdata['last_name'] == LAST_NAME
    assert newdata['company'] == COMPANY
    assert newdata['street_1'] == STREET_ADD1
    assert newdata['street_2'] == STREET_ADD2
    assert newdata['city'] == CITY
    assert newdata['state'] == STATE
    assert newdata['zip'] == POSTCODE
    assert newdata['country'] == "Australia"
    assert newdata['country_iso2'] == "AU"
    assert newdata['phone'] == PHONE

    # Creating the same address second time to test DELETE all the addresses scenario
    basic_auth_post(api, username, auth_token, post_address_payload)


def test_put_customer_address(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']) + '/addresses/' + str(state['address_id']))
    result = basic_auth_put(api, username, auth_token, put_address_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['address_id']
    assert newdata['customer_id'] == state['customer_id']
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


def test_count_address_by_customer_ID(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']) + '/addresses/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_get_customer_address_by_address_ID(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/addresses/' + str(state['address_id']))
    basic_auth_get(api, username, auth_token)


def test_delete_customer_address_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']) + '/addresses/' + str(state['address_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_count_customer(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_delete_customer(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload


def test_post_customer_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers')
    result = basic_auth_post(api, username, auth_token, post_customer_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['customer_id_xml'] = newdata.find('id').text
    assert newdata.find('id').text == state['customer_id_xml']
    assert newdata.find('company').text == COMPANY
    assert newdata.find('first_name').text == FIRST_NAME
    assert newdata.find('last_name').text == LAST_NAME
    assert newdata.find('email').text == EMAIL
    assert newdata.find('phone').text == PHONE
    assert newdata.find('store_credit').text == "10.0000"
    assert newdata.find('registration_ip_address').text == "1.1.1.1"
    assert newdata.find('notes').text == "Automation Testing created this customer"
    assert "/customers/" + str(state['customer_id_xml']) + "/addresses" in newdata.find('addresses/link').text
    assert "/api/v2/customers/" + str(state['customer_id_xml']) + "/addresses" in newdata.find('addresses/link').attrib.get('href')

# Validation for Mandatory Fields and Invalid Data for customer

def test_required_fields_customer_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers')
    result = basic_auth_post(api, username, auth_token, required_firstName_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'first_name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_lastName_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'last_name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_email_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'email' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_email_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'email' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_password_confirmation_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'password_confirmation' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_customer_group_id_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'customer_group_id' is invalid."
    assert newdata[0].find('status').text == "400"

# Validation for Mandatory Fields and Invalid Data for customer Address

def test_required_fields_customer_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']) + '/addresses')
    result = basic_auth_post(api, username, auth_token, required_firstName_payload_address, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'first_name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_lastName_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'last_name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_street1_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'street_1' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_city_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'city' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_state_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'state' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_zip_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'zip' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_country_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'country' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, required_phone_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'phone' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_country_payload_address,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'country' is invalid."
    assert newdata[0].find('status').text == "400"


def test_get_customer_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['customer_id_xml']
    assert newdata.find('company').text == COMPANY
    assert newdata.find('first_name').text == FIRST_NAME
    assert newdata.find('last_name').text == LAST_NAME
    assert newdata.find('email').text == EMAIL
    assert newdata.find('phone').text == PHONE


def test_get_customers_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers/')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_put_customer_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_customer_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['customer_id_xml']
    assert newdata.find('company').text == UPDATE_COMPANY
    assert newdata.find('first_name').text == UPDATE_FIRST_NAME
    assert newdata.find('last_name').text == UPDATE_LAST_NAME
    assert newdata.find('email').text == UPDATE_EMAIL
    assert newdata.find('phone').text == UPDATE_PHONE
    assert newdata.find('store_credit').text == "1.0000"
    assert newdata.find('registration_ip_address').text == "2.2.2.2"
    assert newdata.find('notes').text == "Automation Testing Updated this customer"
    assert "/customers/" + str(state['customer_id_xml']) + "/addresses" in newdata.find('addresses/link').text
    assert "/api/v2/customers/" + str(state['customer_id_xml']) + "/addresses" in newdata.find('addresses/link').attrib.get('href')


def test_post_customer_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']) + '/addresses')
    post_address_payload.update({"customer_id": state['customer_id_xml']})
    result = basic_auth_post(api, username, auth_token, post_address_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['address_id_xml'] = newdata.find('id').text
    assert newdata.find('customer_id').text  == state['customer_id_xml']
    assert newdata.find('first_name').text == FIRST_NAME
    assert newdata.find('last_name').text == LAST_NAME
    assert newdata.find('company').text == COMPANY
    assert newdata.find('street_1').text == STREET_ADD1
    assert newdata.find('street_2').text == STREET_ADD2
    assert newdata.find('city').text == CITY
    assert newdata.find('state').text == STATE
    assert newdata.find('zip').text == POSTCODE
    assert newdata.find('country').text == "Australia"
    assert newdata.find('country_iso2').text == "AU"
    assert newdata.find('phone').text == PHONE

    # Creating the same address second time to test DELETE all the addresses scenario
    basic_auth_post(api, username, auth_token, post_address_payload, payload_format = 'xml')


def test_put_customer_address_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']) + '/addresses/' + str(state['address_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_address_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text  == state['address_id_xml']
    assert newdata.find('customer_id').text  == state['customer_id_xml']
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


def test_count_address_by_customer_ID_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']) + '/addresses/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_get_customer_address_by_address_ID_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/addresses/' + str(state['address_id_xml']))
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_delete_customer_address_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']) + '/addresses/' + str(state['address_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')


def test_count_customer_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customers/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_delete_customer_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customers/' + str(state['customer_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
