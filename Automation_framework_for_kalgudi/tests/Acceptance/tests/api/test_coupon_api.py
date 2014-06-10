from fixtures.coupon import *
import pytest

# JSON Payload


def test_get_coupons(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/coupons')
    basic_auth_get(api, username, auth_token)


def test_post_coupon(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/coupons')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['coupon_id'] = newdata['id']
    assert newdata['id'] == state['coupon_id']
    assert newdata['name'] == COUPON_NAME
    assert newdata['type'] == COUPON_TYPE
    assert newdata['amount'] == "5.0000"
    assert newdata['min_purchase'] == "10.0000"
    assert newdata['expires'] == "Thu, 04 Oct 2012 03:24:40 +0000"
    assert newdata['enabled'] == True
    assert newdata['code'] == COUPON_CODE
    assert newdata['applies_to']['entity'] == "categories"
    assert newdata['applies_to']['ids'][0] == 0
    assert newdata['num_uses'] == 0
    assert newdata['max_uses'] == 100
    assert newdata['max_uses_per_customer'] == 1
    assert newdata['restricted_to']["countries"][0] == "AU"


def test_get_coupon_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['coupon_id']
    assert newdata['name'] == COUPON_NAME
    assert newdata['type'] == COUPON_TYPE


def test_put_coupon(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id']))
    result = basic_auth_put(api, username, auth_token, put_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['coupon_id']
    assert newdata['name'] == UPDATE_COUPON_NAME
    assert newdata['type'] == UPDATE_COUPON_TYPE
    assert newdata['amount'] == "15.0000"
    assert newdata['min_purchase'] == "101.0000"
    assert newdata['expires'] == "Thu, 04 Oct 2012 03:24:40 +0000"
    assert newdata['enabled'] == False
    assert newdata['code'] == UPDATE_COUPON_CODE
    assert newdata['applies_to']['entity'] == "categories"
    assert newdata['applies_to']['ids'][0] == 0
    assert newdata['num_uses'] == 0
    assert newdata['max_uses'] == 10
    assert newdata['max_uses_per_customer'] == 2
    assert newdata['restricted_to']["countries"][0] == "AU"


def test_delete_coupon(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_required_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/coupons')
    result = basic_auth_post(api, username, auth_token, without_name_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_code_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'code' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_type_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_type_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'type' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_amount_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'amount' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_amount_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'amount' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_min_purchase_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'min_purchase' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_expires_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'expires' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']
    assert a['invalid_reason'] == "The provided value '1234' is not a valid RFC-2822 date."
    result = basic_auth_post(api, username, auth_token, invalid_enabled_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'enabled' is invalid."
    assert newdata[0]['status'] == 400

def test_required_applies_to_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/coupons')
    result = basic_auth_post(api, username, auth_token, without_name_payload, 1)
    result = basic_auth_post(api, username, auth_token, invalid_entity_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'entity' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_entity_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'applies_to->entity' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']
    assert a['invalid_reason'] == "The provided value '' is not valid, valid options are 'categories' or 'products'."
    result = basic_auth_post(api, username, auth_token, invalid_id_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'categories' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']
    assert a['invalid_reason'] == "Category id 100 does not exist."
    result = basic_auth_post(api, username, auth_token, invalid_ids_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'ids' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']
    assert a['invalid_reason'] == "The provided value is not a valid array."

def test_required_max_uses_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/coupons')
    result = basic_auth_post(api, username, auth_token, without_name_payload, 1)
    result = basic_auth_post(api, username, auth_token, invalid_max_uses_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'max_uses' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_max_uses_per_customer_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'max_uses_per_customer' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_countries_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'countries' is invalid."
    assert newdata[0]['status'] == 400



# XML Payload
# Seems like there is a bug
# <errors><error><status>400</status><message>
#<![CDATA[The field 'applies_to->entity' is invalid.]]></message><details>
#<invalid_reason>The provided value '' is not valid, valid options are 'categories' or 'products'.</invalid_reason></details>
#</error></errors>
# @pytest.mark.skipif("True")
# def test_get_coupons_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/coupons')
#     basic_auth_get(api, username, auth_token, payload_format='xml')
#
#
# @pytest.mark.skipif("True")
# def test_post_coupon_xml_payload(auth_token, url, username, state):
#     payload = {
#                 "name": COUPON_NAME,
#                 "code": COUPON_CODE,
#                 "type": COUPON_TYPE,
#                 "amount": 5,
#                 "min_purchase": 10,
#                 "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
#                 "enabled": True,
#                 "restricted_to": {"countries":["AU"]},
#                 "applies_to": {
#                     "entity": "categories",
#                        "ids": { "value": "0" }
#                 },
#                 "max_uses": 100,
#                 "max_uses_per_customer": 1
#
#     }
#     api = urlparse.urljoin(url, 'api/v2/coupons')
#     r = basic_auth_post(api, username, auth_token, payload, payload_format='xml')
#     newdata = etree.fromstring(r.text)
#     state['coupon_id_xml'] = newdata['id']
#     assert newdata.find('id').text == state['coupon_id_xml']
#     assert newdata.find('id').text == COUPON_NAME
#     assert newdata.find('id').text == COUPON_TYPE
#     assert newdata.find('id').text == "5.0000"
#     assert newdata.find('id').text == "10.0000"
#     assert newdata.find('id').text == "Thu, 04 Oct 2012 03:24:40 +0000"
#     assert newdata.find('id').text == True
#     assert newdata.find('id').text == COUPON_CODE
#     assert newdata['applies_to']['entity'] == "categories"
#     assert newdata['applies_to']['ids'][0] == 0
#     assert newdata.find('id').text == 0
#     assert newdata.find('id').text == 100
#     assert newdata.find('id').text == 1
#     assert newdata['restricted_to']["countries"][0] == "AU"
#
#
# @pytest.mark.skipif("True")
# def test_get_coupon_by_id_xml_payload(auth_token, url, username, state):
#     api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id_xml']))
#     r = basic_auth_get(api, username, auth_token, payload_format='xml')
#     newdata = etree.fromstring(r.text)
#     assert newdata['id'] == state['coupon_id_xml']
#     assert newdata['name'] == COUPON_NAME
#     assert newdata['type'] == COUPON_TYPE
#
#
# @pytest.mark.skipif("True")
# def test_put_coupon_xml_payload(auth_token, url, username, state):
#     payload = {
#                 "name": UPDATE_COUPON_NAME,
#                 "code": UPDATE_COUPON_CODE,
#                 "type": UPDATE_COUPON_TYPE,
#                 "amount": 15,
#                 "min_purchase": 101,
#                 "enabled": False,
#                 "applies_to": {
#                     "entity": "categories",
#                     "ids": [0]
#                 },
#                 "max_uses": 10,
#                 "max_uses_per_customer": 2,
#                 "restricted_to": {"countries":["AU"]}
#     }
#     api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id_xml']))
#     r = basic_auth_put(api, username, auth_token, payload, payload_format='xml')
#     newdata = etree.fromstring(r.text)
#     assert newdata['id'] == state['coupon_id_xml']
#     assert newdata['name'] == UPDATE_COUPON_NAME
#     assert newdata['type'] == UPDATE_COUPON_TYPE
#     assert newdata['amount'] == "15.0000"
#     assert newdata['min_purchase'] == "101.0000"
#     assert newdata['expires'] == "Thu, 04 Oct 2012 03:24:40 +0000"
#     assert newdata['enabled'] == False
#     assert newdata['code'] == UPDATE_COUPON_CODE
#     assert newdata['applies_to']['entity'] == "categories"
#     assert newdata['applies_to']['ids'][0] == 0
#     assert newdata['num_uses'] == 0
#     assert newdata['max_uses'] == 10
#     assert newdata['max_uses_per_customer'] == 2
#     assert newdata['restricted_to']["countries"][0] == "AU"
#
#
# @pytest.mark.skipif("True")
# def test_delete_coupon_xml_payload(auth_token, url, username, state):
#     api = urlparse.urljoin(url, 'api/v2/coupons/' + str(state['coupon_id_xml']))
#     basic_auth_delete(api, username, auth_token, payload_format='xml')
#     basic_auth_get(api, username, auth_token, 1, payload_format='xml')
