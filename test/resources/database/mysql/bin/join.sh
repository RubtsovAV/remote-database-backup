#!/bin/bash

# get current dir
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

cat "$DIR"/../sql/header.sql \
	"$DIR"/../sql/create_database.sql

for f in "$DIR"/../sql/table*; do
	case "$f" in
		*.sql)    cat "$f";;
	esac
done

cat "$DIR"/../sql/views.sql \
	"$DIR"/../sql/triggers.sql \
	"$DIR"/../sql/procedures.sql \
	"$DIR"/../sql/footer.sql
