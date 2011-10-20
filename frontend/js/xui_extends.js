(function (exports, global) {
	'use strict';

	var	exts = {
		first: function first() {
			return this[0];
		},
		last: function last() {
			return this[this.length - 1];
		},
		delegate: function delegate(type, q, fn) {
			var that = this;
			that.on(type, function delegate_on(event) {
				// event target matches given selector
				if (that.find(q).has(event.target).length > 0) {
					fn(event, event.target);
				}
			});
		}
	};
	global.xui.extend(exts);
	
}.call({}, this, this));
