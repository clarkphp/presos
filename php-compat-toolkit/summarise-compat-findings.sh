#!/usr/bin/env sh
# License: see php-compat-toolkit/LICENSE.md Apache-2.0

# Briefly summarise compatibility findings
# Process *.out files, representing PHPCompatibility reports
# Display the total number of findings in all reports and the number
# of PHP source code files affected
# Invokes counts.php PHP script to obtain per-finding counts
# Usage:
#     chmod u+x summarise-compat-findings.sh
#     ./summarise-compat-findings.sh
REPORT_DIR="/home/$LOGNAME/php-compat-toolkit"
SCRIPTS_DIR="/home/$LOGNAME/php-compat-toolkit"
php_exe="/usr/local/zendsvr6/bin/php-cli" # path to your PHP binary (executable)

cd "$REPORT_DIR" || exit

i=0
esum=0
wsum=0
fsum=0

raw_findings="$REPORT_DIR/raw-findings.txt"
raw_counts="$REPORT_DIR/raw-counts.txt"
raw_ext_findings="$REPORT_DIR/raw-ext-findings.txt"

printf "" > "$raw_findings"
printf "" > "$raw_counts"

for REPORT_FILE in *.out; do
  e_count=0
  w_count=0
  f_count=0

  if [ ! -e "$REPORT_FILE" ]; then # POSIX test for existence
    continue
  fi

  i=$((i + 1))
  printf "%d: Number of incompatibility findings in %s\n" "$i" "$REPORT_FILE"
  e_count="$(grep -Fc '| ERROR ' "$REPORT_FILE")"
  w_count="$(grep -Fc '| WARNING ' "$REPORT_FILE")"
  f_count="$(grep -Fc 'FILE: ' "$REPORT_FILE")"
  printf "Error-level  : %d\nWarning-level: %d\nNum Files    : %d\n\n" "$e_count" "$w_count" "$f_count"

  esum=$((esum + e_count))
  wsum=$((wsum + w_count))
  fsum=$((fsum + f_count))

  grep '| ERROR \|| WARNING ' "$REPORT_FILE" |
    awk 'BEGIN {FS = "|";} { print $3 >> "'$raw_findings'"; ++findings[$3] } END { for (i in findings) print i, findings[i] }' >> "$raw_counts"
done

sort -o "$raw_counts" "$raw_counts"
uniq "$raw_counts" | grep -F ' Extension ' "$raw_counts" > "$raw_ext_findings"

printf "Total findings in %d reports\nError-level  : %d\nWarning-level: %d\n" "$i" "$esum" "$wsum"
printf "Number of PHP source code files affected by findings: %d\n\n" "$fsum"

test -e "$SCRIPTS_DIR/counts.php" || { printf "%s/counts.php is missing\n" "$REPORT_DIR"; exit 1; }
"$php_exe" "$SCRIPTS_DIR/counts.php" "$raw_counts"
