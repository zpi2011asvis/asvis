#!/bin/sh

script_dir="$( cd "$( dirname "$0" )" && pwd )"

cd $script_dir/../frontend/js/vendor/
node4 ./node_modules/webmake/bin/webmake cjs_exports.js cjs_exports_webmade.js
