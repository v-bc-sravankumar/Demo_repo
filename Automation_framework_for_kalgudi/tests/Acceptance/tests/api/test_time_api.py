from lib.api_lib import *

# JSON Payload

def test_get_time(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/time')
    r = basic_auth_get(api, username, auth_token)
    newdata = json.loads(r.text)
    assert r.status_code == 200
    assert newdata['time'] is not None
