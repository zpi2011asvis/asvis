(function (exports, global, lib) {
	'use strict';

	var Signal = global.signals.Signal;

	var GodsFinger = function GodsFinger(camera) {
		var that = this,
			_camera,
			_started = false,
			_touched = null,
			_deferred_timeout = null;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.signals = {
			touched: new Signal(),
			untouched: new Signal()
		};

		this.stop = function stop() {
			_started = false;
		}

		this.start = function start() {
			_started = true;
		};

		this.onMouseMove = function onMouseMove(mouse_pos) {
			if (!_started) return;
	
			// don't check if frequently called
			_deferred_timeout && global.clearTimeout(_deferred_timeout);
	
			_deferred_timeout = global.setTimeout(function () {
				var ray = _camera.getRayForMousePos(mouse_pos),
					c = THREE.Collisions.rayCastNearest(ray);

				if (c !== _touched) {
					if (_touched) that.signals.untouched.dispatch(_touched);
					if (c) that.signals.touched.dispatch(c);
					
					_touched = c;
				}
			}, 25);
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_camera = camera;
	};

	exports.GodsFinger = GodsFinger;

}.call({}, this.app.lib, this, this.app.lib));
