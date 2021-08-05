#!/usr/bin/env php
<?php
/**
 * Filters out "bad" characters from PHPCompatibility report files
 * This is trivial with `sed`, but I'm reducing dependency on anything
 * external to PHP (some clients are unable to install open source packages).
 *
 * This REPLACES the input file.
 *
 * Invoked by php-compat-toolkit/summarise-compat-findings.sh
 *
 * @license php-compat-toolkit/LICENSE.md Apache-2.0
 */

if ($argc < 2) {
    exit('Please invoke with a filepath. E.g.,:' . PHP_EOL
        . basename(__FILE__) . ' /file/to/process.out' . PHP_EOL
    );
}

if (! file_exists($argv[1])) {
    exit("The file '$argv[1]' does not exist." . PHP_EOL
        . 'Please check the filepath.' . PHP_EOL);
}

$content = file_get_contents($argv[1]);
$content = str_replace(array("\e[1m", "\e[0m", "\e[31m", "\e[33m"), '', $content);
file_put_contents($argv[1], $content);
