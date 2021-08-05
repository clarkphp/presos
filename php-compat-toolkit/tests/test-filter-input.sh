#!/usr/bin/env sh
# Test script for filter-input-file.php
# Usage:
#    chmod u+x test-filter-input.sh
#    ./test-filter-input.sh
#
# License: see php-compat-toolkit/LICENSE.md Apache-2.0

cp ./fixture-input-test-7.1-20210803.out ./fixture-input-test-7.1-20210803.tmp
../filter-input-file.php fixture-input-test-7.1-20210803.tmp

if diff --brief ./fixture-output-test-7.1-20210803.out ./fixture-input-test-7.1-20210803.tmp; then
  printf "%s\n" 'Pass'
else
  printf "%s\n" 'Fail'
fi
rm ./fixture-input-test-7.1-20210803.tmp