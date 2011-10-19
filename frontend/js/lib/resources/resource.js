this.app.lib.resources = {};

(function (exports, global, lib) {
	'use strict';

	var merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call;

	var Resource = function Resource(opts) {
		this._store = null;
		this._opts = merge(clone(this.default_opts), opts || {});
	};

	Resource.prototype = {
		name: null,
		default_opts: {},
		_url: null,
		_cache: false,
		_method: null,

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
	};

	// simple factory
	Resource.create = function create(constructor, prototype) {
		constructor.prototype = merge(new Resource(), prototype);
		return function () {
			var obj = new constructor();
			Resource.apply(obj, arguments);
			return obj;
		}
	};

	exports.Resource = Resource;

}.call({}, this.app.lib.resources, this, this.app.lib));
