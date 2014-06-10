from lib.api_lib import *

# JSON Payload

def test_get_store_info(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/store')
    basic_auth_get(api, username, auth_token)
