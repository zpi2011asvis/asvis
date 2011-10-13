(function (exports, global) {
	'use strict';

	var	exts = {
		first: function () { return this[0]; },
		last: function () { return this[this.length - 1]; },
		delegate: function (q, type, fn) {
			this.on('click', function (event) {
				console.log(event);
				event.preventDefault();
			});
		}
	};
	global.xui.extend(exts);
	
}.call({}, this, this));
