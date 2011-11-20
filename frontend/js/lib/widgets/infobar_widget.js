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

			var that = this;
				
			that._cel.html(
				that._position,
				that._tpls.render('infobar', data)
			);
			that._el = that._cel.find('#node_data');
		},
	});

	exports.InfobarWidget = InfobarWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
