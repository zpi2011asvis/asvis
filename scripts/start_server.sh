#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

node $script_dir/../db/nodedb/srv.js
