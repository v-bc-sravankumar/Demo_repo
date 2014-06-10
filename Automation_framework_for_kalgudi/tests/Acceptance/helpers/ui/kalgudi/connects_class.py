from lib.ui_lib import *

class ConnectsBuyClass(CommonMethods):

	def create_SendBuy(self,browser):		
		browser.find_element_by_xpath('//i[@title="myConnects"]').click()
		self.wait_until_element_present('composeMessageBtn', 'ID')
		browser.find_element_by_id('composeMessageBtn').click()
		self.wait_until_element_present('sendToComboType', 'ID')		
		browser.find_element_by_id('sendToComboType').click()
		browser.find_element_by_xpath('//li[@data-userid="connects_1"]').click()
		browser.find_element_by_xpath('//input[@value="2"]').click()
		browser.find_element_by_id('messageCommoditySelect').click()
		time.sleep(15)
		browser.find_element_by_id('messageCommoditySelect').click()
		select = Select(browser.find_element_by_id('messageCommoditySelect'))
		select.select_by_value("52775c04e4b0f0f19d377f9d")		
		browser.find_element_by_id('messageSubjectText').send_keys("Sell Rice")
		browser.find_element_by_id('messageBodyArea').send_keys("HI, I have 100 QTY of Rice, If any one is required,please contact me.")
		browser.find_element_by_id('sendPostSubmitBtn').click()		
		#assert "Your message sent successfully" in browser.find_element_by_id('messageSentSuccessNotification').text                                                           