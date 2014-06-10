from lib.ui_lib import *

class FYClass(CommonMethods):
	
	def create_fisicalyear(self,browser):	    		
		browser.find_element_by_xpath('//i[@title="myAccounts"]').click()
		self.wait_until_element_present('config', 'ID')		
		browser.find_element_by_id('config').click()
		self.wait_until_element_present('fiscalyear', 'ID')
		browser.find_element_by_id('fiscalyear').click()		
		browser.find_element_by_id('creatfiscalaccbtn').click()
		self.wait_until_element_present('fiscalyearname', 'ID')
		browser.find_element_by_id('fiscalyearname').clear()
		browser.find_element_by_id('fiscalyearname').send_keys("2014-2015")
		browser.find_element_by_id('fiscalyearcode').clear()
		browser.find_element_by_id('fiscalyearcode').send_keys('01')
		browser.find_element_by_id('savefiscalyear').click()