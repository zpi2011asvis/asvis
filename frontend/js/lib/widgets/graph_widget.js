(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		Renderer = lib.Renderer,
		x = global.x$;

	var GraphWidget = Widget.create(function GraphWidget() {}, {
		_init: function _init() {
			var that = this;
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
			action_performed: null
		},
		_renderer: null,
		_mouse_pos: { x: 0, y: 0 }, //axis - x right, y down

		_init: function _init() {
			this.signals.resized = new Signal();
			this.signals.scrolled = new Signal();
			this.signals.dragged = new Signal();
			this.signals.action_performed = new Signal();
		},
			
		getMousePos: function () {
			return this._mouse_pos;
		},

		render: function render() {
			if (this._el) return;

			var that = this,
				renderer = new Renderer(this, {
					size: this._getSize()
				}),
				window = x$(global.window);
				
			that._el = x$(renderer.getEl());

			that._cel.html(
				that._position,
				that._el.first()
			);

			window.on('resize', function (event) {
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
				that.signals.action_performed.dispatch();
			});


			//renderer.setStructure(global.data[2], 578, true);
			//renderer.setStructure(global.data[0], 7345, true);
			renderer.setStructure(global.data[1], 7578, true);
			renderer.start();

			/**
			var s = { 1: { up: [], down: [] } };
			for (var i = 2; i < 100; ++i) {
				if (s < 50) s[1].up.push(i);
				else s[1].down.push(i);
				s[i] = { up: [], down: [] };
			};
			/**/			
			/**
			renderer.setStructure({
				structure: {
					1: { up: [2,3,4,5,6], down: [7,8,9] },
					2: { up: [], down: [] },
					3: { up: [], down: [] },
					4: { up: [], down: [] },
					5: { up: [], down: [] },
					6: { up: [], down: [] },
					7: { up: [], down: [] },
					8: { up: [], down: [] },
					9: { up: [], down: [] }
				}
			}, 1, true);
			/**
			renderer.setStructure({
				structure: s
			}, 1, true);
			/**/

			this._renderer = renderer;
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
