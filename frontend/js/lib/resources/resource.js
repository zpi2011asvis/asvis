this.app.lib.resources = {};

(function (exports, global, lib) {
	'use strict';

	var merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call,
		classy = global.util.classy;

	var Resource = classy(function Resource() {}, {
		name: null,
		default_opts: {},
		_url: null,
		_cache: false,
		_method: null,

		init: function init(opts) {
			this._store = null;
			this._opts = merge(clone(this.default_opts), opts || {});
		},

		get: function get(params) {
			return this._prepareData(
				this._store.get({
					url: this._url,
					method: this._method,
					cache: this._cache,
					params: params
				})
			);
		},

		setStore: function setStore(store) {
			this._store = store;
		},

		// override in child if want to customize what's returned
		_prepareData: function _prepareData(data) {
			return data;
		}
	});

	exports.Resource = Resource;

}.call({}, this.app.lib.resources, this, this.app.lib));
