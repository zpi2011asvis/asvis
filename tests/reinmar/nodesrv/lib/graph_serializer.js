'use strict';

exports.structure = function structure(nodes) {
	var result = {},
		ids = Object.keys(nodes),
		id, node, obj,
		i, il;

	var _connectionsReduce = function (dir) {
		return function _connectionsReduce(acc, conn) {
			if (conn.edge.type === dir && nodes[conn.node.id]) {
				acc.push(conn.node.num);
			}
			return acc;
		};
	};
	
	for (i = 0, il = ids.length; i < il; ++i) {
		id = ids[i];
		node = nodes[id];

		obj = {
			out: node.getOut().reduce(_connectionsReduce('up'), []),
			in: node.getOut().reduce(_connectionsReduce('down'), [])
		};
		obj.weight = obj.out.length + obj.in.length;
		result[node.num] = obj;
	}

	return result;
};
