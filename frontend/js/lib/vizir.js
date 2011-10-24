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
			_order = graph.count_order;
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
			//console.log(_graph, _root);
			var d = +new Date();
			
			_recursiveVertexPos(_root, _nvec(0, 0, 0), _nvec(BASE, 0, 0));
			_generateVertices();
			_generateEdges();

			console.log('recalculating: ', new Date() - d);

			_dirty = false;
		};

		/*
		 * @param num {Integer} 
		 * @param pos {Vector3} [x,y,z]
		 * @param vector {Vector3} child_pos - parent_pos
		 */
		_recursiveVertexPos = function _recursiveVertexPos(num, pos, vector) {
			var data = _graph[num],
				cons = data.up.concat(data.down),
				consl = cons.length,
				current_pos,
				new_pos,
				new_num,
				rot_angle,
				rot_matrix_z,
				rot_matrix_y,
				rotated = 0; // already rotated in current surface 

			_done.push(num);
			current_pos = pos.clone();
			_vertices.push(current_pos);
			data.pos = current_pos;
	

			// break if no children
			if (consl === 0) return;
	
			//if (consl < 20) {
				// when number of connections is small
				// place them on plane
				// +1 because don't want to make full circle
				rot_angle = (A360 / (consl + 1));
			//}
			//else {
				// calculate how many vertices can be placed on sphere
				// +2 because of north and south pole (leave them empty)
			//	rot_angle = A360 / (Math.ceil(Math.sqrt(consl)) + 2);
			//}
			// e.g. when number of cons is small don't draw sparse circle
			if (rot_angle > MAX_ANGLE) rot_angle = MAX_ANGLE;

			//console.log(rot_angle);

			// primary rotation
			rot_matrix_z = new T_Matrix4().setRotationZ(rot_angle);
			// secondary one
			rot_matrix_y = new T_Matrix4().setRotationY(rot_angle);
			

			for (var i = 0; i < consl; ++i) {
				new_num = cons[i];
			
				// traversing for the first time
				if (_done.indexOf(new_num) === -1) {
					new_pos = pos.clone().addSelf(vector);
					_recursiveVertexPos(cons[i], new_pos, vector.clone().multiplyScalar(0.45));
					_edges.push(current_pos, new_pos);

					rot_matrix_z.multiplyVector3(vector);
				}
				// traversing again (push only edge)
				else {
					_edges.push(current_pos, _graph[new_num].pos);
				}

				/*rotated += rot_angle;
				if (rotated > A360) {
					rotated = 0;
					rot_matrix_y.multiplyVector3(vector);
					rot_matrix_z.multiplySelf(rot_matrix_y);
				}*/
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
