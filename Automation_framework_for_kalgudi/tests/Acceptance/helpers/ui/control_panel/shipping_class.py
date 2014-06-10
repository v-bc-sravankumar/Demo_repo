from lib.ui_lib import *
from fixtures.shipping import *


class ShippingClass(CommonMethods, ShippingData):


    def setup_store_location(self, browser, country, state, postcode):
        browser.find_element_by_link_text("Setup & Tools").click()
        browser.find_element_by_link_text('Shipping').click()
        if country not in self.get_dropdown_selected_value(browser, 'companycountry'):
            self.clear_field(browser, 'companyname')
            browser.find_element_by_id('companyname').send_keys('Automation Testing')
            self.clear_field(browser, 'companyaddress')
            browser.find_element_by_id('companyaddress').send_keys('George Street')
            self.clear_field(browser, 'companycity')
            browser.find_element_by_id('companycity').send_keys('Sydney')
            self.select_dropdown_value(browser, 'companycountry', country)
            time.sleep(1)
            self.select_dropdown_value(browser, 'companystate', state)
            self.clear_field(browser, 'companyzip')
            browser.find_element_by_id('companyzip').send_keys(postcode)
            try:
                browser.find_element_by_css_selector('.SaveButton').click()
            except WebDriverException as e:
                browser.find_element_by_xpath('//input[@value="Save"]').click()
                if "Click succeeded but Load Failed" in e.msg:
                    pass

            self.verify_and_assert_success_message(browser, "The modified shipping settings have been saved successfully.", ".alert-success")

    def get_quote_in_control_panel(self, browser, provider, country, state, postcode, expected_service, expected_value):
        browser.execute_script("$('tr tr:contains(" + provider + ") td .dropdown-trigger').last().click()")
        browser.find_element_by_link_text('Get Quote').click()
        time.sleep(1)
        if country not in self.get_dropdown_selected_value(browser, 'country'):
            self.select_dropdown_value(browser, 'country', country)
            time.sleep(1)

        if state not in self.get_dropdown_selected_value(browser, 'state'):
            self.select_dropdown_value(browser, 'state', state)

        browser.find_element_by_id('postcode').send_keys(postcode)
        browser.find_element_by_id('weight').send_keys('1')
        browser.find_element_by_id('width').send_keys('1')
        browser.find_element_by_id('length').send_keys('1')
        browser.find_element_by_id('height').send_keys('1')
        browser.find_element_by_id('GetQuote').click()

        # FedEx service goes down, in that case, we dont want our test to be failed.
        if provider == "FedEx":
            try:
                self.verify_and_assert_success_message(browser, expected_service, ".ShippingQuote")
                browser.find_element_by_css_selector('.modalClose').click()
            except TimeoutException:
                self.verify_and_assert_success_message(browser, "not connect to", ".ShippingQuote")
                browser.find_element_by_css_selector('.modalClose').click()
        else:
            self.verify_and_assert_success_message(browser, expected_service, ".ShippingQuote")
            self.verify_and_assert_success_message(browser, expected_value, ".ShippingQuote")
            browser.find_element_by_css_selector('.modalClose').click()

    def add_new_shipping_methods(self, browser):
        browser.find_element_by_id('tab1').click()
        browser.execute_script("$('tr:contains(\"International\") td .dropdown-trigger').last().click()")
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_link_text('Edit Methods').is_displayed() and s.find_element_by_link_text('Edit Methods'))
        browser.find_element_by_link_text('Edit Methods').click()
        self.disable_the_shipping_method(browser)
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_xpath('//input[@value="Add a Shipping Method..."]').is_displayed() and s.find_element_by_xpath('//input[@value="Add a Shipping Method..."]'))
        browser.find_element_by_xpath('//input[@value="Add a Shipping Method..."]').click()

    def setup_shipping_flat_rate_per_order(self, browser, url):
        newurl="/admin/index.php?ToDo=editShippingZone&zoneId=1"
        browser.get(urlparse.urljoin(url, newurl))

        self.wait_until_element_present('tab1', 'ID', browser).click()
        if not self.element_exists('//td[contains(., "Flat Rate Per Order")]', browser, 'XPATH'):
            browser.find_element_by_xpath('//input[@value="Add a Shipping Method..."]').click()
            browser.find_element_by_xpath('//span[text()="Flat Rate Per Order"]').click()
            element = self.wait_until_element_present("shipping_flatrate_shippingcost", 'ID')
            element.send_keys('10')
            browser.find_element_by_name('SubmitButton1').click()
            self.verify_and_assert_success_message(browser, "The shipping method has been created successfully.", ".alert-success")


    def get_quote_in_store_front(self, browser, url, country, state, postcode):
        browser.get(url)
        browser.find_element_by_link_text('HOME').click()
        browser.get(browser.current_url + 'donatello-brown-leather-handbag-with-shoulder-strap')
        browser.find_element_by_xpath('//input[contains(@src,"AddCartButton.gif")]').click()
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_link_text('View or edit your cart').is_displayed() and s.find_element_by_link_text('View or edit your cart'))
        browser.find_element_by_link_text('View or edit your cart').click()
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_css_selector('.EstimateShippingLink').is_displayed() and s.find_element_by_css_selector('.EstimateShippingLink'))
        # "The contents of your shopping cart have been updated." message displayed when quantity set to 1, but
        # Getting exception "Element not found in the cache - perhaps the page has changed since it was looked up"
        try:
            self.select_dropdown_value_by_css(browser, '.quantityInput', '1')
        except StaleElementReferenceException:
            pass
        browser.refresh()
        browser.find_element_by_css_selector('.EstimateShippingLink').click()
        self.select_dropdown_value(browser, 'shippingZoneCountry', country)
        time.sleep(1)
        self.select_dropdown_value(browser, 'shippingZoneState', state)
        browser.find_element_by_id('shippingZoneZip').clear()
        browser.find_element_by_id('shippingZoneZip').send_keys(postcode)
        browser.find_element_by_xpath('//input[@value="Estimate Shipping & Tax"]').click()


    def disable_the_shipping_method(self, browser):
        try:
            enabled_methods = browser.find_elements_by_xpath("//img[contains(@src,\"tick.gif\")]")
            count = 0
            for tick in enabled_methods:
                count = count + 1

            while count >= 0:
                browser.find_element_by_xpath("//img[contains(@src,\"tick.gif\")]").click()
                count = count - 1
        except Exception:
            pass

    def change_default_currency(self, browser, url, country, currencycode):
        browser.find_element_by_link_text('Home').click()
        browser.get(browser.current_url + '/index.php?ToDo=settingsEditCurrency&currencyId=1')
        if country not in self.get_dropdown_selected_value(browser, 'currencyorigin'):
            browser.find_element_by_id('currencyname').clear()
            browser.find_element_by_id('currencyname').send_keys(currencycode + '_Dollar')
            self.select_dropdown_value(browser, 'currencyorigin', country)
            browser.find_element_by_id('currencycode').clear()
            browser.find_element_by_id('currencycode').send_keys(currencycode)
            browser.find_element_by_xpath('//input[@value="Save"]').click()
            self.verify_and_assert_success_message(browser, "The selected currency has been updated successfully.", ".alert-success")


    def is_new_ui(self,browser):
        return self.element_exists('shippingContainer', browser, 'ID')


    def navigate_to_shipping(self):
        self.find_element_by_link_text("Setup & Tools").click()
        self.find_element_by_link_text('Shipping').click()
        self.skip_shipping_intro()


    def skip_shipping_intro(self):
        try:
            self.wait_until_element_present('.shipping-tutorial-tooltip', "CSS_SELECTOR")
            self.wait_until_element_present('.introjs-skipbutton', "CSS_SELECTOR").click()
        except:
            pass


    def setup_store_location_new(self, store_location):
        self.skip_shipping_intro()
        self.wait_until_element_present(".test-edit-address-btn", "CSS_SELECTOR").click()

        self.wait_until_element_present("edit-address-modal", "ID")
        for key in store_location:
            if key=="Country" or key=="State":
                self.find_element_by_css_selector(store_location['Country']['Element']).click()
                state_element=self.wait_until_element_present(store_location['State']['Element'], "CSS_SELECTOR")
                state_element.click()
            else:
                element=store_location[key]['Element']
                value=store_location[key]['Value']
                self.find_element_by_css_selector(element).clear()
                self.find_element_by_css_selector(element).send_keys(value)
        #Press save button
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
        #self.wait_until_element_present("address-sidebar", "CLASS_NAME")
        #self.wait_until_element_present("ng-binding", "CLASS_NAME")
        self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
        address=str(self.wait_until_element_present("address-sidebar", 'CLASS_NAME').text)
        for key in store_location:
            assert store_location[key]['Value'] in address

    def add_country_zone(self, country_zone):
        self.skip_shipping_intro()
        if self.is_country_zone_present(country_zone):
            return
        self.find_element_by_css_selector('.test-addcountryzone-btn').click()
        self.wait_until_element_present('add-country-zone-modal', 'ID')
        # select country
        self.find_element_by_css_selector(country_zone['Country']['Element']).click()
        # save
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()

    def is_country_zone_present(self, country_zone):

        self.find_element_by_css_selector('tr.ng-scope')
        self.wait_until_element_present('tr.ng-scope', 'CSS_SELECTOR')
        zone_list = self.find_elements_by_css_selector('tr.ng-scope')
        for zone in zone_list:
            try:
                assert country_zone['Country']['Value'] in zone.text
                return True
            except:
                pass
        return False

    def zone_row(self, country_zone):
        row = self.find_element_by_xpath("//table[contains(@class, 'shipping-to')]/descendant::tbody/tr[contains(.,'"+country_zone+"')][last()]")
        return row

    def shipping_row(self, name):
        row = self.wait_until_element_present("//tr[contains(.,'"+name+"')]", "XPATH")
        return row

    def open_country_zone(self, country_zone):
        self.skip_shipping_intro()
        try:
            try:
                element=self.zone_row(country_zone)
                element.find_element_by_css_selector('button.test-setupzone-btn').click()
            except:
                element.find_element_by_css_selector('a.test-editzone-btn').click()

        except NoSuchElementException:
            return False

    def open_any_shipping_method(self, name):
        try:
            element=self.shipping_row(name)
            try:
                element.find_element_by_css_selector('button').click()
            except:
                try:
                    self.execute_script("$('tr:contains(\""+name+"\")>td .toggle-on-off input').click()")
                    self.wait_until_element_present('.dialog .dialog-header', 'CSS_SELECTOR')
                except:
                    self.execute_script("$('tr:contains(\""+name+"\")>td .toggle-on-off input').click()")
                    self.wait_until_element_present('.dialog .dialog-header', 'CSS_SELECTOR')
        except NoSuchElementException:
            return False
        pass

    def setup_flat_rate(self, method_name):
        self.open_any_shipping_method( "Flat Rate")
        # enter shipping method details
        for item in method_name:
            if item=="Option":
                self.find_element_by_css_selector(method_name['Option']['Element']).click()
            else:
                element=method_name[item]['Element']
                value=method_name[item]['Value']
                self.find_element_by_css_selector(element).clear()
                self.find_element_by_css_selector(element).send_keys(value)
        #press save btn
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
        self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
        return self.shipping_row(method_name['DisplayName']['Value'])

    def setup_free_shipping(self):
        self.open_any_shipping_method( "Free shipping")
        #press save btn
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
        self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
        return self.shipping_row('Free shipping')


    def setup_fedex(self):
        self.open_any_shipping_method( "FedEx")
        #click Connection tab
        self.find_element_by_link_text("Connection").click()
        # enter shipping method details
        for item in self.fedex:
            if item=="Select":
                for subitem in self.fedex[item]:
                    self.find_element_by_css_selector(self.fedex['Select'][subitem]).click()
            else:
                element=self.fedex[item]['Element']
                value=self.fedex[item]['Value']
                element = self.find_element_by_css_selector(element)
                element.clear()
                element.send_keys(value)
        #press save btn
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
        self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')

        return self.shipping_row('FedEx')

    def setup_australia_post(self):

        try:
            self.open_any_shipping_method( "Australia Post")
            # enter shipping method details
            for item in self.au_post:
                if item=="Select":
                        for subitem in self.au_post[item]:
                            if subitem=="ServiceTypeAll":
                                self.find_element_by_link_text("Settings").click()
                                self.find_element_by_css_selector(self.au_post['Select'][subitem]).click()
                            else:
                                self.find_element_by_link_text("Connection").click()
                                self.find_element_by_css_selector(self.au_post['Select'][subitem]).click()
                else:
                        self.find_element_by_link_text("Connection").click()
                        element=self.au_post[item]['Element']
                        value=self.au_post[item]['Value']
                        self.find_element_by_css_selector(element).clear()
                        self.find_element_by_css_selector(element).send_keys(value)
            #press save btn
            self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
            self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
            return self.shipping_row('Australia Post')
        except UnexpectedAlertPresentException:
            raise UnexpectedAlertPresentException()


    def setup_canada_post(self):
        self.open_any_shipping_method("Canada Post")

        try:
            element=self.wait_until_element_present("//button[@ng-disabled='!readyForSubmit']", time=30)
            element.click()
            try:
                self.find_element_by_css_selector("#oo_invitation_prompt a#oo_no_thanks").click()
            except:
                self.find('input[id$="username"]').send_keys('bcapptest')
                self.find('input[id$="password"]').send_keys('ftw@876')
                self.find('input[id$="signIn"]').click()
                self.wait_until_element_present("#dashboardTabs")
                self.execute_script("$('input[value=\"creditCard\"]').click()")
                self.execute_script("$('input[alt=\"Continue\"]').click()")
                self.wait_until_element_present("#agreeToPlatform")
                self.find_element_by_xpath("//img[@alt='continue']").click()
                self.wait_until_element_present("#shippingContainer")
                self.execute_script("$('.multi-select-toolbar a:contains(\"All\")')[0].click()")
                self.execute_script("$('.multi-select-toolbar a:contains(\"All\")')[1].click()")
                #press save btn
                self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
                return self.shipping_row('Canada Post')
        except:
            self.execute_script("$('.multi-select-toolbar a:contains(\"All\")')[0].click()")
            self.execute_script("$('.multi-select-toolbar a:contains(\"All\")')[1].click()")
            #press save btn
            self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
            self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
            return self.shipping_row('Canada Post')

    def setup_usps(self):
        self.open_any_shipping_method("USPS")
        #click Connection tab

        self.find_element_by_link_text("Connection").click()
        # enter shipping method details
        for item in self.usps:
                if item=="Select":
                    for subitem in self.usps[item]:
                            self.find_element_by_css_selector(self.usps['Select'][subitem]).click()
                else:
                    element=self.usps[item]['Element']
                    value=self.usps[item]['Value']
                    self.find_element_by_css_selector(element).clear()
                    self.find_element_by_css_selector(element).send_keys(value)
        #press save btn
        self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
        self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
        return self.shipping_row('USPS')


    def setup_royal_mail(self):

        try:
            self.open_any_shipping_method("Royal Mail")
            #click Connection tab
            self.find_element_by_link_text("Connection").click()
            for item in self.royal_mail:
                self.find_element_by_css_selector(self.royal_mail[item]).click()
            #press save btn
            self.find_element_by_css_selector(".dialog-actions button.btn-primary").click()
            self.wait_until_element_invisible('dialog-header', 'CLASS_NAME')
        except NoSuchElementException:
            return False
        except UnexpectedAlertPresentException:
            raise UnexpectedAlertPresentException()
        return self.shipping_row('Royal Mail')


class ShippingApi():

    def post_store_location(self, url, cookies, payload):
        apipath=urlparse.urljoin(url, '/admin/shipping/address')
        result = requests.post(apipath, data=json.dumps(payload), headers={'Accept': 'application/json'}, cookies=cookies, verify=False)
        assert result.status_code==200


    def post_shipping_zone(self, url,cookies, payload):
        apipath=urlparse.urljoin(url, '/admin/shipping/zones')
        result = requests.post(apipath, data=json.dumps(payload), headers={'Accept': 'application/json'}, cookies=cookies, verify=False)
        if result.status_code==409:
            item=result.json()
            assert 'A shipping zone already exists with this name' in item['error']
            result = requests.get(apipath, headers={'Accept': 'application/json'}, cookies=cookies, verify=False)
            subitem=result.json()
            for i in subitem:
                if i['name']==payload['name']:
                    print i['zoneid']
                    return i['zoneid']
        assert result.status_code==201
        item=result.json()
        assert item['name']==payload['name']
        assert item['free_shipping']==payload['free_shipping']
        return item['zoneid']


    def post_shipping_flat_rate__per_order_method(self, url, cookies, payload, zoneid):
        apipath=urlparse.urljoin(url, '/admin/shipping/zones/'+str(zoneid)+'/methods')
        result = requests.post(apipath, data=json.dumps(payload), headers={'Accept': 'application/json'}, cookies=cookies, verify=False)
        if result.status_code==409:
            item=result.json()
            assert 'already exists with this name' in item['error']
            return
        assert result.status_code==201
        item=result.json()
        assert item['name']==payload['name']
