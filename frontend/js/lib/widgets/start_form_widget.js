(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal;

	var StartFormWidget = Widget.create(function StartFormWidget() {}, {
		signals: {
			destroyed: null,
			submitted: null,
			closed: null
		},

		_init: function _init() {
			var that = this,
				num_auto = lib.widgets.AutocompleterWidget.new();				

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
			
			num_auto.set('for', '#popup_form_num');
			that._children = [ num_auto ];
		}
	},
	{
		multiple: false
	});

	StartFormWidget.View = Widget.View.create(function StartFormWidgetView() {}, {
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
				that._tpls.render('start_form', data)
			);
			that._el = that._cel.find('#start_form');
			that._el.on('click', function (event) {
				// check whether it is exactly _el that was clicked
				if (event.target === that._el.first()) {
					that.signals.bg_clicked.dispatch();
					return false;
				}
			});
			that._el.find('.submit > button').on('click', function (event) {
				that.signals.submitted.dispatch({
					number: +that._el.find('input[name=number]').first().dataset.value,
					depth: +that._el.find('input[name=depth]').first().value
				});
				event.preventDefault();
				event.stopPropagation();
			});
		},
	});

	exports.StartFormWidget = StartFormWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
