(function (exports, global) {
	'use strict';
	
	var lib = {},
		x = global.x$,
		Signal = global.signals.Signal;

	var app = exports.app = {
		lib: lib,
		opts: null,
		_container_el: null,
		signals : {
			data_loading: {
				started: new Signal(),
				ended: new Signal()
			},
			data_reseted: new Signal()
		},

		widgets: {
			_widgets: [],

			add: function add(widget) {
				this._widgets.push(widget);
				return this;
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
			this._container_el = x('#container');

			var resources = lib.resources,
				stores = lib.stores;

			this.db = app.lib.LocalDB.new([
				stores.RemoteStore.new('/backend', app.lib.XHRAdapterXUI)
			], [
				resources.nodes.NodesFindResource.new(),
				resources.structures.StructureGraphResource.new()
			]);

			this.dispatcher = lib.DispatcherAdapter(x('#container'));
			this._addRoutes();

			lib.Flash.init({
				data_loading: this.signals.data_loading
			}, x('#flash .message'));

			lib.Templates.load(x('script.template'));
		},

		_addRoutes: function _addRoutes() {
			var that = this,
				dispatcher = this.dispatcher,
				widgets = lib.widgets;

			dispatcher.get('/', function routerRoot() {
				var w = widgets.StartFormWidget.new(
					that._container_el
				);
				console.log(w);

				that.widgets.add(w);
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
