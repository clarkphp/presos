#!/usr/bin/env sh
# Test script for filter-input-file.php
# Usage:
#    chmod u+x test-find-php-extensions.sh
#    ./test-find-php-extensions.sh
#
# License: see php-compat-toolkit/LICENSE.md Apache-2.0
TEST_REPORT_DIR="$HOME/php-compat-toolkit"
TEST_TARGET_DIR="/www/zendsvr6/htdocs/foldername"
test ! -w "$TEST_REPORT_DIR" || { printf "Oops! %s DOES exist. Delete it!" "$TEST_REPORT_DIR"; exit 1; }
test ! -r "$TEST_TARGET_DIR" || { printf "Oops! %s DOES exist. Delete it!" "$TEST_TARGET_DIR"; exit 1; }

if ../find-php-extensions.sh | grep -F "Report directory $TEST_REPORT_DIR does not exist or is not writable"; then
  printf "$LINENO: %s\n" 'Pass'
else
  { printf "$LINENO: %s\n" 'Fail. Exiting...'; exit 1; }
fi
mkdir -p "$TEST_REPORT_DIR"

if ../find-php-extensions.sh | grep -F "Target directory $TEST_TARGET_DIR does not exist or is not readable"; then
  printf "$LINENO: %s\n" 'Pass'
else
  { printf "$LINENO: %s\n" 'Fail. Exiting...'; exit 1; }
fi

rm -rf "$TEST_REPORT_DIR"
