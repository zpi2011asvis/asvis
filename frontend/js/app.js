(function (exports, global) {
	'use strict';

	exports.app = {
		lib: {},
		opts: null,
		
		start: function app_start(opts) {
			this.opts = opts;
			this.dispatcher = app.lib.DispatcherAdapter(
				global.x$('#container')
			);
			this._addRoutes();
		},

		_addRoutes: function _app_start() {
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
