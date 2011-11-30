#!/bin/sh

# This script creates new asvis database on localhost OrientDB server 

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../
touch db_offline.lock

cd $script_dir/../db/orientdb/bin
./console.sh ../../../scripts/create_orient_db.sql

cd $script_dir/../
rm db_offline.lock
