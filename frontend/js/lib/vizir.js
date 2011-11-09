(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3,
		T_Matrix4 = T.Matrix4,
		uniq = global.util.arrayUniq;
	
	var Vizir = function Vizir() {
		// consts
		var BASE = 100, //base length
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
			_edges_done = [],	// strings 'num1_num2' for recalculated edges
			_vertices = [],		// ordered as in _order
			_edges = [],
			_dirty = false;

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
			_edges_done = [];
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
				i = 0;

			_runRecursiveVertexPos([
				[_order[i++], _nvec(BASE, 0, 0), _nvec(BASE, 0, 0), 1]
			]);
			
			// jump to the next not yet nodes_done or to the end
			while (_nodes_done[_order[i++]]){}
			
			if (i < _order.length) {	
				// traverse to the end
				_runRecursiveVertexPos([
					[_order[i], _nvec(-BASE, 0, 0), _nvec(-BASE, 0, 0), 1e5]
				]);
			}

			_generateVEObjects();

			global.DEBUG && console.log('Recalculating took: ' + (new Date() - d) + 'ms (for ' + _vertices.length + ' vertices)');

			_dirty = false;
		};

		_runRecursiveVertexPos = function _runRecursiveVertexPos(queue) {
			while (queue.length > 0) {
				var args = queue.shift(),
					todo = _recursiveVertexPos(args[0], args[1], args[2], args[3]);

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
		_recursiveVertexPos = function _recursiveVertexPos(num, pos, vector, depth) {
			var data = _graph[num],
				cons = data.out.concat(data.in),
				consl = cons.length,
				current_pos,
				new_pos,
				new_num,
				rot_angle = A360 / 36 * 1, //10deg
				rotated = 0, // already rotated in current surface 
				todo = []; //queue for _runRecursiveVertexPos

			if (_nodes_done.indexOf(num) > -1) {
				return;
			}

			_nodes_done.push(num);
			current_pos = pos.clone();
			_vertices.push(current_pos);
			data.pos = current_pos;
			
			if (depth < 0) return;

			// break if no children
			if (consl === 0) return;

			// remove duplicated connections
			// here (not before) because of performance
			// -- do this after upper returns
			cons = uniq(cons);
			consl = cons.length;
		
			//generating vector inclined from tree generation direction
			var m = new T_Matrix4(),
				p = _nvec(0, 1, 0).crossSelf(vector).normalize();
			m.setRotationAxis(p, rot_angle);
			m.multiplyVector3(vector);

			// generating matrix for "circular" rotations
			var m2 = new T_Matrix4();
			m2.setRotationAxis(_nvec(1, 0, 0), rot_angle);
			
			//console.log(num, cons);
			for (var i = 0; i < consl; ++i) {
				new_num = cons[i];

				if (!_hasEdge(num, new_num)) {
					// traversing for the first time
					if (_nodes_done.indexOf(new_num) === -1) {
						new_pos = pos.clone().addSelf(vector);
						
						// add child to queue
						todo.push([cons[i], new_pos, vector.clone().multiplyScalar(0.9), depth - 1]);

						//console.log('A', num, new_num);
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
						//console.log('B', num, new_num);
						_pushEdge(num, new_num);
					}
				}
			}

			return todo;
		};

		_pushEdge = function _pushEdge(num1, num2) {
			// add in both directions - simpler to search them
			_edges_done.push(
				num1 + '_' + num2,
				num2 + '_' + num1
			);
			_edges.push(num1, num2);
		};

		_hasEdge = function _hasEdge(num1, num2) {
			// more than 10x faster than searching _edges_done
			return (
				_nodes_done.indexOf(num1) > -1 &&
				_nodes_done.indexOf(num2) > -1
			);
		};

		_generateVEObjects = function _generateVEObjects() {
			var edges = [];
			for (var i = 0, il = _edges.length; i < il; ++i) {
				edges[i] = new T_Vertex(_graph[_edges[i]].pos);
			}

			_edges = edges; //fast swap

			var vertices = [];
			for (var i = 0, il = _vertices.length; i < il; ++i) {
				vertices[i] = new T_Vertex(_vertices[i]);
			}

			_vertices = vertices; //fast swap
		};
	};

	exports.Vizir = Vizir;

}.call({}, this.app.lib, this, this.app.lib));
