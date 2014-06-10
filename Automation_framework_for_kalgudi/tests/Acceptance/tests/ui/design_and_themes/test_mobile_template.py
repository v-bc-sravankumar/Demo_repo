from lib.ui_lib import *

Safari_IOS7 = "Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) CriOS/30.0.1599.12 Mobile/11A465 Safari/8536.25 (3B92C18B-D9DE-4CB7-A02A-22FD2AF17C8F)"
Android_403 = "Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30"
Android_Nexux_7 = "Mozilla/5.0 (Linux; Android 4.4; Nexus 7 Build/KOT24) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.105 Safari/537.36"
BlackBerry_9900 = "Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+"

def load_mobile_template(url, dcap):
        """Loads mobile template and checks for page load errors or page blank"""
        cap = dict(DesiredCapabilities.PHANTOMJS)
        cap["phantomjs.page.settings.userAgent"] = (dcap)
        driver = webdriver.PhantomJS(desired_capabilities=cap)
        driver.get(url)
        log=driver.get_log('har')
        if log is not None:
            for i in log:

                if 'about:blank'in i['message']:
                    driver.close()
                    index=i['message'].find('about:blank')
                    raise Exception('Blank page is loaded:'+i['message'][index-1:])
                elif 'Error' in i['message']:
                    driver.close()
                    index=i['message'].find('Error')
                    raise Exception('Error on page load: '+i['message'][index-1:])


def test_Safari_IOS7(url):
    pytest.skip("Skipping due to flakiness on Bamboo")
    load_mobile_template(url, Safari_IOS7)


def test_Android_403(url):
    pytest.skip("Skipping due to flakiness on Bamboo")
    load_mobile_template(url, Android_403)


def test_Android_Nexux_7(url):
    pytest.skip("Skipping due to flakiness on Bamboo")
    load_mobile_template(url, Android_Nexux_7)


def test_BlackBerry_9900(url):
    pytest.skip("Skipping due to flakiness on Bamboo")
    load_mobile_template(url, BlackBerry_9900)