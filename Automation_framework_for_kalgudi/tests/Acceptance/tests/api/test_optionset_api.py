from fixtures.options import *

# JSON Payload


def test_get_optionset(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets/')
    basic_auth_get(api, username, auth_token)


def test_post_optionset(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets')
    result = basic_auth_post(api, username, auth_token, post_option_set_payload)
    newdata = json.loads(result.text)
    state['option_set_id'] = newdata['id']
    assert newdata['name'] == OPTION_SET_NAME


def test_required_optionset_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets')
    result = basic_auth_post(api, username, auth_token, invalid_name_option_set_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400


def test_post_option_for_optionset(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, post_option_payload)
    newdata = json.loads(result.text)
    state['option_id'] = newdata['id']


def test_post_options_for_optionset_by_id(auth_token, url, username, state):
    post_options_for_optionset_payload.update({"option_id": state['option_id']})
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options' )
    result = basic_auth_post(api, username, auth_token, post_options_for_optionset_payload)
    newdata = json.loads(result.text)
    state['option_set_option_id'] = newdata['id']
    assert newdata['option_set_id'] == state['option_set_id']
    assert newdata['option_id'] == state['option_id']
    assert newdata['display_name'] == "automation appearance"
    assert newdata['is_required'] == True


def test_required_fields_for_optionset_by_id(auth_token, url, username, state):
    invalid_sort_order_payload.update({"option_id": state['option_id']})
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options' )
    result = basic_auth_post(api, username, auth_token, invalid_sort_order_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'sort_order' is invalid."
    assert newdata[0]['status'] == 400
    invalid_is_required_payload.update({"option_id": state['option_id']})
    result = basic_auth_post(api, username, auth_token, invalid_is_required_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'is_required' is invalid."
    assert newdata[0]['status'] == 400


def test_get_option_for_optionset_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options' )
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['option_id'] == state['option_id']
    assert newdata[0]['option_set_id'] == state['option_set_id']


def test_put_option_for_optionset_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options/' + str(state['option_set_option_id']) )
    result = basic_auth_put(api, username, auth_token, put_options_for_optionset_payload)
    newdata = json.loads(result.text)
    assert newdata['option_id'] == state['option_id']
    assert newdata['display_name'] == "Updated automation appearance"
    assert newdata['is_required'] == False
    assert newdata['sort_order'] == 1


def test_get_optionset_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) )
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['option_set_id']
    assert newdata['name'] == OPTION_SET_NAME


def test_put_optionset(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) )
    result = basic_auth_put(api, username, auth_token, put_option_set_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['option_set_id']
    assert newdata['name'] == UPDATE_OPTION_SET_NAME


def test_count_optionset(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_delete_a_option_in_optionset(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options/' + str(state['option_set_option_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_delete_all_options_in_optionset(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']) + '/options' )
    post_options_for_delete_all_scenario_payload.update({"option_id": state['option_id']})
    basic_auth_post(api, username, auth_token, post_options_for_delete_all_scenario_payload)
    basic_auth_delete(api, username, auth_token)
    api = urlparse.urljoin(url, 'api/v2/options/' + str(state['option_id']))
    basic_auth_delete(api, username, auth_token)


def test_delete_option_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload


def test_get_optionset_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets/')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_post_optionset_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets')
    result = basic_auth_post(api, username, auth_token, post_option_set_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['option_set_id_xml'] = newdata.find('id').text
    assert newdata.find('name').text == OPTION_SET_NAME


def test_required_optionset_fields_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets')
    result = basic_auth_post(api, username, auth_token, invalid_name_option_set_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"


def test_post_option_for_optionset_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/options')
    result = basic_auth_post(api, username, auth_token, post_option_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['option_id_xml'] = newdata.find('id').text


def test_post_options_for_optionset_by_id_xml_payload(auth_token, url, username, state):
    post_options_for_optionset_payload.update({"option_id": state['option_id_xml']})
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options' )
    result = basic_auth_post(api, username, auth_token, post_options_for_optionset_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['option_set_option_id_xml'] = newdata.find('id').text
    assert newdata.find('option_set_id').text == state['option_set_id_xml']
    assert newdata.find('option_id').text == state['option_id_xml']
    assert newdata.find('display_name').text == "automation appearance"
    assert newdata.find('is_required').text == "true"


def test_required_fields_for_optionset_by_id_xml_payload(auth_token, url, username, state):
    invalid_sort_order_payload.update({"option_id": state['option_id_xml']})
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options' )
    result = basic_auth_post(api, username, auth_token, invalid_sort_order_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'sort_order' is invalid."
    assert newdata[0].find('status').text == "400"
    invalid_is_required_payload.update({"option_id": state['option_id_xml']})
    result = basic_auth_post(api, username, auth_token, invalid_is_required_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'is_required' is invalid."
    assert newdata[0].find('status').text == "400"


def test_get_option_for_optionset_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options' )
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('option/option_id').text == state['option_id_xml']
    assert newdata.find('option/option_set_id').text == state['option_set_id_xml']


def test_put_option_for_optionset_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options/' + str(state['option_set_option_id_xml']) )
    result = basic_auth_put(api, username, auth_token, put_options_for_optionset_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('option_id').text == state['option_id_xml']
    assert newdata.find('display_name').text == "Updated automation appearance"
    assert newdata.find('is_required').text == "false"
    assert newdata.find('sort_order').text == "1"


def test_get_optionset_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) )
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['option_set_id_xml']
    assert newdata.find('name').text == OPTION_SET_NAME


def test_put_optionset_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) )
    result = basic_auth_put(api, username, auth_token, put_option_set_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['option_set_id_xml']
    assert newdata.find('name').text == UPDATE_OPTION_SET_NAME


def test_count_optionset_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/optionsets/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_delete_a_option_in_optionset_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options/' + str(state['option_set_option_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')


def test_delete_all_options_in_optionset_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']) + '/options' )
    post_options_for_delete_all_scenario_payload.update({"option_id": state['option_id_xml']})
    basic_auth_post(api, username, auth_token, post_options_for_delete_all_scenario_payload, payload_format = 'xml')
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')


def test_delete_option_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/optionsets/' + str(state['option_set_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
