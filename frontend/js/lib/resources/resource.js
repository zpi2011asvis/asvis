this.app.lib.resources = {};

(function (exports, global) {
	'use strict';

	var merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call;

	var Resource = function Resource(opts) {
		this._store = null;
		this._opts = merge(clone(this.default_opts), opts || {});
	};

	Resource.prototype = {
		name: null,
		cache: false,
		default_opts: {},

		get: function get(params) {
			return this._prepareData(
				this._store.get(this.name, this.cache, params)
			);
		},

		setStore: function setStore(store) {
			this._store = store;
		},

		// override in child if want to customize what's returned
		_prepareData: function _prepareData(data) {
			return data;
		}
	};

	exports.Resource = Resource;

}.call({}, this.app.lib.resources, this));
