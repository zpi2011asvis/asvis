(function (exports, global, lib) {
	'use strict';

	var Store = lib.stores.Store,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call;
	
	var RemoteStore = Store.create(function RemoteStore(url, xhr_adapter, opts) {}, {
		default_opts: merge(clone(Store.prototype.default_opts), {
			buffer: true,		// merge array and object resources queries
			buffer_delay: 10,	// ms
		}),
		_url: null,
		_xhr_adapter: null,

		init: function init(url, xhr_adapter, opts) {
			this._sInit(opts);
			this._url = url;
			this._xhr_adapter = xhr_adapter;
		},

		get: function get(opts) {
			var that = this,
				//clone because of changes that will be done
				params = clone(opts.params);

			global.app.signals.data_loading.started.dispatch(that);
			return that._xhr_adapter(
				that._absoluteURL(opts.url, params),
				{
					method: opts.method,
					params: params
				}
			)
			(function (data) {
				//close flash when ok
				global.app.signals.data_loading.ended.dispatch(that);
				//and return parsed response
				return JSON.parse(data);
			},
			function (err) {
				//close it anyway
				global.app.signals.data_loading.ended.dispatch(that);
				return err;
			});
		},

		_absoluteURL: function _absoluteURL(resource_url, params) {
			var pattern;

			for (var param in params) {
				pattern = ':' + param;

				if (resource_url.indexOf(pattern) > -1) {
					resource_url = resource_url.replace(pattern, params[param]);
					delete params[param];
				}
				//TODO url encoding
			}
			return this._url + '/' + resource_url;
		}
	});

	exports.RemoteStore = RemoteStore;

}.call({}, this.app.lib.stores, this, this.app.lib));
