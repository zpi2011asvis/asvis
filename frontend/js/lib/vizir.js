(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		T_Vertex = T.Vertex,
		T_Vector3 = T.Vector3,
		T_Matrix4 = T.Matrix4;
	
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
			_done = [],			//indexes of recalculated vertices
			_vertices = [],		//ordered as in _order
			_edges = [],
			_dirty = false;

		// methods
		var _nver,
			_nvec,
			_recalculatePositions,
			_generateVertices,
			_generateEdges,
			_recursiveVertexPos;
	
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
			_done = [];
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
			
			_recursiveVertexPos(_order[i++], _nvec(BASE, 0, 0), _nvec(BASE, 0, 0), 1);

			// jump to the next not yet done or to the end
			while (_done[_order[i++]]){}
			
			if (i < _order.length) {	
				// traverse to the end
				_recursiveVertexPos(_order[i], _nvec(-BASE, 0, 0), _nvec(-BASE, 0, 0), 1e5);
			}

			_generateVertices();
			_generateEdges();

			global.DEBUG && console.log('Recalculating took: ' + (new Date() - d) + 'ms (for ' + _vertices.length + ' vertices)');

			_dirty = false;
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
		 * number of undone children
		 */
		_recursiveVertexPos = function _recursiveVertexPos(num, pos, vector, depth) {
			var data = _graph[num],
				cons = data.out, // TODO add downstream connections .concat(data.in),
				consl = cons.length,
				current_pos,
				new_pos,
				new_num,
				rot_angle = A360 / 36 * 1.5, //15deg
				rotated = 0; // already rotated in current surface 

			_done.push(num);
			current_pos = pos.clone();
			_vertices.push(current_pos);
			data.pos = current_pos;
			
			if (depth < 0) return;

			// break if no children
			if (consl === 0) return;
		
			//generating vector inclined from tree generation direction
			var m = new T_Matrix4(),
				p = _nvec(0, 1, 0).crossSelf(vector).normalize();
			m.setRotationAxis(p, rot_angle);
			m.multiplyVector3(vector);

			// generating matrix for "circular" rotations
			var m2 = new T_Matrix4();
			m2.setRotationAxis(_nvec(1, 0, 0), rot_angle);

			for (var i = 0; i < consl; ++i) {
				new_num = cons[i];
			
				// traversing for the first time
				if (_done.indexOf(new_num) === -1) {
					new_pos = pos.clone().addSelf(vector);

					_recursiveVertexPos(cons[i], new_pos, vector.clone().multiplyScalar(0.9), depth - 1);

					_edges.push(current_pos, new_pos);
			
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
					_edges.push(current_pos, _graph[new_num].pos);
				}

			}
		};

		_generateVertices = function _generateVertices() {
			var vertices = [];
			for (var i = 0, il = _vertices.length; i < il; ++i) {
				vertices[i] = new T_Vertex(_vertices[i]);
			}

			_vertices = vertices; //fast swap
		};
		
		_generateEdges = function _generateEdges() {
			var edges = [];
			for (var i = 0, il = _edges.length; i < il; ++i) {
				edges[i] = new T_Vertex(_edges[i]);
			}

			_edges = edges; //fast swap
		};

	};

	exports.Vizir = Vizir;

}.call({}, this.app.lib, this, this.app.lib));
