(function (exports, global, lib) {
	'use strict';

	var MESSAGES = {
		data_loading: 'Ładowanie...',
		graph_rendering: 'Renderowanie...',
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

		// when caller is different than the one started
		if (!~i) {
			throw new Error('Couldn\'t find caller ' + caller.toString());
		}

		callers.splice(i, 1); //remove element from array
		_callers_number--;

		if (_callers_number === 0) {
			_hide();
		}
		else {
			// if some is still waiting update label
			// to the first one
			i = 0;
			while (Object.keys(_waiters)[i].length === 0) { i++ };
			_el.html(MESSAGES[Object.keys(_waiters)[i]]);
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
		init: function init(signals, el) {
			var sname, signal;

			_inited = true;
			_el = el;

			for (sname in signals) {
				signal = signals[sname];

				_waiters[sname] = [];
				signal.started.add(_start.bind(null, sname));
				signal.ended.add(_end.bind(null, sname));
			}
		},

	};

	exports.Flash = Flash;

}.call({}, this.app.lib, this, this.app.lib));
