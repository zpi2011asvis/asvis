#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../db/vendor/orientdb_stable/bin
./server.sh
