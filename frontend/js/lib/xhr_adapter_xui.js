(function (exports, global, lib) {
	'use strict';

	var XHRAdapterXUI = function XHRAdapterXUI(url, opts) {
		var d = global.deferred();

		//TODO data object to query params
	
		global.x$().xhr(url, {
			async: true,
			headers: { 'content-type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			method: opts.method,
			data: opts.params,
			callback: function callback() {
				d.resolve(this.responseText);
			},
			error: function errror() {
				var error = new Error(this.statusText + ': ' + this.responseText);
				global.DEBUG && console.log(error.message);
				d.resolve(error);
			}
		});

		return d.promise;
	};

	exports.XHRAdapterXUI = XHRAdapterXUI;

}.call({}, this.app.lib, this, this.app.lib));
