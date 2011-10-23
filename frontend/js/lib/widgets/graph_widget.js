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
		},
		_renderer: null,

		_init: function _init() {
			this.signals.resized = new Signal();
			this.signals.scrolled = new Signal();
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
			});

			that._el.onscroll(function (event, down) {
				that.signals.scrolled.dispatch(down);
			});


			renderer.start();
			renderer.setStructure(global.data[2]);

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
