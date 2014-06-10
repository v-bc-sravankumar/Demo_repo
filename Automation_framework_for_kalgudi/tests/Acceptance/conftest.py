import pytest
import os
import os.path as path
import time
import datetime
import shutilwhich
import shutil
from selenium import webdriver
from furl import furl
from lib.api_lib import get_api_credentials


BROWSER = {
    'chrome': webdriver.Chrome,
    'firefox': webdriver.Firefox,
    'headless': lambda: webdriver.PhantomJS(
        desired_capabilities = {'javascriptEnabled': True},
        executable_path = phantomjs_path(),
        service_args = ["--ignore-ssl-errors=yes"]
    ),
    'remote': lambda: webdriver.Remote(
        desired_capabilities = get_desired_capabilities(),
        command_executor = get_remote_command_executor()
    )
}


def get_desired_capabilities():
    """Provides browser, os, version details that come from environment variables."""

    if str.lower(os.environ['REMOTE_HOST']) == 'browserstack':
        desired_capabilities={
            'browser': os.environ['REMOTE_BROWSER'],
            'browser_version': os.environ['REMOTE_BROWSER_VERSION'],
            'os': os.environ['REMOTE_OS'],
            'os_version': os.environ['REMOTE_OS_VERSION'],
            'browserstack.tunnel': True,
            'acceptSslCerts': True
        }
    else:
        desired_capabilities={
            'browserName': os.environ['REMOTE_BROWSER'],
            'os': os.environ['REMOTE_OS'],
            'version': os.environ['REMOTE_BROWSER_VERSION'],
            'name': "Testing Bigcommerce app on Selenium 2 in Python at Sauce"
        }
    return desired_capabilities


def get_remote_command_executor():
    """Provides command executor for appropriate cloud test environment provide. E.g. Browserstack, Saucelabs."""

    if str.lower(os.environ['REMOTE_HOST']) == 'browserstack':
        domain = 'hub.browserstack.com'
    else:
        domain = 'ondemand.saucelabs.com'

    command_executor = furl('http://' + domain + ':80/wd/hub').set(
        username = os.environ['REMOTE_USERNAME'],
        password = os.environ['REMOTE_TOKEN'],
    ).url
    return command_executor


def phantomjs_path():
    """
    Seeks out a PhantomJS install to use. Why not just use the one from NPM (eg. node_modules/...)?
    The NPM package only installs the phantomjs binary if it can't find it in the $PATH; this goes
    looking for the NPM version, then tries $PATH as a fallback.
    """
    phantomjs = path.realpath(path.join(
        path.dirname(__file__),
        '../../node_modules/phantomjs/lib/phantom/bin/phantomjs'
    ))
    if not path.exists(phantomjs):
        phantomjs = shutil.which('phantomjs') # Probably /usr/local/bin/phantomjs, if it exists.
    if phantomjs is None or not path.exists(phantomjs):
        raise EnvironmentError("PhantomJS binary not found")
    if not os.access(phantomjs, os.X_OK):
        raise EnvironmentError("PhantomJS binary is not executable: %s" % phantomjs)
    return phantomjs


def pytest_addoption(parser):
    parser.addoption('--url', action='store')
    parser.addoption('--browser', action='store', default='headless')
    parser.addoption('--username', action='store')
    parser.addoption('--password', action='store')
    parser.addoption('--auth_client', action='store')
    parser.addoption('--auth_token', action='store')
    parser.addoption('--store_hash', action='store')
    parser.addoption('--implicit_wait', action='store')
    parser.addoption('--email', action='store')

@pytest.fixture(scope="module")
def state():
    return dict()

@pytest.fixture(scope="session")
def url(request):
    return request.config.getoption('url')

@pytest.fixture(scope="session")
def username(request):
    return request.config.getoption('username')

@pytest.fixture(scope="session")
def password(request):
    return request.config.getoption('password')

@pytest.fixture(scope="session")
def browser(request):
    __browser = request.config.getoption('browser')
    driver = BROWSER[__browser]()
    driver.set_window_size(1600, 900)
    implicit_wait = request.config.getoption('implicit_wait')
    if implicit_wait is None:
        implicit_wait = 0
    else:
        implicit_wait = int(implicit_wait)
    driver.implicitly_wait(implicit_wait)
    request.addfinalizer(lambda: driver.quit())
    return driver

# Proxy Api test needs to parameterized auth token & auth client
@pytest.fixture(scope="session")
def auth_client(request):
    return request.config.getoption('auth_client')

@pytest.fixture(scope="session")
def auth_token(request):
    api_token = request.config.getoption('auth_token')
    if api_token is None:
        store_url = request.config.getoption('url')
        store_email = request.config.getoption('email')
        store_password = request.config.getoption('password')
        api_credentials = get_api_credentials(store_url, store_email, store_password)
        api_token = api_credentials['token']
    return api_token

@pytest.fixture(scope="session")
def store_hash(request):
    return request.config.getoption('store_hash')

@pytest.fixture(scope="session")
def email(request):
    return request.config.getoption('email')

@pytest.mark.tryfirst
def pytest_runtest_makereport(item, call, __multicall__):

    # execute all other hooks to obtain the report object
    rep = __multicall__.execute()
    now = datetime.datetime.now()
    screenshots_path = path.realpath(path.join(
      path.dirname(__file__),
      'screenshot_on_failure'
    ))
    if not os.path.exists(screenshots_path):
        os.makedirs(screenshots_path)
    if rep.failed:
        if "browser" in item.funcargs:
            try:
                item.funcargs['browser'].save_screenshot(screenshots_path + '/' + now.strftime("%Y%m%dT%H%M%S") + '_' + item.funcargs['request'].function.func_name + '.png')
            except:
                pass
    return rep
