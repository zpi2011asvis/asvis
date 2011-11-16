(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3,
		T_Matrix4 = T.Matrix4,
		FBA = lib.FBA,
		uniq = global.util.arrayUniq;
	
	var Vizir = function Vizir() {
		// consts
		var BASE = 40, //base length
			WEIGHT_FACTOR = 1.5,
			MAX_X = 200,
			MAX_Y = 100,
			MAX_Z = 100,
			MAX_ANGLE = 30,
			A360 = Math.PI * 2;

		// properties
		var _root,
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
			_runRecursiveVertexPos;
	
		/*
		 * Publics -------------------------------------------------------------
		 */

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
				node;
			
			// set mass centers in triangle
			node = _graph[_order[0]];
			node.pos = _nvec(-BASE, 0, 0);
			node = _graph[_order[1]];
			node.pos = _nvec(BASE, 0, 0);
			node = _graph[_order[2]];
			node.pos = _nvec(0, -BASE, 0);
			
			_runRecursiveVertexPos(
				[_order[0]]
			);

			_generateVEObjects();
			global.DEBUG && console.log('Recalculating took: ' + (new Date() - d) + 'ms (for ' + _vertices.length + ' vertices)');
			_dirty = false;

			var mc = {};
			mc[_order[0]] = true;
			mc[_order[1]] = true;
			mc[_order[2]] = true;

			_fba = new FBA(_root, _graph, mc);
			setTimeout(_fba.run.bind(_fba, 10000), 1000); // run for 10s after 100ms
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
			var data = _graph[num],				// node
				conns = data.out.concat(data.in),
				connsl = conns.length,
				current_pos,
				new_pos,
				new_num,
				rot_angle = A360 / 360 * 7.33,	// 7.33deg
				rotated = 0,					// already rotated in current surface 
				todo = [];						// queue for _runRecursiveVertexPos

			// this is done twice (also before node was added to the queue)
			// because of order in BFS
			if (_nodes_done.indexOf(num) > -1) {
				return;
			}

			// position already set (for the mass center)
			// so use it
			if (data.pos) {
				pos = data.pos.clone();
				current_pos = data.pos;
				vector = data.pos.clone();
			}
			else {
				current_pos = pos.clone();
				data.pos = current_pos;
				data.conns = [];
			}
			_vertices.push(current_pos);
			_nodes_done.push(num);
			
			// remove duplicated connections (bidirectional)
			// here (not before) because of performance
			// -- do this after upper returns
			conns = data.conns = uniq(conns);
			connsl = conns.length;

			// generating matrix for "circular" rotations
			var m2 = new T_Matrix4();
			m2.setRotationAxis(vector.clone().normalize(), rot_angle);
		
			//generating vector inclined from tree generation direction
			var m = new T_Matrix4(),
				p = _nvec(0, 1, 0).crossSelf(vector).normalize();

			// if vector was (0,1,0) then cross product is (0,0,0)
			// so try with other axis rotation
			if (p.lengthSq() === 0) {
				p = _nvec(0, 0, 1).crossSelf(vector).normalize();
			}
			m.setRotationAxis(p, rot_angle);
			m.multiplyVector3(vector);
			
			for (var i = 0; i < connsl; ++i) {
				new_num = conns[i];

				if (!_hasEdge(num, new_num)) {
					// traversing for the first time
					if (_nodes_done.indexOf(new_num) === -1) {
						new_pos = pos.clone().addSelf(vector);
						
						// add child to queue
						todo.push([conns[i], new_pos, vector.clone().multiplyScalar(0.95)]);

						_pushEdge(num, new_num);
				
						// calculate new position on sphere
						m2.multiplyVector3(vector);
						rotated += rot_angle;
						if (rotated >= A360) {
							m.multiplyVector3(vector);
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
	};

	exports.Vizir = Vizir;

}.call({}, this.app.lib, this, this.app.lib));
