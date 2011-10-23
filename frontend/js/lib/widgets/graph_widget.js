(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		Renderer = lib.Renderer;

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
		},
		_renderer: null,

		_init: function _init() {
		},

		render: function render() {
			if (this._el) return;

			var that = this,
				renderer = new Renderer();

			that._cel.html(
				that._position, 
				renderer.getEl()
			);
			renderer.start();
			renderer.setStructure(global.data[2]);

			this._renderer = renderer;
		},
	});

	exports.GraphWidget = GraphWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
