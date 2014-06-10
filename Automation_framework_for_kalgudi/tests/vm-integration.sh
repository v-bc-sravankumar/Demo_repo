#!/bin/bash

if [[ $# -ne 0 || "$NO_SAMPLE_DATA" = "1" ]]; then
  group=""

  if [ "$NO_SAMPLE_DATA" = "1" ]; then
    group="--group nosample"
    echo -e "\nRunning $@ without sample data...\n"
  else
    echo -e "\nRunning $@ ...\n"
  fi

  sudo -E TEST_DB_PASS=magic php -dsuhosin.memory_limit=0 -dmemory_limit=-1 -ddisplay_errors=1 ../vendor/bin/phpunit $group --configuration integration.xml --stderr $@
else
  echo -e "\nRunning sample data dependent tests...\n"
  sudo -E TEST_DB_PASS=magic php -dsuhosin.memory_limit=0 -dmemory_limit=-1 -ddisplay_errors=1 ../vendor/bin/phpunit --configuration integration.xml --stderr

  echo -e "\nRunning sample data independent tests...\n"
  sudo -E NO_SAMPLE_DATA=1 TEST_DB_PASS=magic php -dsuhosin.memory_limit=0 -dmemory_limit=-1 -ddisplay_errors=1 ../vendor/bin/phpunit --group nosample --configuration integration.xml --stderr
fi
