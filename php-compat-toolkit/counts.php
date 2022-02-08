#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Generates per-finding counts of PHPCompatibility report findings.
 * Invoked by php-compat-toolkit/summarise-compat-findings.sh
 *
 * @license php-compat-toolkit/LICENSE.md Apache-2.0
 */
const LEN_FINDING_COL = 100;
const LEN_COUNT_COL = 5;

if ($argc < 2) {
    exit('Please invoke with a filepath. E.g.,:' . PHP_EOL
        . basename(__FILE__) . ' /file/to/process.txt' . PHP_EOL
    );
}

if (! file_exists($argv[1])) {
    exit("The file '$argv[1]' does not exist." . PHP_EOL
        . 'Please check the filepath.' . PHP_EOL);
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
 * Different versions of PHPCompatibility produce different strings for some findings,
 * making this de-duplication a bit of work.
 *
 * To add to this list, check the output of `raw-counts.txt` and use the ksort() below
 */
$dup_map = [
    'Since PHP 7.0, functions inspecting arguments' => 'Since PHP 7.0, functions inspecting arguments provide current value',
    'Function split() is deprecated since PHP 5.3 and removed'  => 'Function split() is deprecated since PHP 5.3',
    'Extension \'mcrypt\' is deprecated'     => 'Extension \'mcrypt\' is deprecated since PHP 7.1',
    'Function mcrypt_.+ is deprecated'       => 'mcrypt_*() functions deprecated since PHP 7.1',
    'The constant "MCRYPT_.+" is deprecated' => 'MCRYPT_* constants deprecated since PHP 7.1',
    'Global variable \'$HTTP_POST_FILES\' is deprecated since'  => 'Global variable \'$HTTP_POST_FILES\' is deprecated',
    'Global variable \'$HTTP_POST_VARS\' is deprecated since'   => 'Global variable \'$HTTP_POST_VARS\' is deprecated',
    'Global variable \'$HTTP_SERVER_VARS\' is deprecated since' => 'Global variable \'$HTTP_SERVER_VARS\' is deprecated',
    'INI directive \'safe_mode\' is deprecated since PHP 5.3'   => 'INI directive \'safe_mode\' is deprecated since PHP',
    'The __toString() magic method will no longer accept'       => 'The __toString() magic method can no longer accept',
    'Use of deprecated PHP4 style class constructor' => 'Use of PHP4 style class constructor is deprecated/removed',
    'preg_replace() - \/e modifier is deprecated since PHP'      => 'preg_replace() - /e modifier is deprecated since',
];

fwrite(STDERR, "Processing file $argv[1]" . PHP_EOL);
$lines = file($argv[1]);

foreach ($lines as $line) {
    $index = extractFindingAndCount($line, $matches);
    $index = deduplicateFindingText($dup_map, $index);

    if (! array_key_exists($index, $findings)) {
        $findings[$index] = 0;
    }
    $findings[$index] += $matches[2];
    $sum += $matches[2];
}

arsort($findings, SORT_NUMERIC); // sort descending by frequency of finding
//ksort($findings, SORT_LOCALE_STRING); // look for "duplicate" findings strings

printHeader();
foreach ($findings as $finding => $count) {
    print str_pad((string) $count, LEN_COUNT_COL, ' ', STR_PAD_LEFT) . ' : ' . $finding . PHP_EOL;
}
printSum($sum);

/**
 * Note: Side-effect: operates upon $matches array
 * @param string $line
 * @param array $matches
 *
 * @return string
 */
function extractFindingAndCount(string $line, array &$matches): string
{
    // Still looking for a single pattern to do this.
    // If it exists, we do not want the text ( Found: \s+), prior to the count
    // at the end of the line.
    // If the line does not contain "Found" then search again without "Found"
    // Even with pos/neg lookahead/behind greedy/ungreedy, I haven't worked
    // out how to match both with and without "Found". The intuitive approach
    // didn't work.
    if (0 === preg_match('/(.+)Found.*\s(\d+)/', $line, $matches)) {
        preg_match('/(.+)\s(\d+)/', $line, $matches);
    }

    return $matches[1];
}

/**
 * @param array $dup_map
 * @param string $finding
 * @return string
 */
function deduplicateFindingText(array $dup_map, string $finding): string
{
    $findingText = ltrim($finding, ' []x');
    $pattern = '';

    foreach ($dup_map as $pattern => $dedupString) {
        if (preg_match("/$pattern/", $finding) === 0) {
            continue;
        }

        $findingText = $dup_map[$pattern];
    }

    return $findingText;
}

/**
 * @return void
 */
function printHeader(): void
{
    print str_pad('Number of Occurrences : PHP Compatibility Finding', LEN_COUNT_COL + LEN_FINDING_COL, ' ', STR_PAD_BOTH) . PHP_EOL
        . str_repeat('-', LEN_FINDING_COL + LEN_COUNT_COL) . PHP_EOL;
}

/**
 * @param int $sum
 * @return void
 */
function printSum(int $sum): void
{
    print PHP_EOL . str_repeat('-', 1 + LEN_FINDING_COL + LEN_COUNT_COL) . PHP_EOL
        . 'Total Findings' . ':' . str_pad((string) $sum, LEN_COUNT_COL, ' ', STR_PAD_LEFT) . PHP_EOL;
}
