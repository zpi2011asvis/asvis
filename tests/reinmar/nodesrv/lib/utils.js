'usr strict';

var DEBUG = false;

var log = function (module_name) {
	var slice = [].slice;
	return function log() {
		DEBUG && console.log.apply(console, module_name ? [ module_name + ':' ].concat(slice.call(arguments)) : arguments);
	};
};

var extend = function extend(data) {
	for (var key in data) {
		if (data.hasOwnProperty(key)) {
			this[key] = data[key];
		}
	}
};




exports.log = log;
exports.extend = extend;

exports.setDebug = function setDebug(d) {
	DEBUG = d;
};

