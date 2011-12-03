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
};
Graph.prototype = {
	constructor: Graph,
	add: function add(node) {
		this._nodes[node.id] = node;
		this._nodes_by_key[node[this._node_key_name]] = node;
	},

	get: function get(key) {
		return this._nodes_by_key[key];
	},

	getByID: function getByID(id) {
		return this._nodes[id];
	}
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

};

var Edge = function Edge(from, to, data) {
	_extend.call(this, data);
	this._from = from;
	this._to = to;
	this.id = _last_edge_id;
	_last_edge_id += 1;
};


Graph.Node = Node;
Graph.Edge = Edge;
module.exports = Graph;
