(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		x = global.x$,
		widget_id = 0;

	var AutocompleterWidget = Widget.create(function AutocompleterWidget() {}, {
		MIN_LEN: 2,
		_value: '',
		_selected: 0,
		_size: 0,
		_opts: null,

		_init: function _init() {
			var that = this;

			that._data.id = widget_id;
			widget_id++;

			that._view.signals.enter_pressed.add(function (value) {
				that._confirmSelected();
			});
			that._view.signals.up_pressed.add(function (value) {
				that._select(-1);
			});
			that._view.signals.down_pressed.add(function (value) {
				that._select(1);
			});
			that._view.signals.changed.add(function (value) {
				value = value.trim();
				var old_value = that._value;
				that._value = value;

				if (value.length >= that.MIN_LEN && value !== old_value) {
					that._search();
				}

			});
			that._view.signals.blured.add(function () {
				that._closePopup();
			});
		},

		_confirmSelected: function _confirmSelected() {
			if (this._size === 0) {
				return;
			}
			var num = Object.keys(this._opts)[this._selected];

			this._view.updateInput(this._selected, num);
			this._closePopup();

			this._selected = 0;
			this._size = 0;
			this._opts = null;
		},

		_select: function _select(dir) {
			var new_num = dir + this._selected;

			if (dir > 0) {
				new_num = Math.min(this._size - 1, new_num);
			}
			else {
				new_num = Math.max(0, new_num);
			}

			this._selected = new_num;
			this._view.select(new_num);
		},

		_closePopup: function _closePopup() {
			this._view.close();
		},

		_search: function _search() {
			var that = this;

			that._view.loading(true);
			that._selected = 0;
			that._size = 0;

			global.app.db.get('nodes/find', {
				number: that._value
			})
			(function (data) {
				that._opts = data;
				that._size = Object.keys(data).length;
				that._view.loading(false);
				that._view.updateOpts(data);
				that._view.select(that._selected);
			}).end(global.app.err);
		}
	},
	{
		multiple: true
	});

	AutocompleterWidget.View = Widget.View.create(function AutocompleterWidgetView() {}, {
		signals: {
			enter_pressed: null,
			up_pressed: null,
			down_pressed: null,
			changed: null,
			blured: null
		},
	
		_init: function _init() {
			this.signals = {
				enter_pressed: new Signal(),
				up_pressed: new Signal(),
				down_pressed: new Signal(),
				changed: new Signal(),
				blured: new Signal()
			};
		},

		render: function render(data) {
			if (this._el) return;

			var that = this,
				text_input_el,
				hidden_input_el;

			hidden_input_el = x(data.for);
			hidden_input_el.html(
				'after', 
				that._tpls.render('autocompleter', data)
			);
			that._el = x('#autocompleter_' + data.id);
			hidden_input_el.remove().attr('type', 'hidden');
			that._el.html('top', hidden_input_el);

			text_input_el = x('#autocompleter_query_' + data.id);
			text_input_el.first().value = hidden_input_el.first().value;
			hidden_input_el.first().dataset.value = hidden_input_el.first().value;

			text_input_el.on('keyup', function (event) {
				var v = text_input_el.first().value;

				that._hidden_input_el.first().value = v;
				that._hidden_input_el.first().dataset.value = v;

				if (event.keyCode === 13) {
					that.signals.enter_pressed.dispatch(v);
					event.preventDefault();
				}
				else if (event.keyCode === 38) {
					that.signals.up_pressed.dispatch(v);
					event.preventDefault();
				}
				else if (event.keyCode === 40) {
					that.signals.down_pressed.dispatch(v);
					event.preventDefault();
				}
				else {
					that.signals.changed.dispatch(v);
				}
			});
			text_input_el.on('blur', function (event) {
				that.signals.blured.dispatch();
			});

			that._text_input_el = text_input_el;
			that._hidden_input_el = hidden_input_el;
			that._opts_el = that._el.find('.opts');
		},

		loading: function loading(yes_no) {
			if (yes_no) {
				this._text_input_el.addClass('loading');
			}
			else {
				this._text_input_el.removeClass('loading');
			}
		},

		updateOpts: function updateOpts(opts) {
			var that = this,
				opts_el = that._opts_el,
				opts_html = '';

			Object.keys(opts).forEach(function (opt_num) {
				opts_html += '<li>(#' + opt_num + ') ' + opts[opt_num].name + '</li>';
			});

			opts_el.html(opts_html);
			opts_el.addClass('opened');
		},

		close: function close() {
			this._opts_el.html('').removeClass('opened');
		},

		select: function select(index) {
			this._opts_el.find('.selected').removeClass('selected');
			x(this._opts_el.find('li')[index]).addClass('selected');
		},

		updateInput: function updateInput(index, value) {
			this._text_input_el.first().value = 
				x(this._opts_el.find('li')[index]).first().innerHTML;

			this._hidden_input_el.first().value = value; // wtf - this is unreadable in another widget
			this._hidden_input_el.first().dataset.value = value;

		}
	});

	exports.AutocompleterWidget = AutocompleterWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
