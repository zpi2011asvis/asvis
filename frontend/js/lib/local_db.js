(function (exports, global, lib) {
	'use strict';

	var classy = global.util.classy;

	var LocalDB = classy(function LocalDB() {},
		{
			init: function init(stores, resources) {
				var that = this;

				this._stores = [];
				this._resources = {};

				stores.forEach(function (s) {
					that.pushStore(s);
				});
				resources.forEach(function (r) {
					that.addResource(r.name, r);
				});
			},

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
				return this._getResource(name).get(params);
			},

			_getResource: function _getResource(name) {
				var r = this._resources[name];
				if (!r) throw new Error('Resource "' + name + '" does not exist');
				return r;
			}
		}
	);

	exports.LocalDB = LocalDB;

}.call({}, this.app.lib, this, this.app.lib));
