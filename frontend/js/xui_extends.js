(function (exports, global) {
	'use strict';

	var	exts = {
		first: function () { return this[0]; },
		last: function () { return this[this.length - 1]; },
		delegate: function (type, q, fn) {
			var that = this;
			that.on(type, function (event) {
				// event target matches to given selector
				if (that.find(q).has(event.target).length > 0) {
					fn(event, event.target);
				}
			});
		}
	};
	global.xui.extend(exts);
	
}.call({}, this, this));
