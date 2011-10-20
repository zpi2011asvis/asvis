(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget;

	var StartFormWidget = Widget.create(
		function StartFormWidget() {},
		{
			_destroy: function _destroy() {
			},

			render: function render() {
			}
		},
		{
			multiple: false
		}
	);

	StartFormWidget.Renderer = Widget.Renderer.create(
		function StartFormWidgetRenderer(el) {
			this._el = el;
		},
		{
			render: function render(data) {
			}
		}
	);

	exports.StartFormWidget = StartFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
