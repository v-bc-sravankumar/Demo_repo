#!/bin/bash

sudo php -dsuhosin.memory_limit=0 -dmemory_limit=-1 ../vendor/bin/phpunit --configuration unit.xml $@
