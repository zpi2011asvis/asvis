(function (exports, global) {
	'use strict';

	var Store = global.app.lib.stores.Store,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call;
	
	var RemoteStore = function RemoteStore(url, xhr_adapter, opts) {
		Store.call(this, opts);
		this._url = url;
		this._xhr_adapter = xhr_adapter;
	};

	RemoteStore.prototype = merge(new Store(), {
		default_opts: merge(clone(Store.prototype.default_opts), {
			buffer: true, //merge array and object resources queries
			buffer_delay: 10, //ms
		}),

		get: function get(opts) {
			return this._xhr_adapter(
				this._absoluteURL(opts.url),
				{
					method: opts.method,
					params: opts.params
				}
			)
			(function (data) {
				return JSON.parse(data);
			});
		},

		_absoluteURL: function _absoluteURL(resource_url) {
			return this._url + '/' + resource_url;
		}
	});

	exports.RemoteStore = RemoteStore;

}.call({}, this.app.lib.stores, this));
