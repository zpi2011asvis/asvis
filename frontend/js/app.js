(function (exports, global) {
	'use strict';
	
	var lib = {},
		x = global.x$,
		Signal = global.signals.Signal;

	var app = exports.app = {
		lib: lib,
		opts: null,
		signals : {
			data_loading: {
				started: new Signal(),
				ended: new Signal()
			},
			data_reseted: new Signal()
		},

		widgets: {
			_widgets: [],

			add: function add(widget_constructor) {
				var widget = widget_constructor.new();
				this._widgets.push(widget);
				return widget;
			},

			destroy: function destroy() {
				this._widgets.forEach(function (w) {
					w.destroy();
				});
				this._widgets = [];
			}
		},

		start: function start(opts) {
			this.opts = opts;

			var resources = lib.resources,
				stores = lib.stores;

			this.db = new app.lib.LocalDB([
				new stores.RemoteStore('/backend', app.lib.XHRAdapterXUI)
			], [
				new resources.nodes.NodesFindResource(),
				new resources.structures.StructureGraphResource()
			]);

			this.dispatcher = lib.DispatcherAdapter(x('#container'));
			this._addRoutes();

			lib.Flash.init({
				data_loading: this.signals.data_loading
			}, x('#flash .message'));
		},

		_addRoutes: function _addRoutes() {
			var that = this,
				dispatcher = this.dispatcher,
				widgets = lib.widgets;

			dispatcher.get('/', function routerRoot() {
				that.widgets.add(widgets.StartFormWidget);
				that.render();
			});

			dispatcher.get('/node/{number}/{depth}', function routerNode(request) {
				var num = request.get.number,
					depth = request.get.depth;
			});
		},

		render: function render(params) {
			
		}
	};

}.call({}, this, this));
