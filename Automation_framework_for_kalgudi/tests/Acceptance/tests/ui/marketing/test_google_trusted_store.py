from lib.ui_lib import *


@pytest.mark.skipif("True")
def test_upgrade_plan(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    common.upgrade_staging_plan(browser)
    
@pytest.mark.skipif("True")
def test_gts_badge_code_is_present(browser, url, email, password):
    gts_account_id = str(randint(100, 999))
    gts_mc_account_id = str(randint(100, 999))
    gts_est_shipping_days = str(randint(1, 9))
    store_front_pages = ['account.php', 'checkout.php', 'giftcertificates.php', 'login.php', 'login.php?action=create_account', 'rss-syndication', 'shipping-returns']
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    common.enable_google_trusted_store(browser, url, gts_account_id, gts_mc_account_id, gts_est_shipping_days)
    keywords = ["gts.push([\"id\", \""+gts_account_id+"\"]);"]
    common.search_page_source_with_keywords(browser, url, keywords)

    for each_page in store_front_pages:
        common.search_page_source_with_keywords(browser, urlparse.urljoin(url, each_page), keywords)


def test_gts_badge_code_is_absent(browser, url, email, password):
    common = CommonMethods(browser)
    gts_account_id = str(randint(100, 999))
    gts_mc_account_id = str(randint(100, 999))
    gts_est_shipping_days = str(randint(1, 9))
    store_front_pages = ['account.php', 'checkout.php', 'giftcertificates.php', 'login.php', 'login.php?action=create_account', 'rss-syndication', 'shipping-returns']
    common.go_to_admin(browser, url, email, password)
    keywords = ["gts.push([\"id\", \""+gts_mc_account_id+"\"]);"]
    common.search_page_source_keywords_absent(browser, url, keywords)

    for each_page in store_front_pages:
        common.search_page_source_keywords_absent(browser, urlparse.urljoin(url, each_page), keywords)

@pytest.mark.skipif("True")
def test_gts_shipment_feed(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    download_url, a = common.enable_google_trusted_store(browser, url)
    download_loc = urllib.urlretrieve(download_url)[0]
    col_names = ['merchant order id', 'tracking number', 'carrier code', 'other carrier name', 'ship date']

    with open(download_loc) as tsv:
        reader = csv.DictReader(tsv, delimiter="\t")
        assert reader.fieldnames == col_names

@pytest.mark.skipif("True")
def test_gts_cancellation_feed(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    a, download_url = common.enable_google_trusted_store(browser, url)
    download_loc = urllib.urlretrieve(download_url)[0]
    col_names = ['merchant order id', 'reason']

    with open(download_loc) as tsv:
        reader = csv.DictReader(tsv, delimiter="\t")
        assert reader.fieldnames == col_names
