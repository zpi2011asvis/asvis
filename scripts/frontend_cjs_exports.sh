#!/bin/sh

current_path=`pwd`
script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../frontend/js/vendor/
webmake cjs_exports.js cjs_exports_webmade.js
cd $current_path
