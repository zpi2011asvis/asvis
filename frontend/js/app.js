(function (exports, global) {
	'use strict';
	
	var lib = {},
		x = global.x$,
		invoke = global.es5ext.Function.invoke,
		Signal = global.signals.Signal,
		deffered = global.deferred;

	var app = exports.app = {
		lib: lib,
		opts: null,
		_container_el: null,
		signals: {
			// all tasks connected with XHR
			data_loading: {
				started: new Signal(),
				ended: new Signal()
			},
			// all tasks connected with recalculating nodes positions
			graph_rendering: {
				started: new Signal(),
				ended: new Signal()
			},
			// fired when data on server changed (new import came)
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
				// with cloning because of splicing in _remove()
				// which is done together with this
				this._widgets.slice().forEach(invoke('destroy'));
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
				data_loading: this.signals.data_loading,
				graph_rendering: this.signals.graph_rendering,
			}, x('#flash .message'));

			lib.Templates.load(x('script.template'));
		},

		_addRoutes: function _addRoutes() {
			var that = this,
				dispatcher = this.dispatcher,
				widgets = lib.widgets;

			dispatcher.get('/', function routerRoot() {
				var w = widgets.StartFormWidget.new(that._container_el);

				w.signals.submitted.add(function routerRoot_onSubmit(params) {
					that.dispatcher.get('/node/{number}/{depth}', params);
				});

				that.widgets.destroy();
				that.widgets.add(w);
				that.render();
			});
		
			dispatcher.get('/node/{number}/{depth}', function routerNode(request) {
				var number = request.get.number,
					depth = request.get.depth,
					graph_w, info_w;
	
				// TODO remove in the future - should modify current graph
				// and widget should manage what to do with new data
				that.widgets.destroy();
	

				that.db.get('structure/graph', {
					number: number,
					depth: depth
				})
				(function routerNode_promise1(graph_data) {
					graph_w = widgets.GraphWidget.new(
						that._container_el.find('#graph_renderer')
					);
					graph_w.set('graph', graph_data);
					graph_w.set('root', number);

					that.widgets.add(graph_w);
					that.render();

					return deferred.all(
						that.db.get('connections/meta', {
							for_node: number
						}),
						that.db.get('nodes/meta', {
							// don't ask for more than 10000 nodes - get the closest
							numbers: graph_data.distance_order.slice(0, 10000)
						})
					);
				})
				(function routerNode_promise2(data) {
					var conns_meta = data[0],
						nodes_meta = data[1];

					info_w = widgets.InfobarWidget.new(
						that._container_el.find('#sidebar')					
					);
					// TODO add batch set (with object)
					info_w.set('nodes_meta', nodes_meta);
					info_w.set('connections_meta', conns_meta);
					info_w.set('root', number);
					info_w.set('depth', depth);
					
					that.widgets.add(info_w);
					that.render();

					info_w.signals.connection_hovered.add(function (from, to) {
						graph_w.markConnectionTo(from, to);
					});
					info_w.signals.connection_unhovered.add(function () {
						graph_w.unmarkConnection();
					});
				}).end(that.err);
			});
		},

		_initDB: function _initDB() {
			var resources = lib.resources,
				stores = lib.stores;

			this.db = app.lib.LocalDB.new([
				stores.MemoryStore.new(),
				stores.RemoteStore.new('/backend', app.lib.XHRAdapterXUI)
			], [
				resources.nodes.NodesFindResource.new(),
				resources.nodes.NodesMetaResource.new(),
				resources.connections.ConnectionsMetaResource.new(),
				resources.structures.StructureGraphResource.new()
			]);
		},

		render: function render() {
			this.widgets.renderAll();
		},

		err: function err(error) {
			console.log(error.message);
			console.log(error.stack);
			alert('Błąd: ' + error.message.slice(0, 100));
		}
	};

}.call({}, this, this));
