var _last_node_id = 0,
	_last_edge_id = 0;

var _extend = function _extend(data) {
	for (var key in data) {
		if (data.hasOwnProperty(key)) {
			this[key] = data[key];
		}
	}
};

var Graph = function Graph(node_key_name) {
	this._nodes = [];
	this._nodes_by_key = {};
	this._node_key_name = node_key_name;
	this._size = 0;
};
Graph.prototype = {
	constructor: Graph,
	add: function add(node) {
		this._nodes[node.id] = node;
		this._nodes_by_key[node[this._node_key_name]] = node;
		this._size += 1;
	},

	get: function get(key) {
		return this._nodes_by_key[key];
	},

	getByID: function getByID(id) {
		return this._nodes[id];
	},

	size: function size() {
		return this._size;
	},
};


var Node = function Node(data) {
	_extend.call(this, data);
	this._out = [];
	this._in = [];
	this.id = _last_node_id;
	_last_node_id += 1;
};
Node.prototype = {
	constructor: Node,
	
	addTo: function addTo(to, edge_data) {
		var edge = new Edge(this, to, edge_data);
		this._out.push({ node: to, edge: edge });
		to._in.push({ node: this, edge: edge });
	},

	getAllTo: function getAllTo(to) {
		var i, il;

		return this._out.filter(function (edge) {
			return to.id === edge.node.id;
		});
	}
};

var Edge = function Edge(from, to, data) {
	_extend.call(this, data);
	this._from = from;
	this._to = to;
	this.id = _last_edge_id;
	_last_edge_id += 1;
};


var BFSQuery = function BFSQuery(graph) {
	var that = {},
		_root = null,
		_depth = -1,

		_nodes_queued = {},
		_queue = [],
		_queue_l = 0,
		_queue_i = 0;

	var root = function root(r) {
		_root = r;
		return that;
	};

	var depth = function depth(d) {
		_depth = d;
		return that;
	};

	var execute = function execute() {
		var item, current, depth, conns,
			next_conn, next_node,
			i, il;

		_nodes_queued[_root.id] = true;
		_queue.push({ next: _root, depth: _depth });
		_queue_l += 1;

		while (_queue_i < _queue_l) {
			item = _queue[_queue_i];
			current = item.next;
			depth = item.depth;
			conns = current._out;

			if (depth > 0 || depth === -1) {
				for (i = 0, il = conns.length; i < il; ++i) {
					next_conn = conns[i];
					next_node = next_conn.node;

					if (!_nodes_queued[next_node.id]) {
						_nodes_queued[next_node.id] = true;
						_queue.push({ next: next_node, depth: depth - 1 });
						_queue_l += 1;
					}
				}
			}

			_queue_i += 1;
		}

		return that;
	};

	var toObject = function toObject() {
	};

	var toPlainObject = function toPlainObject(cut) {
		var obj = {}, keys;
		if (cut) {

		}
		else {
			keys = Object.keys(_nodes_queued);
			//.map(function (node_id) {
			//	return graph._nodes[+node_id];
			//});
		}

		return obj;
	};

	_extend.call(that, {
		root: root,
		depth: depth,
		execute: execute,
		toObject: toObject,
		toArray: toArray
	});
	return that;
};


Graph.Node = Node;
Graph.Edge = Edge;
Graph.BFSQuery = BFSQuery;
module.exports = Graph;
