#!/usr/bin/env bash
set -e

title() {
    local len=$((${#1}+2))
    printf "\n+"
    printf -- "-%.0s" $(seq 1 $len)
    printf "+\n| %s |\n+" "$1"
    printf -- "-%.0s" $(seq 1 $len)
    printf "+\n\n"
}
notify() {
    printf "> %s \n" "$1"
}

exit_failed() {
    notify "Failed: $1"
    exit 1
}

dump_trigger () {
  notify "dump triggers"
  mysqldump -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -P"$DB_PORT" --no-data --triggers --no-create-db --no-create-info  --lock-tables=FALSE "$DB_NAME" > triggers_backup.sql
}

restore_trigger () {
  notify "dump triggers"
  mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -P"$DB_PORT" "$DB_NAME" < triggers_backup.sql
}

import_db () {
  notify "Importing dump $DUMP_FILE"
  mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -P"$DB_PORT" "$DB_NAME" < "$DUMP_FILE"
}


create_dump () {
  notify "Creating dump"
  ## ignore error 1449 user not found for DEFINER in VIEWS
  mysqldump -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -P"$DB_PORT" --no-data --hex-blob --ignore-error=1449 "$DB_NAME" > "$DUMP_FILE_NEW" 2>database.err
  sed -i /insecure/d  database.err
  sed -i /Got\ error\:\ 1449/d  database.err
  if [ -s database.err ]; then
        cat database.err
        return 2
  fi
  rm database.err
}

processing_anonymify () {
  notify "Processing dump"
  php -d  memory_limit=-1 bin/console anony -c "$SETTING"
}

cleanup () {
  notify "Cleanup system"
  rm "$DUMP_FILE_NEW" triggers_backup.sql

  TABLES=$(mysql -u"$DB_USER" -p"$DB_PASS" -P"$DB_PORT" -h"$DB_HOST" "$DB_NAME" -e 'SHOW TABLES' | $AWK '{ print $1}' | $GREP -v '^Tables' )
  for t in $TABLES
  do
    mysql -u"$DB_USER" -p"$DB_PASS" -P"$DB_PORT" -h"$DB_HOST" "$DB_NAME" -e "SET FOREIGN_KEY_CHECK=0;DROP TABLE $t;SET FOREIGN_KEY_CHECK=1"
  done
}

options="d:c:t:h"
long_options="dump:,pre-processing:,config:,target-file:,help"
# Parse command line options
parsed_options=$(getopt -o $options -l $long_options -- "$@")
# Check for errors in parsing
if [ $? -ne 0 ]; then
    exit 1
fi
# Evaluate the parsed options
eval set -- "$parsed_options"
# Process the options
while true; do
    case "$1" in
        -d|--dump)
            DUMP_FILE=$2
            shift 2
            ;;
        -c|--config)
            SETTING=$2
            shift 2
            ;;
        -t|--target-file)
            OUTPUT_FILE=$2
            shift 2
            ;;
        -h|--help)
            echo "Usage: script.sh -p|--dump <dump-file> -c|--config <config> -t|target-file <obfuscate-output-file> [-h|--help]"
            exit 0
            ;;
        --)
            shift
            break
            ;;
        *)
            echo "Invalid option: $1"
            exit 1
            ;;
    esac
done

title "Obfuscate Data"

if [ ! -f .env ]; then
    echo ".env file not found!"
fi

. .env

if [[ -z "$DUMP_FILE" ]]; then
  echo "Missing dumpfile -d|--dump"
  exit 1
fi
if [[ -z "$SETTING" ]]; then
  echo "Missing config file -c|--config"
  exit 1
fi
if [[ -z "$OUTPUT_FILE" ]]; then
  echo "Missing target file -t|--target-file"
  exit 1
fi

#import_db || exit_failed "Importing DB dump"
#dump_trigger || exit_failed "Create trigggers dump"
processing_anonymify || exit_failed "Anonymify failed"
#restore_trigger || exit_failed "Restore trigggers"
#@todo create_dump || exit_failed "Creating dump file"

title "Finished"
