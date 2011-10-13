(function (exports, global) {
	'use strict';
	
	exports.DispatcherAdapter = function DispatcherAdapter(router, container_el) {
		container_el.delegate('a', 'click', function (event, el) {
		});
	};

}.call({}, this.app.lib, this));
