#!/usr/bin/env php
<?php
/**
 * Generates per-finding counts of PHPCompatibility report findings.
 * Invoked by php-compat-toolkit/summarise-compat-findings.sh
 *
 * @license php-compat-toolkit/LICENSE.md Apache-2.0
 */

if ($argc < 2) {
    exit('Please invoke with a filepath. E.g.,:' . PHP_EOL
        . basename(__FILE__) . ' /file/to/process.txt' . PHP_EOL
    );
}

$matches = [];
$findings = [];
$sum = 0;

/**
 * PHPCompatibility output includes occasional "near identical" finding text.
 * To sum the findings by type, it's necessary to map variations to the same text.
 *
 * This is a de-duplication mapping of indexes of the text of "findings". If the key is
 * found, use the value instead. Value is the version that appears most often,
 * or is otherwise preferable.
 *
 * To add to this list, check the output of `raw-counts.txt`.
 */
$dup_map = [
    'Function split() is deprecated since PHP 5.3 and removed' => 'Function split() is deprecated since PHP 5.3 and',
    'Global variable \'$HTTP_POST_FILES\' is deprecated since' => 'Global variable \'$HTTP_POST_FILES\' is deprecated',
    'Global variable \'$HTTP_POST_VARS\' is deprecated since' => 'Global variable \'$HTTP_POST_VARS\' is deprecated',
    'Global variable \'$HTTP_SERVER_VARS\' is deprecated since' => 'Global variable \'$HTTP_SERVER_VARS\' is deprecated',
    'INI directive \'safe_mode\' is deprecated since PHP 5.3' => 'INI directive \'safe_mode\' is deprecated since PHP',
    'Since PHP 7.0, functions inspecting arguments, like' => 'Since PHP 7.0, functions inspecting arguments,',
    'The __toString() magic method will no longer accept' => 'The __toString() magic method can no longer accept',
    'Use of deprecated PHP4 style class constructor is not' => 'Use of deprecated PHP4 style class constructor',
    'Use of deprecated PHP4 style class constructor is' => 'Use of deprecated PHP4 style class constructor',
    'preg_replace() - /e modifier is deprecated since PHP' => 'preg_replace() - /e modifier is deprecated since',
];

print "Processing file $argv[1]" . PHP_EOL;
$lines = file($argv[1]);

foreach ($lines as $line) {
    preg_match('/ (.+) (\d+)$/', $line, $matches);
    $index = ltrim($matches[1], ' []x');
    if (isset($dup_map[$index])) {
        $index = $dup_map[$index];
    }

    if (! isset($findings[$index])) {
        $findings[$index] = 0;
    }
    $findings[$index] += $matches[2];
    $sum += $matches[2];
}

arsort($findings, SORT_NUMERIC);

const LEN_FINDING_COL = 55;
const LEN_COUNT_COL = 23;
print str_pad('PHP Compatibility Finding', LEN_FINDING_COL, ' ', STR_PAD_BOTH)
    . ': ' . 'Number of Occurrences' . PHP_EOL
    . str_repeat('-', LEN_FINDING_COL + LEN_COUNT_COL) . PHP_EOL;
foreach ($findings as $finding => $count) {
    print str_pad($finding, LEN_FINDING_COL) . ':' . str_pad($count, 5, ' ', STR_PAD_LEFT) . PHP_EOL;
}
print PHP_EOL . str_repeat('-', 1 + LEN_FINDING_COL + LEN_COUNT_COL) . PHP_EOL
    . str_pad('Total Findings', LEN_FINDING_COL) . ':' . str_pad($sum, 5, ' ', STR_PAD_LEFT) . PHP_EOL;
