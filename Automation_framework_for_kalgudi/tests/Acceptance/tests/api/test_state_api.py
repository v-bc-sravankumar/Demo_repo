from lib.api_lib import *

# JSON Payload

def test_get_all_states(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states')
    basic_auth_get(api, username, auth_token)

def test_get_state_by_country_id(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/226/states/1')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == 1
    assert newdata['state']    == "Alabama"
    assert newdata['state_abbreviation'] == "AL"
    assert newdata['country_id'] == 226

def test_get_state_by_id(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states/101')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == 101
    assert newdata['state']    == "Tirol"
    assert newdata['state_abbreviation'] == "TI"
    assert newdata['country_id'] == 14

def test_count_states(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] == 308

#XML Payload

def test_get_all_states_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')

def test_get_state_by_country_id_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/226/states/1')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == "1"
    assert newdata.find('state').text    == "Alabama"
    assert newdata.find('state_abbreviation').text == "AL"
    assert newdata.find('country_id').text == "226"

def test_get_state_by_id_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states/101')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == "101"
    assert newdata.find('state').text    == "Tirol"
    assert newdata.find('state_abbreviation').text == "TI"
    assert newdata.find('country_id').text == "14"

def test_count_states_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/states/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text == "308"
