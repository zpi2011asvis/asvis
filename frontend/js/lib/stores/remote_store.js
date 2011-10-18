(function (exports, global) {
	'use strict';

	var app = global.app,
		Store = app.lib.stores.Store,
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
			var that = this;
			app.signals.data_loading.started.dispatch();

			return that._xhr_adapter(
				that._absoluteURL(opts.url),
				{
					method: opts.method,
					params: opts.params
				}
			)
			(function (data) {
				app.signals.data_loading.ended.dispatch(that);
				return JSON.parse(data);
			});
		},

		_absoluteURL: function _absoluteURL(resource_url) {
			return this._url + '/' + resource_url;
		}
	});

	exports.RemoteStore = RemoteStore;

}.call({}, this.app.lib.stores, this));
