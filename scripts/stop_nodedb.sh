#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"
lck_file=$script_dir/../nodedb.lck
node_pid=`cat $lck_file`

if kill $node_pid; then
	rm $lck_file
	echo "SERVER: Stopped.\n"
fi
