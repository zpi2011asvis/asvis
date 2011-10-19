(function (exports, global, lib) {
	'use strict';

	var LocalDB = function LocalDB(stores, resources) {
		var that = this;

		this._stores = [];
		this._resources = {};

		stores.forEach(function (s) {
			that.pushStore(s);
		});
		resources.forEach(function (r) {
			that.addResource(r.name, r);
		});
	};

	LocalDB.prototype = {
		addResource: function addResource(name, resource) {
			if (this._stores.length === 0) {
				throw new Error('Stores have to be pushed before adding resource');
			}
			this._resources[name] = resource;
			resource.setStore(this._stores[0]);

			return this;
		},

		pushStore: function pushStore(store) {
			if (this._stores.length > 0) {
				this._stores[this._stores.length - 1].setNext(store);
			}
			this._stores.push(store);

			return this;
		},

		get: function get(name, params) {
			return this._resources[name].get(params);
		}
	};

	exports.LocalDB = LocalDB;

}.call({}, this.app.lib, this, this.app.lib));
