from lib.api_lib import *

# ****************************************************************************
#                                 JSON Payload
# ****************************************************************************

def test_get_all_reduest_logs(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/requestlogs/')
    basic_auth_get(api, username, auth_token)
