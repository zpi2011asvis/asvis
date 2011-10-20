#/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/import/
php import_to_orient.php
