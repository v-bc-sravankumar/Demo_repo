# HMAC key credentials
credentials = {
   'id': 'api_proxy',
   'key': 'dont use this in production',
   'algorithm': 'sha256'
}

# Header for the JSON format API request
json_headers = {'content-type': 'application/json',
            'Accept': 'application/json'
                }

# Header for the XML format API request
xml_headers = {'content-type': 'application/xml',
                'Accept': 'application/xml'
                }
