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
			var that = this,
				//clone because of changes that will be done
				params = clone(opts.params);

			app.signals.data_loading.started.dispatch(that);

			return that._xhr_adapter(
				that._absoluteURL(opts.url, params),
				{
					method: opts.method,
					params: params
				}
			)
			(function (data) {
				//close flash when ok
				app.signals.data_loading.ended.dispatch(that);
				//and return parsed response
				return JSON.parse(data);
			},
			function (err) {
				//close it anyway
				app.signals.data_loading.ended.dispatch(that);
				return err;
			});
		},

		_absoluteURL: function _absoluteURL(resource_url, params) {
			for (var p in params) {
				//TODO url encoding
				resource_url = resource_url.replace(':' + p, params[p]);
				delete params[p];
			}
			return this._url + '/' + resource_url;
		}
	});

	exports.RemoteStore = RemoteStore;

}.call({}, this.app.lib.stores, this));
