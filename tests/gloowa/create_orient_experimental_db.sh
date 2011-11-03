#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../../db/vendor/orientdb/bin
./console.sh ../../../../tests/gloowa/create_orient_experimental_db.sql
