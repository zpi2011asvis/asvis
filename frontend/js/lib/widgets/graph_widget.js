(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		Renderer = lib.Renderer,
		x = global.x$;

	var GraphWidget = Widget.create(function GraphWidget() {}, {
		_renderer: null,
		_controls: null,
		_node_info: null,
		_block_node_info_timeout: null,
		_is_node_info_blocked: null,
		_connection_mark_id: null,
		_hovered_mark_id: null,

		_init: function _init() {
			var that = this,
				renderer = new Renderer(that._view, {
					size: that._view._getSize()
				}),
				controls = lib.widgets.GraphControlsWidget.new(that._container_el),
				node_info = lib.widgets.NodeInfoWidget.new(that._container_el);

			controls.set('settings', renderer.getSettings());
			controls.signals.settings_changed.add(function (settings) {
				renderer.setSettings(settings);
				renderer.refresh();
			});
			controls.signals.fba_clicked.add(function () {
				renderer.runFBA(5000);
			});

			that._view.signals.mouse_moved.add(function (mouse_pos) {
				node_info.onMouseMove(mouse_pos);
			});
			renderer.signals.touched_node.add(function (node_num) {
				if (!that._is_node_info_blocked)
					that.showNodeInfoFor(node_num);
			});
			renderer.signals.untouched_node.add(function (node_num) {
				that.hideNodeInfoFor(node_num);
			});

			node_info.signals.hidden.add(function () {
				that.unmarkHoveredNode();
			});

			// block info popup when dragging
			// remove blockade after stopping
			that._view.signals.dragged.add(function () {
				that._is_node_info_blocked = true;
				that._block_node_info_timeout && global.clearTimeout(that._block_node_info_timeout);

				that._block_node_info_timeout = global.setTimeout(function () {
					that._is_node_info_blocked = false;
				}, 100);
			});

			that._view._renderer = renderer;
			that._renderer = renderer;
				
			that._controls = controls;
			that._node_info = node_info;
			that._children = [ controls, node_info ];
		},

		markConnectionTo: function markConnectionTo(from, to) {
			this._connection_mark_id = this._renderer.addComponents([
				{
					class: 'line',
					style: 'marking',
					fromNode: from,
					toNode: to
				},
				{
					class: 'node',
					style: 'marking',
					forNode: to
				}
			]);
		},
		
		unmarkConnection: function unmarkConnection() {
			this._connection_mark_id && this._renderer.removeComponents(this._connection_mark_id);
			this._connection_mark_id = null;
		},

		markHoveredNode: function (node_num) {
			this._hovered_mark_id = this._renderer.addComponents([
				{
					class: 'node',
					style: 'hovered',
					forNode: node_num
				}
			]);
		},

		unmarkHoveredNode: function () {
			this._hovered_mark_id && this._renderer.removeComponents(this._hovered_mark_id);
			this._hovered_mark_id = null;
		},

		showNodeInfoFor: function showNodeInfoFor(node_num) {
			this.unmarkHoveredNode();
			this.markHoveredNode(node_num);
			this._node_info.set('node_num', node_num);
			this._node_info.set('depth', this._data.depth);
			this._node_info.set('node_meta', this._data.nodes_meta && this._data.nodes_meta[node_num]);
			this._node_info.show();
		},

		hideNodeInfoFor: function hideNodeInfoFor(node_num) {
			this._node_info.hide();
		}
	},
	{
		multiple: false
	});

	GraphWidget.View = Widget.View.create(function GraphWidgetView() {}, {
		signals: {
			resized: null,
			scrolled: null,
			dragged: null,
			mouse_moved: null,
			action_performed: null
		},
		_renderer: null,
		_mouse_pos: { x: 0, y: 0 }, //axis - x right, y down

		_init: function _init() {
			this.signals = {
				resized: new Signal(),
				scrolled: new Signal(),
				dragged: new Signal(),
				mouse_moved: new Signal(),
				action_performed: new Signal()
			};
			this._mouse_pos = { x: 0, y: 0 };
		},

		destroy: function destroy() {
			this._sDestroy();
			this._renderer.destroy();
			this._renderer = this.signals = null;
		},
			
		getMousePos: function () {
			return this._mouse_pos;
		},

		render: function render(data) {
			if (this._el) return;

			var that = this,
				renderer = that._renderer,
				window_el = x$(global.window);
				
			that._el = x$(renderer.getEl());

			that._cel.html(
				that._position,
				that._el.first()
			);
			
			that._addEvent(window_el, 'resize', function (event) {
				that.signals.resized.dispatch(that._getSize());
				that.signals.action_performed.dispatch();
			});

			that._el.onscroll(function (event, down) {
				that.signals.scrolled.dispatch(down, that._mouse_pos);
				that.signals.action_performed.dispatch();
			});

			that._el.ondrag(function (event, change) {
				event.preventDefault();
				that.signals.dragged.dispatch(change, { ctrl: event.ctrlKey });
				that.signals.action_performed.dispatch();
			});

			that._el.on('mousemove', function (event) {
				that._mouse_pos = { x: event.layerX, y: event.layerY };
				that.signals.mouse_moved.dispatch(that._mouse_pos);
				that.signals.action_performed.dispatch();
			});

			global.app.signals.graph_rendering.started.dispatch(that);
			
			setTimeout(function () {
				renderer.setStructure(data['graph'], data['root'], true);
				renderer.start();
				global.app.signals.graph_rendering.ended.dispatch(that);
			}, 1);
		},

		_getSize: function _getSize() {
			return {
				width: this._cel.first().clientWidth,
				height: this._cel.first().clientHeight
			};
		}
	});

	exports.GraphWidget = GraphWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
