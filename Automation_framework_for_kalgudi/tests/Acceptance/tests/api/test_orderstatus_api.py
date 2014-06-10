from lib.api_lib import *

# JSON Payload

def test_get_orderstatus(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/orderstatuses')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)

    assert newdata[0]['id'] == 0
    assert newdata[0]['name'] == "Incomplete"
    assert newdata[0]['order'] == 0

    assert newdata[1]['id'] == "1"
    assert newdata[1]['name'] == "Pending"
    assert newdata[1]['order'] == "1"

    assert newdata[2]['id'] == "2"
    assert newdata[2]['name'] == "Shipped"
    assert newdata[2]['order'] == "8"

    assert newdata[3]['id'] == "3"
    assert newdata[3]['name'] == "Partially Shipped"
    assert newdata[3]['order'] == "6"

    assert newdata[4]['id'] == "4"
    assert newdata[4]['name'] == "Refunded"
    assert newdata[4]['order'] == "11"

    assert newdata[5]['id'] == "5"
    assert newdata[5]['name'] == "Cancelled"
    assert newdata[5]['order'] == "9"

    assert newdata[6]['id'] == "6"
    assert newdata[6]['name'] == "Declined"
    assert newdata[6]['order'] == "10"

    assert newdata[7]['id'] == "7"
    assert newdata[7]['name'] == "Awaiting Payment"
    assert newdata[7]['order'] == "2"

    assert newdata[8]['id'] == "8"
    assert newdata[8]['name'] == "Awaiting Pickup"
    assert newdata[8]['order'] == "5"

    assert newdata[9]['id'] == "9"
    assert newdata[9]['name'] == "Awaiting Shipment"
    assert newdata[9]['order'] == "4"

    assert newdata[10]['id'] == "10"
    assert newdata[10]['name'] == "Completed"
    assert newdata[10]['order'] == "7"

    assert newdata[11]['id'] == "11"
    assert newdata[11]['name'] == "Awaiting Fulfillment"
    assert newdata[11]['order'] == "3"

    assert newdata[12]['id'] == "12"
    assert newdata[12]['name'] == "Manual Verification Required"
    assert newdata[12]['order'] == "12"

def test_get_orderstatus_by_ID(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/orderstatuses/3')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == "3"
    assert newdata['name'] == "Partially Shipped"
    assert newdata['order'] == "6"

# XML Payload

def test_get_orderstatus_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/orderstatuses')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)

    assert newdata[0].find('id').text == "0"
    assert newdata[0].find('name').text == "Incomplete"
    assert newdata[0].find('order').text == "0"

    assert newdata[1].find('id').text == "1"
    assert newdata[1].find('name').text == "Pending"
    assert newdata[1].find('order').text == "1"

    assert newdata[2].find('id').text == "2"
    assert newdata[2].find('name').text == "Shipped"
    assert newdata[2].find('order').text == "8"

    assert newdata[3].find('id').text == "3"
    assert newdata[3].find('name').text == "Partially Shipped"
    assert newdata[3].find('order').text == "6"

    assert newdata[4].find('id').text == "4"
    assert newdata[4].find('name').text == "Refunded"
    assert newdata[4].find('order').text == "11"

    assert newdata[5].find('id').text == "5"
    assert newdata[5].find('name').text == "Cancelled"
    assert newdata[5].find('order').text == "9"

    assert newdata[6].find('id').text == "6"
    assert newdata[6].find('name').text == "Declined"
    assert newdata[6].find('order').text == "10"

    assert newdata[7].find('id').text == "7"
    assert newdata[7].find('name').text == "Awaiting Payment"
    assert newdata[7].find('order').text == "2"

    assert newdata[8].find('id').text == "8"
    assert newdata[8].find('name').text == "Awaiting Pickup"
    assert newdata[8].find('order').text == "5"

    assert newdata[9].find('id').text == "9"
    assert newdata[9].find('name').text == "Awaiting Shipment"
    assert newdata[9].find('order').text == "4"

    assert newdata[10].find('id').text == "10"
    assert newdata[10].find('name').text == "Completed"
    assert newdata[10].find('order').text == "7"

    assert newdata[11].find('id').text == "11"
    assert newdata[11].find('name').text == "Awaiting Fulfillment"
    assert newdata[11].find('order').text == "3"

    assert newdata[12].find('id').text == "12"
    assert newdata[12].find('name').text == "Manual Verification Required"
    assert newdata[12].find('order').text == "12"

def test_get_orderstatus_by_ID_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/orderstatuses/3')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == "3"
    assert newdata.find('name').text == "Partially Shipped"
    assert newdata.find('order').text == "6"
