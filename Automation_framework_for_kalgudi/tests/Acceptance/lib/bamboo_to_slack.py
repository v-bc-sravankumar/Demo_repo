__author__ = 'shobhit.nautiyal'


import sys
import requests
import json

buildName = str(sys.argv[1])
jobName = str(sys.argv[2])
buildUrl = str(sys.argv[3])

buildStateUrl = 'http://bamboo.bigcommerce.net/bamboo/rest/api/latest/result/{0}/latest.json?buildstate'.format(buildName)
r = requests.get(buildStateUrl)
buildState = r.json()['buildState']

if not buildState is 'Successful':
  message = "Job \"{0}\" not successful! State: {1}. See {2}".format(jobName, buildState, buildUrl)
  payload = {'channel': '#bamboo_slack_shobhit', 'username': 'Bamboo', 'text': message}
  r = requests.post('https://bigcommerce.slack.com/services/hooks/incoming-webhook?token=mN3DWJCKf4a1rUzf6UtDmJ8X', data=json.dumps(payload))
  print "Slacker output: {0}".format(r.text)
