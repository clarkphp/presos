# php-compat-toolkit

A set of scripts to facilitate scanning directory trees to generate PHP version
compatibility reports and summarize the findings. This is one of the tools I use
to gauge the size of a PHP version migration effort.

## Overview
- You need to:
  - know what files potentially contain PHP source code you need to migrate
  - run PHPCompatibility checks over your source code
  - know the number of PHP incompatibility findings (error level, warning level, number of files affected)
  - get an itemized list of the number of occurrences of each PHP compatibility finding

## The Workflow
1. Download archive or clone the repository
1. You'll need to edit some of these scripts. Whether you do that first, and then
   transfer them to the IFS, or transfer them first and edit in-place, is up
   to you.
1. Edit `find-php-extensions.sh`
   - This script identifies all file extensions that appear to have PHP source code in them. Files with these extensions might NOT have PHP source in them, but this script provides a candidate list.
   - REPORT_DIR is the absolute path to directory where your report output will be written
   - OUTPUT_RPT is the name of the file extensions report
   - TARGET_DIR is the absolute path to the directory subtree to look for files containing PHP
1. Make `find-php-extensions.sh` executable and run it.

       chmod u+x find-php-extensions.sh && ./find-php-extensions.sh

1. Read the output file for a candidate list of file extensions that _appear_ to contain PHP.
   Make your best judgment with this list.
1. Check `CodeSniffer.conf`. The initial value should work for you.
   - `installed_paths` is the absolute path to where the PHPCompatibility standard resides on disk. 
   - If you [install PHPCompatibility separately](https://github.com/PHPCompatibility/PHPCompatibility#installation-via-a-git-check-out-to-an-arbitrary-directory-method-2),
     (and you should, after initial use of this toolkit), you will need to update
     the `installed_paths` value.
1. Edit `php-compat.sh`
   - This script runs PHPCompatibility sniffs over the target project source code, to generate a compatibility report.
   - `ENCLOSING` the path to the folder that encloses your application folder
   - `REPORT_DIR` the path to where you want scan results to go
   - `PHP_MEM_LIMIT` the maximum anount of memory to let PHP have while scanning
   - `FILE_EXTENSIONS` comma-separated list of file extensions to scan. Base this
     upon the output from running `find-php-extensions.sh` above.
   - `IGNORE_FILES` which folders and file patterns scanning should ignore
   - `TARGET_PHP_VERSION` the version of PHP you plan to migrate to
   - `SRC_DIR` the name of the directory within $ENCLOSING to scan for compatibility
     findings. This can be "" if your app is entirely under $ENCLOSING.
   - `RPT_FILEPATH` you normally don't edit this; it is built up from the other variables
   - `SNIFFS` if you know phpcs and PHPCompatibility, a list of specific sniffs to use.
     For initial use, you can ignore it.
   - `RUN_PROGRESS` if set to "-p", a dot is printed for each file scanned, as a progress meter,
   - `$php_exe` absolute path to your PHP binary (executable). This allows the script
     to run even if you don't have PHP included in your command PATH environment variable.
     Or you can run using a different version of PHP.
1. Make `php-compat.sh` executable and run it.

       chmod u+x php-compat.sh && ./php-compat.sh

1. Edit `summarise-compat-findings.sh`
   - This script briefly summarises compatibility findings recorded in PHPCompatibility reports. It displays the total number of findings, the number of PHP source code files affected, and how many occurrences of each finding there are.
   - `REPORT_DIR` the path to where you want output to be written
   - `SCRIPTS_DIR` the path to where the `counts.php` file resides. You won't need
     to edit this value unless you move that file.
   - `$php_exe` absolute path to your PHP binary (executable). This allows the script
     to run even if you don't have PHP included in your command PATH environment variable.
     Or you can run using a different version of PHP.
   - In processing the compatibility report file(s), it produces three files of
     intermediate results, which you can review, if you wish:
     - raw-findings.txt
     - raw-counts.txt
     - raw-ext-findings.txt
1. Make `summarise-compat-findings.sh` executable and run it.

       chmod u+x summarise-compat-findings.sh && ./summarise-compat-findings.sh

   - You will see displayed on the screen a summary like this:
```
1: Number of incompatibility findings in your-report-name-7.4-20210621.out
Error-level  : 101
Warning-level: 223
Num Files    : 83
```     

   - Your per-finding detailed count of findings will be in a file called `compatibility-summary-YYMMDD.txt`
     and looks like the example below. If yours is this clean, consider yourself lucky!

```
                            Number of Occurrences : PHP Compatibility Finding
---------------------------------------------------------------------------------------------------------
  240 : File has mixed line endings; this may cause incorrect results
    2 : INI directive 'highlight.bg' is deprecated since PHP 5.3 and removed since PHP 5.4
    1 : 'resource' is a soft reserved keyword as of PHP version 7.0 and should not be used to name a class, interface or trait or as part of a
    1 : 'float' is a reserved keyword as of PHP version 7.0 and should not be used to name a class, interface or trait or as part of a namespace

----------------------------------------------------------------------------------------------------------
Total Findings:  244
```