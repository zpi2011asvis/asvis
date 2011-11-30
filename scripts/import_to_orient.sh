#/bin/sh

# This script imports data stored in MySQL asmap database into OrientDB server 

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../
touch db_offline.lock

cd $script_dir/import/
php import_to_orient.php

cd $script_dir/../
rm db_offline.lock