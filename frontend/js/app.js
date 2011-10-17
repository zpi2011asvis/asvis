(function (exports, global) {
	'use strict';
	
	var x = global.x$,
		Signal = global.signals.Signal;	

	exports.app = {
		lib: {},
		opts: null,
		signals : {
			data_loading: {
				started: new Signal(),
				ended: new Signal()
			},
			data_reseted: new Signal()
		},
		
		start: function start(opts) {
			this.opts = opts;

			var resources = global.app.lib.resources,
				stores = global.app.lib.stores;

			this.db = new app.lib.LocalDB([
				new stores.RemoteStore('/backend', global.app.lib.XHRAdapterXUI)
			], [
				new resources.NodesResource()
			]);

			this.dispatcher = app.lib.DispatcherAdapter(x('#container'));
			this._addRoutes();

			app.lib.Flash.init({
				data_loading: this.signals.data_loading
			}, x('#flash .message'));
		},

		_addRoutes: function _addRoutes() {
			var dispatcher = this.dispatcher;

			dispatcher.get('/', function router_root() {
				console.log('IN: /');
			});

			dispatcher.get('/kopytko/{id}', function router_kopytko(request) {
				console.log('IN: /kopytko, id: ', request);
			});
		}
	};

}.call({}, this, this));
