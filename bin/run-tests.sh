#!/bin/sh

dir=$(pwd)
dir+="/../../../../../wordpress-develop/tests/phpunit"

export WP_TESTS_DIR=${dir}

phpunit
