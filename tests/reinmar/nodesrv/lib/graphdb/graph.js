'use strict';

var extend = require('../utils').extend;

var _last_node_id = 0,
	_last_edge_id = 0;

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

	getMany: function getMany(keys) {
		var nodes = this._nodes;

		return keys.reduce(function (acc, node_id) {
			acc[node_id] = nodes[+node_id];
			
			return acc;
		}, {});
	},

	getByID: function getByID(id) {
		return this._nodes[id];
	},

	size: function size() {
		return Object.keys(this._nodes_by_key).length;
	},
};


var Node = function Node(data) {
	extend.call(this, data);
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
	extend.call(this, data);
	this._from = from;
	this._to = to;
	this.id = _last_edge_id;
	_last_edge_id += 1;
};

Graph.Node = Node;
Graph.Edge = Edge;
module.exports = Graph;
