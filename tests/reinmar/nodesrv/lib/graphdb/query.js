'use strict';

var extend = require('../utils').extend;

var BFS = function BFS(graph) {
	var that = {},
		_root = null,
		_depth = -1,

		_nodes_queued = {},
		_result,
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

		_result = Object.keys(_nodes_queued);

		return that;
	};

	var getNodes = function getNodes() {
		return graph.getMany(_result);
	};

	extend.call(that, {
		root: root,
		depth: depth,
		execute: execute,
		getNodes: getNodes
	});
	return that;
};

exports.BFS = BFS;
