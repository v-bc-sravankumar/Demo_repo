#!/bin/bash
set -e # Explode on error

currentdate=`date +%s%d%m%y`
DOMAIN="rsuiteseqtest$currentdate.staging.bigcommerce.net"
RESPONSE=$(echo "<?xml version=\"1.0\"?>
<request>
  <provision_new_store><![CDATA[1]]></provision_new_store>
  <firstname><![CDATA[User]]></firstname>
  <lastname><![CDATA[Name]]></lastname>
  <companyname><![CDATA[Organisation Name]]></companyname>
  <domain><![CDATA[$DOMAIN]]></domain>
  <phone><![CDATA[123456790]]></phone>
  <email><![CDATA[test.engineer+$BAMBOO_BUILD@bigcommerce.com]]></email>
  <password><![CDATA[P@ssw0rd]]></password>
  <country><![CDATA[US]]></country>
  <state><![CDATA[N/A]]></state>
  <newsletter><![CDATA[1]]></newsletter>
  <product><![CDATA[48]]></product>
</request>
" | curl -sS -X POST -H 'Content-type: text/xml' -d @- https://account-bigcommerce.interspire/free_trial.php)

if [[ -z "$RESPONSE" ]]; then
    # HTTP 200 with blank response. Explode.
    echo 'WHMCS responded to the free_trial.php provisioning request with a blank page!' >&2
    exit 99
fi

echo "$RESPONSE"

# Capture 1234 from <response><hosting_id>1234</hosting_id>...</response>
# We need this for checking deployment status below

# HACK: resorting to grep and sed; replace with xmllint below when it's installed on Bamboo (TECHOPS-10418).
# This is intentionally a two-part command; if the grep fails, the script will exit (thanks to "set -e" above).
HOSTING_CHUNK=$(echo "$RESPONSE" | grep -o '<hosting_id>[0-9][0-9]*<.hosting_id>')
HOSTING_ID=$(echo "$HOSTING_CHUNK"| sed -e 's/<hosting_id>\([0-9][0-9]*\)<.hosting_id>/\1/')
# TODO: Replace HOSTING_CHUNK and HOSTING_ID above with just the following.
#HOSTING_ID=$(echo "$RESPONSE" | xmllint --xpath '//response/hosting_id/text()' -)

if [[ -z "$HOSTING_ID" ]]; then
    echo "Couldn't derive hosting_id from returned XML!" >&2    
    exit 99
fi

# Use bc_check_deployment.php to check the deployment status
CHECK_URL=$(printf "https://account-bigcommerce.interspire/bc_check_deployment.php?hid=%d" "$HOSTING_ID")
TIMEOUT=240 # seconds, ie. 4 minutes
START=$(date '+%s')
while [[ $(expr $(date '+%s') - "$START") -lt "$TIMEOUT" ]]; do
  CHECK_RESPONSE=$(curl $CHECK_URL)
  case "$CHECK_RESPONSE" in
    error)
        echo "Provisioning for $DOMAIN ($HOSTING_ID) failed!" >&2
        exit 99
        ;;
    deployed)
        echo "Provisioning for $DOMAIN ($HOSTING_ID) completed." >&2
        exit 0 # Done; get out.
        ;;
    *)
        echo "Incomplete ('$CHECK_RESPONSE'), waiting 20 seconds..." >&2
        sleep 20
        ;;
  esac
done
echo "Timeout when provisioning $DOMAIN ($HOSTING_ID)! Failing build." >&2;
exit 99


set +e
  env SEQ_STORE_URL="https://${DOMAIN}" SEQ_EMAIL="$EMAIL" SEQ_PASSWORD="$PASSWORD"
 # ACCEPTANCE_RET=$?
set -e