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
				cel = that._cel,
				window_el = x$(global.window),
				root = data.root,
				root_meta = data.nodes_meta[root],
				connections = data.connections_meta,
				count_fn = function (dir) {
					return function (acc, curr) { return curr.dir === dir ? acc + 1 : acc; };
				};

			cel.html(
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
			that._el = cel.find('#node_data');
			that._els.lists_scrolls = {
				conns: cel.find('#node_data_conns .scrolled'),
				pools: cel.find('#node_data_pools .scrolled')
			};

			that._resize();


			that._addEvent(window_el, 'resize', function (event) {
				that._resize();
			});
		},

		_resize: function _resize() {
			// argh............................. i'm sooo stupid
			// my worst method ever
			var	heights = this._getListsAutoHeights(),
				diff = heights.conns + heights.pools - this._getSpaceForLists(),
				min_height = this._getSpaceForLists() * 0.3,
				ratio = heights.conns / heights.pools,
				lists = this._els.lists_scrolls,
				conns_el = lists.conns.first(),
				pools_el = lists.pools.first(),
				newh_c, newh_p;

			newh_c = heights.conns - ratio / (1 + ratio) * diff;
			newh_p = heights.pools - 1 / (1 + ratio) * diff;

			if (newh_c < min_height) {
				newh_p -= (min_height - newh_c);
				newh_c = min_height;
			}
			if (newh_p < min_height) {
				newh_c -= (min_height - newh_p);
				newh_p = min_height;
			}
			if (newh_c > heights.conns) {
				newh_p += (newh_c - heights.conns);
				newh_c = heights.conns;
			}

			lists.conns.setStyle('height', newh_c + 'px');
			lists.pools.setStyle('height', newh_p + 'px');
		},

		_getSize: function _getSize() {
			return {
				width: this._cel.first().clientWidth,
				height: this._cel.first().clientHeight
			};
		},
		
		_getListsAutoHeights: function _getListsAutoHeights() {
			var lists = this._els.lists_scrolls;

			lists.conns.setStyle('height', 'auto');
			lists.pools.setStyle('height', 'auto');

			return {
				conns: lists.conns.first().clientHeight,
				pools: lists.pools.first().clientHeight
			};
		},

		_getSpaceForLists: function _getSpaceForLists() {
			return (
				this._getSize().height -
				this._cel.find('#node_data_conns .header').first().offsetHeight -
				this._cel.find('#node_data_pools .header').first().offsetHeight -
				this._els.lists_scrolls.conns.first().parentNode.offsetTop
			);
		}
	});

	exports.InfobarWidget = InfobarWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
