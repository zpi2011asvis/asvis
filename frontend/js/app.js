(function (exports, global) {
	'use strict';

	exports.app = {
		lib: {},
		opts: null,
		
		start: function app_start(opts) {
			this.opts = opts;
			this.dispatcher = app.lib.DispatcherAdapter(
				this._getRouter(),
				global.x$('#container')
			);
		},

		_getRouter: function _app_start() {
			var router = global.crossroads;

			router.addRoute('/get/', function router_root() {
				console.log('IN: /');
			});

			router.addRoute('/get/kopytko/{id}', function router_kopytko(id) {
				console.log('IN: /kopytko, id: ' + id);
			});

			return router;
		}
	};

}.call({}, this, this));
