this.app.lib.stores = {};

(function (exports, global, lib) {
	'use strict';

	var Signal = global.signals.Signal,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call,
		classy = global.util.classy;
	
	var Store = classy(function Store(opts) {},	{
		default_opts: {},

		init: function init(opts) {
			this.reseted = new Signal();
			this._next = null;
			this._opts = merge(clone(this.default_opts), opts || {});
		},

		setNext: function setNext(store) {
			var that = this;
			that._next = store;

			//when next store is reseted
			//reset and pass this message further
			store.reseted.add(function () {
				that._reset();
				that.reseted.dispatch(that);
			});
		},

		get: function get(opts) {
			// TODO how complex requests should be cached
			// resource_type parameter? 'array' means indexes are indexes in db,
			// 'object' keys are keys, etc.
			//
			// opts:
			// * cache - resource value should be cached
			// * type - 'plain', 'array', 'object' 
			// * url
			// * name
			// * method
			// * params
		},

		_reset: function _reset() {
			//do nothing?
		}
	});

	exports.Store = Store;

}.call({}, this.app.lib.stores, this, this.app.lib));
