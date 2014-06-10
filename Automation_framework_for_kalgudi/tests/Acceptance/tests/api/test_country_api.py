from lib.api_lib import *

# JSON Payload

def test_get_country(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries')
    basic_auth_get(api, username, auth_token)

def test_get_country_by_ID(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/226')
    r = basic_auth_get(api, username, auth_token)
    newdata = json.loads(r.text)
    assert newdata['id'] == 226
    assert newdata['country'] == "United States"
    assert newdata['country_iso2'] == "US"
    assert newdata['country_iso3'] == "USA"
    assert newdata['states']['resource'] == '/countries/226/states'
    assert '/api/v2/countries/226/states.json' in newdata['states']['url']

def test_count_countries(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/count')
    r = basic_auth_get(api, username, auth_token)
    newdata = json.loads(r.text)
    assert newdata['count'] == 242

# XML Payload

def test_get_country_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries')
    basic_auth_get(api, username, auth_token, payload_format='xml')

def test_get_country_by_ID_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/226')
    r = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(r.text)
    assert newdata.find('id').text == "226"
    assert newdata.find('country').text == "United States"
    assert newdata.find('country_iso2').text == "US"
    assert newdata.find('country_iso3').text == "USA"
    assert newdata.find('states/link').text == "/countries/226/states"
    assert '/api/v2/countries/226/states.xml' in newdata.find('states/link').attrib.get('href')

def test_count_countries_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/countries/count')
    r = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(r.text)
    assert newdata.find('count').text == "242"
