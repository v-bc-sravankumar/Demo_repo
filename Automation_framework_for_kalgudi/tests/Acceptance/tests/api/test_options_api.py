from fixtures.options import *


# JSON Payload

def test_get_options(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options/')
    basic_auth_get(api, username, auth_token)


def test_post_option(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, post_option_payload)
    newdata = json.loads(result.text)
    state['option_id'] = newdata['id']
    assert newdata['id'] == state['option_id']
    assert newdata['name']  == OPTION_NAME
    assert newdata['display_name']  == OPTION_DISPLAY_NAME
    assert newdata['type']  == TYPE
    assert newdata['values']['resource']  == "/options/" + str(state['option_id']) + "/values"
    assert "/api/v2/options/" + str(state['option_id']) + "/values.json" in newdata['values']['url']


def test_required_options_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, without_name_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_type_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    a = newdata[0]['details']['valid_types']
    actual_validtypes = a.split(',')
    expected_validtypes = ['RB','RT','S','CS','P','PI','T','MT','F','C','N','D']
    assert set(actual_validtypes) == set(expected_validtypes)
    result = basic_auth_post(api, username, auth_token, without_type_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'type' was not supplied."
    assert newdata[0]['status'] == 400


def test_get_option_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']) )
    basic_auth_get(api, username, auth_token)


def test_put_option(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']) )
    result = basic_auth_put(api, username, auth_token, put_option_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['option_id']
    assert newdata['name']  == UPDATE_OPTION_NAME
    assert newdata['display_name']  == UPDATE_OPTION_DISPLAY_NAME
    assert newdata['type']  == UPDATE_TYPE
    assert newdata['values']['resource']  == "/options/" + str(state['option_id']) + "/values"
    assert "/api/v2/options/" + str(state['option_id']) + "/values.json" in newdata['values']['url']


def test_count_option(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_post_option_value(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']) + '/values')
    result = basic_auth_post(api, username, auth_token, post_option_value_payload)
    newdata = json.loads(result.text)
    state['value_id'] = newdata['id']
    assert newdata['id'] == state['value_id']
    assert newdata['option_id'] == state['option_id']
    assert newdata['label'] == LABEL_TEXT
    assert newdata['value'] == LABEL_VALUE


def test_required_option_value_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']) + '/values')
    result = basic_auth_post(api, username, auth_token, without_label_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'label' was not supplied."
    assert newdata[0]['status'] == 400


def test_put_option_value(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/values/' + str(state['value_id']))
    result = basic_auth_put(api, username, auth_token, put_option_value_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['value_id']
    assert newdata['option_id'] == state['option_id']
    assert newdata['label'] == UPDATE_LABEL_TEXT
    assert newdata['value'] == UPDATE_LABEL_VALUE


def test_delete_option_value(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/values/' + str(state['value_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_delete_option_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload


def test_get_options_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options/')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_post_option_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, post_option_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['option_id_xml'] = newdata.find('id').text
    assert newdata.find('id').text == state['option_id_xml']
    assert newdata.find('name').text == OPTION_NAME
    assert newdata.find('display_name').text == OPTION_DISPLAY_NAME
    assert newdata.find('type').text == TYPE
    assert newdata.find('values/link').text == "/options/" + str(state['option_id_xml']) + "/values"


def test_required_options_fields_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, without_name_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_type_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'type' is invalid."
    assert newdata[0].find('status').text == "400"
    a = newdata[0].find('details/valid_types').text
    actual_validtypes = a.split(',')
    expected_validtypes = ('RB','RT','S','CS','P','PI','T','MT','F','C','N','D')
    assert set(actual_validtypes) == set(expected_validtypes)
    result = basic_auth_post(api, username, auth_token, without_type_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'type' was not supplied."
    assert newdata[0].find('status').text == "400"


def test_get_option_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id_xml']) )
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_put_option_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id_xml']) )
    result = basic_auth_put(api, username, auth_token, put_option_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['option_id_xml']
    assert newdata.find('name').text == UPDATE_OPTION_NAME
    assert newdata.find('display_name').text == UPDATE_OPTION_DISPLAY_NAME
    assert newdata.find('type').text == UPDATE_TYPE
    assert newdata.find('values/link').text == "/options/" + str(state['option_id_xml']) + "/values"


def test_count_option_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/options/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_post_option_value_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id_xml']) + '/values')
    result = basic_auth_post(api, username, auth_token, post_option_value_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['value_id_xml'] = newdata.find('id').text
    assert newdata.find('id').text == state['value_id_xml']
    assert newdata.find('option_id').text == state['option_id_xml']
    assert newdata.find('label').text == LABEL_TEXT
    assert newdata.find('value').text == LABEL_VALUE


def test_required_option_value_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id_xml']) + '/values')
    result = basic_auth_post(api, username, auth_token, without_label_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'label' was not supplied."
    assert newdata[0].find('status').text == "400"


def test_put_option_value_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/values/' + str(state['value_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_option_value_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['value_id_xml']
    assert newdata.find('option_id').text == state['option_id_xml']
    assert newdata.find('label').text == UPDATE_LABEL_TEXT
    assert newdata.find('value').text == UPDATE_LABEL_VALUE


def test_delete_option_value_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/values/' + str(state['value_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')


def test_delete_option_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
