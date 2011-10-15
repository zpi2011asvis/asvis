this.app.lib.resources = {};

(function (exports, global) {
	'use strict';

	var Resource = function Resource() {
		this._store = null;
	};

	Resource.prototype = {
		name: null,
		cache: false,

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
