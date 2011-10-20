this.util = {};

(function (exports, global) {
	'use strict';

	var superMerge = function superMerge(parent, child) {
		var new_prop;
		Object.keys(child).forEach(function (p) {
			if (typeof child[p] === 'function' && typeof parent[p] === 'function') {
				new_prop = p.replace(/^_/, '');
				new_prop = '_s' + new_prop[0].toUpperCase() + new_prop.slice(1);
				parent[new_prop] = parent[p];
			}
			parent[p] = child[p];
		});

		return parent;
	};

	// neww because new is reserved keyword
	var neww = function neww() {
		var obj = new this;
		obj.init && obj.init.apply(obj, arguments);
		return obj;
	};

	var classy = function classy(constructor, prototype, statics) {
		var parent = (typeof this === 'function' ? this : function () { return {}; });
		statics = statics || {};

		constructor.prototype = superMerge(new parent(), prototype);
		constructor.create = classy;

		// if parent's new() method is not the standard one it means
		// it was overridden
		// if new statics object does not contain new(), continue chaining by
		// delegating parent's method to the new constructor statics
		//
		// warning: 3 level chaining is not possible and should not be used
		if (
			parent.new && parent.new !== neww &&
			typeof statics.new !== 'function'
		) {
			statics.new = parent.new;
		}
		constructor.new = neww;

		statics && superMerge(constructor, statics);

		return constructor;
	};

	exports.classy = classy;

}.call({}, this.util, this));
