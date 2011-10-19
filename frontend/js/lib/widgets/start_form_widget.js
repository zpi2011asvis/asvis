(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget;

	var StartFormWidgets = Widget.create(
		function StartFormWidgets() {},
		{
			_destroy: function () {
			}
		},
		{
			multiple: false
		}
	);

	exports.StartFormWidgets = StartFormWidgets;
}.call({}, this.app.lib.widgets, this, this.app.lib));
