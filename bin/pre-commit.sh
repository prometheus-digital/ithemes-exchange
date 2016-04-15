#!/bin/bash
git diff --cached --name-only | while read FILE; do
if [[ "$FILE" =~ ^.+(php|inc|module|install|test)$ ]]; then
    if [[ -f $FILE ]]; then
        php -l "$FILE" 1> /dev/null
        if [ $? -ne 0 ]; then
            echo "Aborting commit due to file with syntax errors: ${FILE}." >&2
            exit 1
        fi
    fi
fi
done || exit $?

if [ $? -eq 0 ]; then
    ./bin/run-tests.sh 1> /dev/null
    if [ $? -ne 0 ]; then
        echo "Unit tests failed! Aborting commit." >&2
        exit 1;
    fi
fi