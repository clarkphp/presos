#!/usr/bin/env bash
# License: see php-compat-toolkit/LICENSE.md Apache-2.0

# Identify all file extensions that appear to have PHP source code in them
# Files with these extension might NOT have PHP source in them, but this
# script provides a candidate list

# Edit and run this script first
# Usage:
#    chmod u+x find-php-extensions.sh
#    ./find-php-extensions.sh
REPORT_DIR="$HOME/php-compat-toolkit" # /Absolute/path/to/report/output/directory
OUTPUT_RPT="file-extensions-with-php.txt"
TARGET_DIR="/www/zendsvr6/htdocs/foldername" # /path/to/directory/tree/to/scan

cd $TARGET_DIR || { printf "Cannot change to %s\n" "$TARGET_DIR"; exit 1; }

/QOpenSys/pkgs/bin/grep --null -E -l --recursive --exclude-dir=.svn '<\?php|<\?=' . \
  | grep -v '^Binary file' | xargs -0 basename -a | awk -F. '{print $NF}' \
  | sort | uniq > "$REPORT_DIR/$OUTPUT_RPT"
