this.app.lib.stores = {};

(function (exports, global) {
	'use strict';

	var Signal = global.signals.Signal;
	
	var Store = function Store() {
		this.reseted = new Signal();
		this._next = null;
	};

	Store.prototype = {
		setNext: function setNext(store) {
			var that = this;
			that._next = store;

			//when next store is reseted
			//reset and pass this message further
			store.reseted.add(function () {
				that._reset();
				that.reseted.dispatch();
			});
		},

		get: function get(resource_name, cache_it, params) {
			// TODO how partial request should be cached
			// cache_type parameter? 'array' means indexes are indexes in db,
			// 'object' keys are keys, etc.
			return 1;
		},

		_reset: function _reset() {
			//abstract
		}
	};

	exports.Store = Store;

}.call({}, this.app.lib.stores, this));
