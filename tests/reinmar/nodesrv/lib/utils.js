'usr strict';

var DEBUG = false;

var log = function log() {
	DEBUG && console.log.apply(console, arguments);
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

