import os
from lib.api_lib import *

email = os.environ['EMAIL']
password = os.environ['PASSWORD']
url = os.environ['STORE_URL']

credentials = get_api_credentials(url, email, password)
print "%s\n%s" % (credentials['username'], credentials['token'])
