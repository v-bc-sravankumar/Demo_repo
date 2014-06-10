import time
import datetime
import urlparse
import json
import sys
import re
import random
import string
import hawk
import dicttoxml
import xml.etree.ElementTree as etree
import dateutil.parser as parser
import urllib
import csv
import os
import pytest
from httplib import BadStatusLine
import requests
from requests.auth import HTTPBasicAuth
from selenium.webdriver.common.keys import Keys
from selenium.common.exceptions import NoSuchElementException, ElementNotVisibleException, WebDriverException, StaleElementReferenceException, TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from faker import Factory
from selenium.webdriver.common.action_chains import ActionChains
from random import randint
from xml.dom import minidom
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
