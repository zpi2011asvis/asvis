(function (exports, global) {
	'use strict';
	
	var lib = {},
		x = global.x$,
		invoke = global.es5ext.Function.invoke,
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
				var that = this;
				that._widgets.push(widget);
				widget.signals.destroyed.add(function () {
					that._remove(widget);
				});
				return that;
			},

			destroy: function destroy() {
				this._widgets.forEach(invoke('destroy'));
				this._widgets = [];
			},

			renderAll: function renderAll() {
				this._widgets.forEach(invoke('render'));
			},

			_remove: function _remove(widget) {
				var i = this._widgets.indexOf(widget);
				if (!~i) {
					throw new Error('Couldn\'t find widget in repository');
				}
				this._widgets.splice(i, 1);
			}
		},

		start: function start(opts) {
			this.opts = opts;
			this._container_el = x('#container');
			
			this._initDB();
			this.dispatcher = lib.DispatcherAdapter(this._container_el);
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
				var w = widgets.StartFormWidget.new(that._container_el);

				w.signals.submitted.add(function (params) {
					that.dispatcher.get('/node/{number}/{depth}', params);
				});

				that.widgets.add(w);
				that.render();
			});
		
			dispatcher.get('/node/{number}/{depth}', function routerNode(request) {
				var number = request.get.number,
					depth = request.get.depth;

				that.db.get('structure/graph', {
					number: number,
					depth: depth
				})
				(function (data) {
					var w = widgets.GraphWidget.new(
						that._container_el.find('#graph_renderer')
					);
					w.set('graph', data);
					w.set('root', number);

					that.widgets.add(w);
					that.render();
				}).end(that.err);
			});
		},

		_initDB: function _initDB() {
			var resources = lib.resources,
				stores = lib.stores;

			this.db = app.lib.LocalDB.new([
				stores.RemoteStore.new('/backend', app.lib.XHRAdapterXUI)
			], [
				resources.nodes.NodesFindResource.new(),
				resources.structures.StructureGraphResource.new()
			]);
		},

		render: function render() {
			this.widgets.renderAll();
		},

		err: function err(error) {
			console.log(error.message);
			console.log(error.stack);
			alert('Błąd: ' + error.message);
		}
	};

}.call({}, this, this));
