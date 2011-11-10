(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3;

	/*
	 * Forced based algorithm for drawing graphs in an aesthetically pleasing way
	 */
	var FBA = function FBA(vertices) {
		// consts

		// properties
		var _velocities,
			_vertices,
			_start_time,
			_end_time;

		// methods
		var _step;

		/*
		 * Publics -------------------------------------------------------------
		 */

		/*
		 * @param max_times (ms)
		 */
		this.run = function run(max_time) {
			_start_time = +new Date();
			_end_time = _start_time + max_time;
			_step();
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_step = function _step() {


			if (+new Date() < _end_time) {
				setTimeout(_step, 1);
			}
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_vertices = vertices;
		_velocities = _vertices.map(function () {
			return new T_Vector3();
		});
	};

	exports.FBA = FBA;

}.call({}, this.app.lib, this, this.app.lib));
