#!/bin/sh

current_path=`pwd`
script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../db/vendor/orientdb/bin
./server.sh
cd $current_path
