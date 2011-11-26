#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../db/orientdb/bin
./server_low_mem.sh
