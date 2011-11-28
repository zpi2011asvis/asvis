(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal;

	var AutocompleterWidget = Widget.create(function AutocompleterWidget() {}, {
		_init: function _init() {
		}
	},
	{
		multiple: false
	});

	AutocompleterWidget.View = Widget.View.create(function AutocompleterWidgetView() {}, {
		signals: {
		},
	
		_init: function _init() {
			this.signals = {
			};
		},

		render: function render() {
			if (this._el) return;

			var that = this;
			that._cel.html(
				that._position, 
				that._tpls.render('autocompleter')
			);
			that._el = that._cel.find('#autocompleter');
		},
	});

	exports.AutocompleterWidget = AutocompleterWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
