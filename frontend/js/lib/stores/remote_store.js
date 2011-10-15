(function (exports, global) {
	'use strict';

	var Store = global.app.lib.stores.Store,
		merge = global.es5ext.Object.plain.merge.call;
	
	var RemoteStore = function RemoteStore(url, adapter, opts) {
		Store.call(this, opts);
	};

	RemoteStore.prototype = merge(new Store(), {
		get: function get(resource_name, cache_it, params) {
			return 'haaaa';
		},
	});

	exports.RemoteStore = RemoteStore;

}.call({}, this.app.lib.stores, this));
