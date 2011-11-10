(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3;

	/*
	 * Forced based algorithm for drawing graphs in an aesthetically pleasing way
	 */
	var FBA = function FBA(root, graph, connections) {
		// consts
		var STEPS_AT_ONCE = 5,
			SPRING_LEN = 50,
			CHARGE = 100,				// for coulomb's law
			DUMPING = 1;

		// properties
		var _root,
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
		//	for (var i = 0; i < STEPS_AT_ONCE; ++i) {
				_step();
				
				if (+new Date() > _end_time) {
			//		i = STEPS_AT_ONCE; // break - too long
				}
		//	}

			if (+new Date() < _end_time) {
				setTimeout(_run, 100);
			}
			else {
				global.DEBUG && console.log('FBA steps done: ' + _steps_done);
			}
		};

		_step = function _step() {
			var i, il, j,
				num, node, pos, conns, velocity,
				num2, node2, pos2,
				dx, dy, dz,
				vx, vy, vz,
				rsq, force, f,
				start_time = +new Date();

			for (i = 0, il = _graph_nums.length; i < il; ++i) {
				num = _graph_nums[i];
				node = _graph_arr[i];
				pos = node.pos;
				conns = node.conns;
				velocity = _velocities[i];
				vx = velocity.x;
				vy = velocity.y;
				vz = velocity.z;
				
				for (j = 0; j < il; ++j) {
					if (i !== j) {
						num2 = _graph_nums[j];
						node2 = _graph_arr[j];
						pos2 = node2.pos;
						dx = pos2.x - pos.x;
						dy = pos2.y - pos.y;
						dz = pos2.z - pos.z;
						rsq = Math.sqrt(dx*dx + dy*dy + dz*dz);

						// nodes are connected
						if (conns.indexOf(num2) > -1) {
							force = 0;
							//force = 1;
							//force = (rsq - SPRING_LEN) / 2;
							//console.log(i, j, rsq, force);
						}
						else {
							force = -((node.weight * node2.weight) / (rsq * rsq)) * CHARGE;
						}

						//f = force / rsq;
						vx = dx * force;
						vy = dy * force;
						vz = dz * force;
					}
				}
				pos.x += vx;
				pos.y += vy;
				pos.z += vz;
				velocity.x = vx * DUMPING;
				velocity.y = vy * DUMPING;
				velocity.z = vz * DUMPING;
			}

			// set root in center
			_root.pos.x = 0;
			_root.pos.y = 0;
			_root.pos.z = 0;

			_steps_done += 1;
			global.DEBUG2 && console.log('FBA step took: ' + (new Date() - start_time) + 'ms');
		};

		/*
		 * Init ----------------------------------------------------------------
		 */
		_root = graph[root];
		_graph = graph;
		_connections = connections;
		_graph_nums = Object.keys(graph).map(function (v) { return +v; });
		_velocities = [];
		_graph_arr = _graph_nums.map(function (num) {
			_velocities.push(new T_Vector3());
			return _graph[num];
		});
	};

	exports.FBA = FBA;

}.call({}, this.app.lib, this, this.app.lib));
