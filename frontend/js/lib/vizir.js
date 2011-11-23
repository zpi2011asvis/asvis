(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3,
		T_Matrix4 = T.Matrix4,
		FBA = lib.FBA,
		uniq = global.util.arrayUniq,
		deg2Rad = global.util.deg2Rad,
		Signal = global.signals.Signal;
	
	var Vizir = function Vizir() {
		// consts
		var BASE = 75,				// base length
			A360 = Math.PI * 2,
			AUTO_FBA_MAX_NODES = 3000,
			AUTO_FBA_WORK_TIME = 5000,
			AUTO_FBA_DELAY = 250,
			MASS_CENTER_BASE_FACTOR = 1.5;

		// properties
		var that = this,
			_root,
			_graph,
			_distance_order,
			_order,
			_nodes_done = [],	// indexes of recalculated vertices
			_edges_done = {},	// 'num1_num2': true for recalculated edge
			_vertices = [],		// ordered as in _order
			_edges = [],
			_dirty = false,
			_fba;

		// methods
		var _nver,
			_nvec,
			_recalculatePositions,
			_pushEdge,
			_hasEdge,
			_generateVEObjects,
			_recursiveVertexPos,
			_runRecursiveVertexPos,
			_calculateInclinationAngle,
			_calculateRotationAngle;
	
		/*
		 * Publics -------------------------------------------------------------
		 */

		this.signals = {
			started: new Signal(),
			ended: new Signal()
		};

		this.destroy = function destroy() {
			_fba && _fba.stop();
		};

		this.setGraph = function setGraph(graph) {
			_dirty = true;
			_graph = graph.structure;
			_distance_order = graph.distance_order;
			_order = graph.weight_order;
			return this;
		};

		this.setRoot = function setRoot(root) {
			_dirty = true;
			_root = root;
			return this;
		};

		this.getVertices = function getVertices() {
			if (_dirty) _recalculatePositions();

			return _vertices;
		};

		this.getEdges = function getEdges() {
			if (_dirty) _recalculatePositions();

			return _edges;
		};

		this.clear = function clear() {
			_graph = null;
			_root = null;
			_order = null;
			_distance_order = null;
			_vertices = [];
			_edges = [];
			_nodes_done = [];
			_edges_done = {};
			_dirty = false;
			return this;
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_nver = function _nver(x, y, z) {
			return new T_Vertex(_nvec(x, y, z));
		};

		_nvec = function _nvec(x, y, z) {
			return new T_Vector3(x, y, z);
		};

		_recalculatePositions = function _recalculatePositions() {
			var d = +new Date(),
				node,
				mc = {},
				signal,
				mc_base = BASE * MASS_CENTER_BASE_FACTOR;
			
			// set mass centers
			node = _graph[_order[0]];
			node.pos = _nvec(mc_base, mc_base / 2, 0);
			mc[_order[0]] = true;

			if (_order.length > 1) {
				node = _graph[_order[1]];
				node.pos = _nvec(-mc_base, mc_base / 2, 0);
				mc[_order[1]] = true;
			}
			if (_order.length > 2) {
				node = _graph[_order[2]];
				node.pos = _nvec(0, -mc_base, 0);
				mc[_order[2]] = true;
			}
			
			_runRecursiveVertexPos([_order[0]]);

			_generateVEObjects();
			global.DEBUG && console.log('Recalculating took: ' + (new Date() - d) + 'ms (for ' + _vertices.length + ' vertices)');
			_dirty = false;

			_fba = new FBA(_root, _graph, mc);

			// forward signals
			signal = that.signals.started;
			_fba.signals.started.add(signal.dispatch, signal);
			signal = that.signals.ended;
			_fba.signals.ended.add(signal.dispatch, signal);

			if (_order.length < AUTO_FBA_MAX_NODES) {
				setTimeout(_fba.run.bind(_fba, AUTO_FBA_WORK_TIME), AUTO_FBA_DELAY);
			}
		};

		_runRecursiveVertexPos = function _runRecursiveVertexPos(queue) {
			queue = [ queue ];
			while (queue.length > 0) {
				var args = queue.shift(),
					todo = _recursiveVertexPos.apply(null, args);

				todo && (queue = queue.concat(todo));
			}
		};

		/*
		 * @param num {Integer} 
		 * @param pos {Vector3} [x,y,z]
		 * @param vector {Vector3} child_pos - parent_pos
		 * @param depth {Integer} how deep go in this direction
		 *
		 * TODO
		 * * something is wrong with inclining from tree direction
		 * * depth gives poor results - traverse while vertex has big
		 * number of unnodes_done children
		 */
		_recursiveVertexPos = function _recursiveVertexPos(num, pos, vector) {
			var node = _graph[num],				// node
				conns = node.out.concat(node.in),
				connsl = conns.length,
				current_pos,
				new_pos,
				new_num,
				todo = [],						// queue for _runRecursiveVertexPos
				rotated = 0,					// already rotated in current surface 
				incl_angle, rot_angle,
				m_incl, m_rot;

			// this is done twice (also before node was added to the queue)
			// because of order in BFS
			if (_nodes_done.indexOf(num) > -1) {
				return;
			}

			// position already set (for the mass centers)
			// so use it
			if (node.pos) {
				pos = node.pos.clone();
				current_pos = node.pos;
				vector = node.pos.clone().multiplyScalar(1 / MASS_CENTER_BASE_FACTOR);
			}
			else {
				current_pos = pos.clone();
				node.pos = current_pos;
				node.conns = [];
			}
			_vertices.push(current_pos);
			_nodes_done.push(num);
			
			// remove duplicated connections (bidirectional)
			// here (not before) because of performance
			// -- do this after upper returns
			conns = node.conns = uniq(conns);
			connsl = conns.length;
			// TODO for calculating angles use number of nodes already not done
			incl_angle = _calculateInclinationAngle(connsl, node.out, node.in);
			rot_angle = _calculateRotationAngle(incl_angle);

			// generating matrix for "circular" rotations
			var m_rot = new T_Matrix4();
			m_rot.setRotationAxis(vector.clone().normalize(), rot_angle);
		
			//generating vector inclined from tree generation direction
			var m_incl = new T_Matrix4(),
				p = _nvec(0, 1, 0).crossSelf(vector).normalize();

			// if vector was (0,1,0) then cross product is (0,0,0)
			// so try with other axis rotation
			if (p.lengthSq() === 0) {
				p = _nvec(0, 0, 1).crossSelf(vector).normalize();
			}
			m_incl.setRotationAxis(p, incl_angle);
			m_incl.multiplyVector3(vector);
			
			for (var i = 0; i < connsl; ++i) {
				new_num = conns[i];

				if (!_hasEdge(num, new_num)) {
					// traversing for the first time
					if (_nodes_done.indexOf(new_num) === -1) {
						new_pos = pos.clone().addSelf(vector);
						
						// add child to queue
						todo.push([conns[i], new_pos, vector.clone()]);

						_pushEdge(num, new_num);
				
						// calculate new position on sphere
						m_rot.multiplyVector3(vector);
						rotated += rot_angle;
						if (rotated >= A360) {
							m_incl.multiplyVector3(vector);
							rotated = 0;
						}
					}
					// traversing this vertex again (push only edge)
					else {
						_pushEdge(num, new_num);
					}
				}
			}

			return todo;
		};

		_pushEdge = function _pushEdge(num1, num2) {
			// add in both directions - simpler to search them
			_edges_done[num1 + '_' + num2] = true;
			_edges_done[num2 + '_' + num1] = true;

			_edges.push(num1, num2);
		};

		_hasEdge = function _hasEdge(num1, num2) {
			// TODO maybe _edges_done search will be faster?
			return (
				_nodes_done.indexOf(num1) > -1 &&
				_nodes_done.indexOf(num2) > -1
			);
		};

		_generateVEObjects = function _generateVEObjects() {
			var edges = [],
				vertices = [];

			for (var i = 0, il = _edges.length; i < il; ++i) {
				edges[i] = new T_Vertex(_graph[_edges[i]].pos);
			}

			for (var i = 0, il = _vertices.length; i < il; ++i) {
				vertices[i] = new T_Vertex(_vertices[i]);
			}

			_edges = edges; //fast swap
			_vertices = vertices; //fast swap
		};

		_calculateInclinationAngle = function (connsl, conns_out, conns_in) {
			// only one neighbour
			if (
					connsl === 1 ||
					(connsl === 2 && conns_out[0] === conns_in[0])
			) {
				return 0;
			}

			return deg2Rad(75 / Math.sqrt(connsl) + 4.9); 
		};

		_calculateRotationAngle = function (incl_angle) {
			return incl_angle;
		};
	};

	exports.Vizir = Vizir;

}.call({}, this.app.lib, this, this.app.lib));
