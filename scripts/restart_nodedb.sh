#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

$script_dir/stop_nodedb.sh
$script_dir/start_nodedb.sh

