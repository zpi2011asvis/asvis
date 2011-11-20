(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		x = global.x$;

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

		_init: function _init() {
		},
			
		render: function render(data) {
			if (this._el) return;

			var that = this,
				window = x$(global.window);
				
			that._el = x$(renderer.getEl());

			that._cel.html(
				that._position,
				that._el.first()
			);

		},
	});

	exports.InfobarWidget = InfobarWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
