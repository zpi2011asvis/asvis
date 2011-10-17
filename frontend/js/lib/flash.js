(function (exports, global) {
	'use strict';

	var MESSAGES = {
		data_loading: 'Ładowanie...'
	};

	var x = global.x$;

	var _inited = false,
		//object of signal names as keys and arrays of callers as values
		//callers may be useful for debugging
		_waiters = {},
		_callers_number = 0,
		_opened = false,
		//html element with flash
		_el;

	var _start = function _start(sname, caller) {
		_waiters[sname].push(caller);
		_callers_number++;
		_open(sname);
	};

	var _end = function _end(sname, caller) {
		var callers = _waiters[sname],
			i = callers.indexOf(caller);
	
		//TODO what's when caller is different than one started?
		callers.splice(i, 1); //remove
		_callers_number--;

		if (_callers_number === 0) {
			_hide();
		}
	};

	var _open = function _open(sname) {
		if (_opened) return;

		_opened = true;
		_el.addClass('opened');
		_el.html(MESSAGES[sname]);
	};

	var _hide = function _hide() {
		if (!_opened) return;
		
		_opened = false;
		_el.removeClass('opened');
	};

	var Flash = {
		init: function (signals, el) {
			var sname, signal;

			_inited = true;
			_el = x(el);

			for (sname in signals) {
				signal = signals[sname];

				_waiters[sname] = [];
				signal.started.on(_start.bind(sname));
				signal.ended.on(_end.bind(sname));
			}
		},

	};

	exports.Flash = Flash;

}.call({}, this.app.lib, this));
