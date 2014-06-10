#!/bin/bash

echo -e "Running unit tests...\n"
./vm-unit.sh

echo -e "\nRunning integration tests...\n"
./vm-integration.sh
