#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

node $script_dir/../db/nodedb/srv.js &
echo $! > $script_dir/../nodedb.lck
echo "\n"
exit 1
