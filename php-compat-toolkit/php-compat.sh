#!/usr/bin/env bash
# License: see php-compat-toolkit/LICENSE.md Apache-2.0

# Runs https://github.com/PHPCompatibility/PHPCompatibility
# sniffs over target project source code, to generate a report. Edit this
# script as needed prior to running, for filename extensions, report
# filename, target PHP version, etc.

# Assign each variable an appropriate value prior to running this script!
# This script assumes you're running it on the IFS in PASE, and assumes
# it is located in your home directory (/home/USERPROFILE). Edit accordingly
# if that is not the case.
# Usage:
#     chmod u+x php-compat.sh
#     ./php-compat.sh

TODAY="$(date +'%Y%m%d')"
ENCLOSING="/www/zendsvr6/htdocs"
REPORT_DIR="/home/$LOGNAME/php-compat-toolkit" # /path/to/your/desired/report/output/folder
PHP_MEM_LIMIT="1024M" # 1GB, you can almost always lower this
FILE_EXTENSIONS="html,inc,php,phtml"
IGNORE_FILES="*/vendor/*,*Copy*,*/folder2/*"
TARGET_PHP_VERSION=7.4
SRC_DIR=foldername # the name of the directory within $ENCLOSING to scan
RPT_FILEPATH="$REPORT_DIR/$SRC_DIR-$TARGET_PHP_VERSION-$TODAY.out"
SNIFFS="" # if you know phpcs and PHPCompatibility, a list of specific sniffs to use
RUN_PROGRESS=""
#RUN_PROGRESS="-p" # uncomment if you want to see dots printed as a progress meter
php_exe="/usr/local/zendsvr6/bin/php-cli" # path to your PHP binary (executable)

printf "Scanning for PHP compatibility findings in %s...\n" $SRC_DIR
$php_exe "/home/$LOGNAME/php-compat-toolkit/phpcs.phar" -d memory_limit="$PHP_MEM_LIMIT" --no-colors \
 --extensions=$FILE_EXTENSIONS "$RUN_PROGRESS" $ENCLOSING/$SRC_DIR \
 --ignore="$IGNORE_FILES" --report-full="$RPT_FILEPATH" \
 --standard=PHPCompatibility "$SNIFFS" --runtime-set testVersion $TARGET_PHP_VERSION

printf "See output report at %s\n" "$RPT_FILEPATH"
