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
			global.Routes.call(this);

			lib.Flash.init({
				data_loading: this.signals.data_loading,
				graph_rendering: this.signals.graph_rendering,
			}, x('#flash .message'));

			lib.Templates.load(x('script.template'));
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
				resources.structures.StructureGraphResource.new(),
				resources.structures.StructureTreesResource.new(),
				resources.structures.StructurePathsResource.new()
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
