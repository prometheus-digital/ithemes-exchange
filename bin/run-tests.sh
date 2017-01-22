#!/bin/sh

#dir=$(pwd)
dir="/app/wordpress-develop/tests/phpunit/"

export WP_TESTS_DIR="$1"

shift

phpunit "$@"
