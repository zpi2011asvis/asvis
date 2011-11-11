(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3;

	/*
	 * Forced based algorithm for drawing graphs in an aesthetically pleasing way
	 */
	var FBA = function FBA(root, graph) {
		// consts
		var STEPS_AT_ONCE = 2,
			SPRING_LEN = 50,
			SPRING_FORCE = 0.001,
			CHARGE = 0.005,				// for coulomb's law
			DAMPING = 0.1;

		// properties
		var _root,
			_root_index,
			_graph,
			_connections,
			_graph_arr,		// see: http://jsperf.com/for-in-loop-vs-for-with-keys-array
			_graph_nums,	// performance boost while iterating over object keys array
			_velocities,
			_start_time,
			_end_time,
			_steps_done;

		// methods
		var _step,
			_run;

		/*
		 * Publics -------------------------------------------------------------
		 */

		/*
		 * @param max_times (ms)
		 */
		this.run = function run(max_time) {
			_start_time = +new Date();
			_end_time = _start_time + max_time;
			_steps_done = 0;
			_run();
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
				global.DEBUG && console.log('FBA steps done: ' + _steps_done);
			}
		};

		_step = function _step() {
			var i, il, j,
				node, pos, conns, velocity, weight,		// current node data
				node2, pos2, weight2,					// second node data
				dx, dy, dz,							// node2.pos - node.pos
				nfx, nfy, nfz,						// current node net force
				a, b, c,							// helpers
				start_time = +new Date();

			for (i = il = _graph_nums.length; i--;) {
				node = _graph_arr[i];
				pos = node.pos;
				conns = node.conns;
				weight = node.weight;
				velocity = _velocities[i];
				nfx = nfy = nfz = 0;
	
				// repulsion
				for (j = il; j--;) {
					if (i !== j) {
						node2 = _graph_arr[j];
						pos2 = node2.pos;
						weight2 = node2.weight;

						dx = pos2.x - pos.x;
						dy = pos2.y - pos.y;
						dz = pos2.z - pos.z;
						// coulombs factor q*w1*w2 / r^2
						a = CHARGE * (weight * weight2) / (dx*dx + dy*dy + dz*dz);

						nfx -= dx * a;
						nfy -= dy * a;
						nfz -= dz * a;
					}
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

				velocity.x = DAMPING * (velocity.x + nfx);
				velocity.y = DAMPING * (velocity.y + nfy);
				velocity.z = DAMPING * (velocity.z + nfz);
			}

			// update positions
			for (i = _graph_nums.length; i--;) {
				pos = _graph_arr[i].pos;
				velocity = _velocities[i];

				pos.x += velocity.x;
				pos.y += velocity.y;
				pos.z += velocity.z;
			}

			_steps_done += 1;
			global.DEBUG2 && console.log('FBA step took: ' + (new Date() - start_time) + 'ms');
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_graph = graph;
		_graph_nums = Object.keys(graph).map(function (v) { return +v; });
		_velocities = [];
		_graph_arr = _graph_nums.map(function (num) {
			_velocities.push(new T_Vector3());
			return _graph[num];
		});
		_root = graph[root];
		_root_index = _graph_nums.indexOf(+root);
	};

	exports.FBA = FBA;

}.call({}, this.app.lib, this, this.app.lib));
