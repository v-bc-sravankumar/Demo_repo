from headers import *
from includes import *

def generate_random_string(size = 10, chars = string.ascii_uppercase + string.digits):
    return ''.join(random.choice(chars) for x in range(size))

# Basic Authorisation
# Connection Error exceptions raised Intermittent, due to network issue.
# To overcome this issue, following methods will wait 2 seconds and try the same request again
def post_token_request(url, payload, payload_format):
    try:
        if payload_format == 'json':
            result = requests.post(url, headers = json_headers, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.post(url, headers = xml_headers, data = payload, verify = False)
    except requests.ConnectionError:
        time.sleep(2)
        if payload_format == 'json':
            result = requests.post(url, headers = json_headers, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.post(url, headers = xml_headers, data = payload, verify = False)
    return result

def post_request(url, auth, payload, payload_format):
    try:
        if payload_format == 'json':
            result = requests.post(url, headers = json_headers, auth = auth, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.post(url, headers = xml_headers, auth = auth, data = payload, verify = False)
    except requests.ConnectionError:
        time.sleep(2)
        if payload_format == 'json':
            result = requests.post(url, headers = json_headers, auth = auth, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.post(url, headers = xml_headers, auth = auth, data = payload, verify = False)

    return result

def put_request(url, auth, payload, payload_format):
    try:
        if payload_format == 'json':
            result = requests.put(url, headers = json_headers, auth = auth, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.put(url, headers = xml_headers, auth = auth, data = payload, verify = False)
    except requests.ConnectionError:
        time.sleep(2)
        if payload_format == 'json':
            result = requests.put(url, headers = json_headers, auth = auth, data = json.dumps(payload), verify = False)
        elif payload_format == 'xml':
            result = requests.put(url, headers = xml_headers, auth = auth, data = payload, verify = False)

    return result

def delete_request(url, headers, auth):
    try:
        result = requests.delete(url, headers = headers, auth = auth, verify = False)
    except requests.ConnectionError:
        time.sleep(2)
        result = requests.delete(url, headers = headers, auth = auth, verify = False)

    return result

def get_request(url, headers, auth):
    try:
        result = requests.get(url, headers = headers, auth = auth, verify = False)
    except requests.ConnectionError:
        time.sleep(2)
        result = requests.get(url, headers = headers, auth = auth, verify = False)
    return result


# Get the Token ID
def get_api_credentials(url, email, password):
    token_url = urlparse.urljoin(url, '/api/v2/token') # Replaces /the/path/of url with /api/v2/token
    payload = {"username": email, "password": password}
    result = post_token_request(token_url, payload, payload_format='json')
    json = result.json()
    return {
        "token":    json[0],
        "username": json[2],
    }

def auth_from_token(username, token):
    return HTTPBasicAuth(username, token)

def basic_auth_get(url, username, token, invalid_record = 0, payload_format = 'json'):
    auth = auth_from_token(username, token)
    if payload_format == 'json':
        headers = json_headers
    elif payload_format == 'xml':
        headers = xml_headers
    result = get_request(url, headers, auth)

    # Should get 200 status for every Get request,
    # However, verify 404 status returns when try to Get Deleted record
    if invalid_record == 0:
        assert result.status_code == 200
    else:
        assert result.status_code == 404
    return result


def basic_auth_post(url, username, token, payload, check_required_fields = 0, payload_format = 'json'):
    auth = auth_from_token(username, token)
    if payload_format == 'xml':
        payload = remove_elements_attribute(dicttoxml.dicttoxml(payload), 'type')
        print payload # TODO: Is this left-over debugging?
    result = post_request(url, auth, payload, payload_format)

    # Should get 201 status for every successful request,
    # However, verify 400 status returns when mandatory fields not supply to payload
    if check_required_fields == 0:
        assert result.status_code == 201
    else:
        assert result.status_code == 400
    return result


def basic_auth_put(url, username, token, payload, check_required_fields = 0, payload_format = 'json'):
    auth = auth_from_token(username, token)
    if payload_format == 'xml':
        payload = remove_elements_attribute(dicttoxml.dicttoxml(payload), 'type')
    result = put_request(url, auth, payload, payload_format)

    if check_required_fields == 0:
        assert result.status_code == 200
    else:
        assert result.status_code == 409
    return result


def basic_auth_delete(url, username, token, invalid_record = 0, payload_format = 'json'):
    auth = auth_from_token(username, token)
    if payload_format == 'json':
        headers = json_headers
    elif payload_format == 'xml':
        headers = xml_headers
    result = delete_request(url, headers, auth)
    assert result.status_code == 204
    return result

# Remove the attributes added by dicttoxml package, while converting json to xml payload
def remove_elements_attribute(xmlload, attribute_name):
    dom = minidom.parseString(xmlload)
    for parent in dom.childNodes:
        for child in parent.childNodes:
            child.removeAttribute(attribute_name)

    items = dom.getElementsByTagName('item')
    if items.length > 0:
        payload = dom.toxml()
        item = items[0]
        if len(item.childNodes) == 1:
            payload = re.sub(r'\bitem\b', r'value', dom.toxml())
    else:
        payload = dom.toxml()

    return payload
