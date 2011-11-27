(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		Renderer = lib.Renderer,
		x = global.x$;

	var GraphWidget = Widget.create(function GraphWidget() {}, {
		_connection_mark_id: null,
		_renderer: null,

		_init: function _init() {
			var renderer = new Renderer(this._view, {
					size: this._view._getSize()
				}),
				controls = lib.widgets.GraphControlsWidget.new(this._container_el);

			controls.set('settings', renderer.getSettings());
			controls.signals.settings_changed.add(function (settings) {
				renderer.setSettings(settings);
				renderer.refresh();
			});
			controls.signals.fba_clicked.add(function () {
				renderer.runFBA(5000);
			});

			this._view._renderer = renderer;
			this._renderer = renderer;
				
			this._controls = controls;
			this._children = [ this._controls ];
		},

		markConnectionTo: function markConnectionTo(from, to) {
			this._connection_mark_id = this._view._renderer.addComponents([
				{
					class: 'line',
					type: 'marking',
					fromNode: from,
					toNode: to
				},
				{
					class: 'node',
					type: 'marking',
					forNode: to
				}
			]);
		},
		
		unmarkConnection: function unmarkConnection() {
			this._view._renderer.removeComponent(this._connection_mark_id);
			this._connection_mark_id = null;
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
