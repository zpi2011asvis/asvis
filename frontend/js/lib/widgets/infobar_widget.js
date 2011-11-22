(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		x = global.x$,
		objectValues = global.es5ext.Object.plain.values.call;

	var InfobarWidget = Widget.create(function InfobarWidget() {}, {
		_init: function _init() {
		}
	},
	{
		multiple: false
	});

	InfobarWidget.View = Widget.View.create(function InfobarWidgetView() {}, {
		signals: {
		},
		_els: null,

		_init: function _init() {
			this.signals = {
			};
			this._els = {};
		},
			
		render: function render(data) {
			if (this._el) return;

			var that = this,
				window_el = x$(global.window),
				root = data.root,
				root_meta = data.nodes_meta[root],
				connections = data.connections_meta,
				count_fn = function (dir) {
					return function (acc, curr) { return curr.dir === dir ? acc + 1 : acc; };
				};

			that._cel.html(
				that._position,
				that._tpls.render('infobar', {
					root: root,
					depth: data.depth,
					root_name: root_meta.name,
					root_pools: root_meta.pools,
					connections_count: {
						total: connections.length,
						both: connections.reduce(count_fn('both'), 0),
						up: connections.reduce(count_fn('up'), 0),
						down: connections.reduce(count_fn('down'), 0)
					},
					connections: connections,
					connection_statuses: [
						'Połączenie prawidłowe',
						// TODO update %1 and %2 while templating
						'Brak połączenia w węźle :1',
						'Brak połączenia w węźle :2'
					]
				})
			);
			that._el = that._cel.find('#node_data');
	//		that._els.


			that._addEvent(window_el, 'resize', function (event) {
				that._resize();
			});			
		},

		_resize: function _resize() {
		}
	});

	exports.InfobarWidget = InfobarWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
