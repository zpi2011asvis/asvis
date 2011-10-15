(function (exports, global) {
	'use strict';
	
	var x = global.x$;

	exports.app = {
		lib: {},
		opts: null,
		
		start: function start(opts) {
			this.opts = opts;

			var resources = global.app.lib.resources,
				stores = global.app.lib.stores;

			this.db = new app.lib.LocalDB([
				new stores.Store()
			], [
				new resources.NodesResource()
			]);

			this.dispatcher = app.lib.DispatcherAdapter(x('#container'));
			this._addRoutes();
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
