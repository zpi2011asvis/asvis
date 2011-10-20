(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal;

	var StartFormWidget = Widget.create(function StartFormWidget() {}, {
		_init: function _init() {
			this._view.signals.bg_clicked.add(this.destroy.bind(this));
		}
	},
	{
		multiple: false
	});

	StartFormWidget.View = Widget.View.create(function StartFormWidgetView() {}, {
		signals: {
			bg_clicked: null
		},
	
		_init: function _init() {
			this.signals = {
				bg_clicked: new Signal()
			};
		},

		render: function render() {
			if (this._el) return;

			var that = this;
			that._cel.html(
				that._position, 
				that._tpls.render('start_form')
			);
			that._el = that._cel.find('#start_form');
			that._el.on('click', function (event) {
				// check whether it is exactly _el that was clicked
				if (event.target === that._el.first()) {
					that.signals.bg_clicked.dispatch();
					return false;
				}
			});
		},
	},
	{
	});

	exports.StartFormWidget = StartFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
