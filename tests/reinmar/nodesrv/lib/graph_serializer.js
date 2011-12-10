'use strict';


exports.structure = function structure(nodes) {
	var result = {},
		ids = Object.keys(nodes),
		id, node, obj,
		i, il;

	var _connectionsReduce = function (dir) {
		var method = { up: 'to', down: 'to' }[dir],
			node;

		return function _connectionsReduce(acc, edge) {
			node = edge[method]();

			if (edge.dir === dir && nodes[node.id]) {
				acc.push(node.num);
			}
			return acc;
		};
	};
	
	for (i = 0, il = ids.length; i < il; ++i) {
		id = ids[i];
		node = nodes[id];

		obj = {
			out: node.getOut().reduce(_connectionsReduce('up'), []),
			in: node.getOut().reduce(_connectionsReduce('down'), []),
			distance: -1 // set this so the objects' shapes are not changed in the future
		};
		obj.weight = obj.out.length + obj.in.length;
		result[node.num] = obj;
	}

	return result;
};
