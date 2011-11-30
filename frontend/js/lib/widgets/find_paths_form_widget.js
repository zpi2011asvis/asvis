(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal;

	var FindPathsFormWidget = Widget.create(function FindPathsFormWidget() {}, {
		signals: {
			destroyed: null,
			submitted: null,
			closed: null
		},

		_init: function _init() {
			var that = this;

			that.signals = {
				destroyed: new Signal(),
				submitted: new Signal(),
				closed: new Signal()
			};

			that._view.signals.bg_clicked.add(function () {
				that.signals.closed.dispatch();
			});

			that._view.signals.submitted.add(function (params) {
				that.destroy();
				that.signals.submitted.dispatch(params);
			});
		}
	},
	{
		multiple: false
	});

	FindPathsFormWidget.View = Widget.View.create(function FindPathsFormWidgetView() {}, {
		signals: {
			bg_clicked: null,
			submitted: null,
		},
	
		_init: function _init() {
			this.signals = {
				bg_clicked: new Signal(),
				submitted: new Signal()
			};
		},

		render: function render(data) {
			if (this._el) return;

			var that = this;
			that._cel.html(
				that._position, 
				that._tpls.render('find_paths_form', data)
			);
			that._el = that._cel.find('#find_paths_form');
			that._el.on('click', function (event) {
				// check whether it is exactly _el that was clicked
				if (event.target === that._el.first()) {
					that.signals.bg_clicked.dispatch();
					return false;
				}
			});
			that._el.find('.submit > button').on('click', function (event) {
				that.signals.submitted.dispatch({
					from:		data.from || +that._el.find('#popup_form_from').first().value,
					to:			+that._el.find('#popup_form_to').first().value,
					type:		that._el.find('#popup_form_type').first().value
				});
				event.preventDefault();
				event.stopPropagation();
			});
		},
	});

	exports.FindPathsFormWidget = FindPathsFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));