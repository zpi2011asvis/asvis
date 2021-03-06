(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3,
		Signal = global.signals.Signal;

	/*
	 * Forced based algorithm for drawing graphs in an aesthetically pleasing way
	 */
	var FBA = function FBA(root, graph, mass_centers) {
		// consts
		var STEPS_AT_ONCE = 2,
			SPRING_LEN = 75,
			SPRING_FORCE = 0.01,			// for hook's law
			CHARGE = 1.5,						// for coulomb's law
			DAMPING = 0.9,
			VMAX = 3.0;

		// properties
		var that = this,
			_started = false,
			_root,
			_root_index,
			_graph,
			_mass_centers,  // nodes that should stay in place
			_graph_arr,		// see: http://jsperf.com/for-in-loop-vs-for-with-keys-array
			_graph_nums,	// performance boost while iterating over object keys array
			_velocities,
			_net_forces,
			_positions,
			_weights,
			_connections,
			_start_time,
			_end_time,
			_steps_done;

		// methods
		var _step,
			_run;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.signals = {
			started: new Signal(),
			ended: new Signal()
		};

		/*
		 * @param max_times (ms)
		 */
		this.run = function run(max_time) {
			if (_started) return;

			_started = true;
			global.app.signals.graph_rendering.started.dispatch(FBA);
			this.signals.started.dispatch();

			_start_time = +new Date();
			_end_time = _start_time + max_time;
			_steps_done = 0;
			_run();
		};

		this.stop = function stop() {
			_end_time = 0;
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_run = function _run() {
			for (var i = 0; i < STEPS_AT_ONCE; ++i) {
				_step();
				
				if (+new Date() > _end_time) {
					i = STEPS_AT_ONCE; // break - too long
				}
			}

			if (+new Date() < _end_time) {
				setTimeout(_run, 1);
			}
			else {
				_started = false;
				global.DEBUG && console.log('FBA steps done: ' + _steps_done);
				global.app.signals.graph_rendering.ended.dispatch(FBA);
				that.signals.ended.dispatch();
			}
		};

		_step = function _step() {
			var i, il, j, k,							// indexes (int)
				pos, conns, weight,						// current node data
				pos2, weight2,							// second node data
				dx, dy, dz,								// node2.pos - node.pos
				nfx, nfy, nfz,							// current node net force
				a, b, c,								// helpers (float)
				start_time = +new Date();

			for (i = 0, il = _graph_nums.length; i < il; ++i) {
				if (!_mass_centers[i]) {
					pos = _positions[i];
					conns = _connections[i];
					weight = _weights[i];

					k = i * 3;
					nfx = _net_forces[k++];
					nfy = _net_forces[k++];
					nfz = _net_forces[k];
		
					// repulsion - 95% of CPU in profiler
					// maybe merge attraction loop into this loop?
					for (j = i + 1, k = 3 * j; j < il; ++j) {
						pos2 = _positions[j];
						weight2 = _weights[j];

						dx = pos2.x - pos.x;
						dy = pos2.y - pos.y;
						dz = pos2.z - pos.z;
						// coulombs factor q*w1*w2 / r^2
						a = CHARGE * (weight * weight2) / (dx*dx + dy*dy + dz*dz);

						nfx -= dx * a;
						nfy -= dy * a;
						nfz -= dz * a;
				
						_net_forces[k++] += dx * a;
						_net_forces[k++] += dy * a;
						_net_forces[k++] += dz * a;
					}

					// attraction
					for (j = conns.length; j--;) {
						pos2 = _graph[conns[j]].pos;

						dx = pos2.x - pos.x;
						dy = pos2.y - pos.y;
						dz = pos2.z - pos.z;
						// -k*x
						a = SPRING_FORCE * (Math.sqrt(dx*dx + dy*dy + dz*dz) - SPRING_LEN);

						nfx += dx * a;
						nfy += dy * a;
						nfz += dz * a;
					}
		
					k = 3 * i;
					a = DAMPING * (_velocities[k] + nfx / weight);
					_velocities[k++] = a > 0 ? Math.min(VMAX, a) : Math.max(-VMAX, a);

					a = DAMPING * (_velocities[k] + nfy / weight);
					_velocities[k++] = a > 0 ? Math.min(VMAX, a) : Math.max(-VMAX, a);

					a = DAMPING * (_velocities[k] + nfz / weight);
					_velocities[k] = a > 0 ? Math.min(VMAX, a) : Math.max(-VMAX, a);


					k = 3 * i;
					_net_forces[k++] = 0;
					_net_forces[k++] = 0;
					_net_forces[k] = 0;
				}
			}

			// update positions
			for (i = 0, il = _graph_nums.length, k = 0; i < il; ++i) {
				pos = _positions[i];

				pos.x += _velocities[k++];
				pos.y += _velocities[k++];
				pos.z += _velocities[k++];
			}

			_steps_done += 1;
			global.DEBUG2 && console.log('FBA step took: ' + (new Date() - start_time) + 'ms');
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_graph = graph;
		_graph_nums = Object.keys(graph).map(function (v) { return +v; });

		_velocities = new Float32Array(3 * _graph_nums.length);
		_net_forces = new Float32Array(3 * _graph_nums.length);
		_positions = [];
		_weights = new Uint16Array(_graph_nums.length);
		_connections = [];
		
		_graph_arr = _graph_nums.map(function (num, i) {
			var obj = _graph[num];

			_velocities[i * 3] = 0;
			_velocities[i * 3 + 1] = 0;
			_velocities[i * 3 + 2] = 0;
			_net_forces[i * 3] = 0;
			_net_forces[i * 3 + 1] = 0;
			_net_forces[i * 3 + 2] = 0;

			_positions.push(obj.pos);
			_weights[i] = obj.weight;
			_connections.push(obj.conns);

			return obj;
		});
		_root = _graph[root];
		_root_index = _graph_nums.indexOf(+root);
		// switch mass_center to based on grah_nums indexes
		// do it for performance reasons
		_mass_centers = {};
		Object.keys(mass_centers).forEach(function (v) {
			_mass_centers[_graph_nums.indexOf(+v)] = true;
		});
	};

	exports.FBA = FBA;

}.call({}, this.app.lib, this, this.app.lib));
