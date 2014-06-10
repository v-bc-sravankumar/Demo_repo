from lib.ui_lib import *

class AccClass(CommonMethods):
	
	def create_BankAccount(self,browser):
		browser.find_element_by_xpath('//i[@title="myAccounts"]').click()
		self.wait_until_element_present('config', 'ID')		
		browser.find_element_by_id('config').click()
		self.wait_until_element_present('accounts', 'ID')		
		browser.find_element_by_id('accounts').click()
		self.wait_until_element_present('createbankaccbtn', 'ID')
		browser.find_element_by_id('createbankaccbtn').click()		
		self.wait_until_element_present('acnum', 'ID')
		browser.find_element_by_id('acnum').clear()
		browser.find_element_by_id('acnum').send_keys("12345678")
		browser.find_element_by_id('actowner').clear()
		browser.find_element_by_id('actowner').send_keys("Vasudhaika")
		browser.find_element_by_id('bankname').clear()
		browser.find_element_by_id('bankname').send_keys("Axis Bank")
		browser.find_element_by_id('ifsc').clear()
		browser.find_element_by_id('ifsc').send_keys("12345")
		browser.find_element_by_id('branchName').clear()
		browser.find_element_by_id('branchName').send_keys("Madhapur")
		browser.find_element_by_id('openbal').clear()
		browser.find_element_by_id('openbal').send_keys("1000")
		browser.find_element_by_id('savebankaccount').click()