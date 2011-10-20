#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../db/vendor/orientdb/bin
./console.sh ../../../../scripts/create_orient_db.sql
