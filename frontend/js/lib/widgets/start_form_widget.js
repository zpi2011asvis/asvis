(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget;

	var StartFormWidget = Widget.create(
		function StartFormWidgets() {},
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

	exports.StartFormWidget = StartFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
