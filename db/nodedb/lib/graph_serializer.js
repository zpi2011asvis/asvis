'use strict';


exports.structure = function structure(nodes) {
	var result = {},
		ids = Object.keys(nodes),
		id, node, obj,
		i, il;

	var _connectionsReduce = function (dir) {
		return function _connectionsReduce(acc, edge) {
			var node = edge.to();

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
			// set this so the objects' shapes are not changed in the future
			distance: -1,
			weight: -1
		};
		obj.weight = obj.out.length + obj.in.length;
		result[node.num] = obj;
	}

	return result;
};

exports.connections = function connections(node) {
	var conns = node.getOut().reduce(function (conns, edge) {
		var node = edge.to(),
			conn = conns[node.num];

		if (conn) {
			conn.dir = 'both';
		}
		else {
			conns[node.num] = { with: node.num, dir: edge.dir, status: edge.status };
		}

		return conns;
	}, {});

	return Object.keys(conns).map(function (num) {
		return conns[num];
	}).sort(function (conn1, conn2) {
		if (
			conn1.status !== conn2.status &&
			(conn1.status === 0 || conn2.status === 0)
		) {
			// sort by status only if pairs: (0,1) (0,2) (1,0) (2,0)
			return conn1.status - conn2.status;
		}

		if (conn1.dir !== conn2.dir) {
			if (conn1.dir === 'both') return -1;
			if (conn2.dir === 'both') return 1;
			if (conn1.dir === 'up') return -1;
			return 1;
		}

		return conn1.with - conn2.with;
	});
};
