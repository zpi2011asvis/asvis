(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget;

	var StartFormWidget = Widget.create(function StartFormWidget() {}, {
		render: function render() {
		}
	},
	{
		multiple: true
	});

	StartFormWidget.Renderer = Widget.Renderer.create(function StartFormWidgetRenderer() {}, {
		init: function init(el) {
			this._el = el;
		}
	},
	{
		render: function render(data) {
		}
	});

	exports.StartFormWidget = StartFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
