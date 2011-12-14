'usr strict';

var DEBUG = false;

var log = function (module_name) {
	var slice = [].slice;
	return function log() {
		DEBUG && console.log.apply(console, module_name ? [ module_name + ':' ].concat(slice.call(arguments)) : arguments);
	};
};

var extend = function extend(data) {
	for (var key in data) {
		if (data.hasOwnProperty(key)) {
			this[key] = data[key];
		}
	}
};

var calculateDistances = function calculateDistances(root, nodes) {
	var queue = [],
		queue_i = 0, queue_l = 0,
		item, node,
		nodes_queued = {},
		conns, num, i, il;

	queue.push({ next: root.num, distance: 0 });
	queue_l += 1;
	nodes_queued[root.num] = true;
	
	while (queue_i < queue_l) {
		item = queue[queue_i];
		node = nodes[item.next];

		node.distance = item.distance;

		conns = node.out.concat(node.in);
		for (i = 0, il = conns.length; i < il; ++i) {
			num = conns[i];
			if (!nodes_queued[num]) {
				queue.push({ next: num, distance: item.distance + 1 });
				queue_l += 1;
				nodes_queued[num] = true;
			}
		}
		queue_i += 1;
	}
};

var getWeightOrder = function getWeightOrder(nodes) {
	var nums = Object.keys(nodes);
	nums.sort(function (a, b) {
		return nodes[b].weight - nodes[a].weight;
	});

	return nums.map(function (s) { return +s; });
};

var getDistanceOrder = function getDistanceOrder(nodes) {
	var nums = Object.keys(nodes);
	nums.sort(function (a, b) {
		return nodes[a].distance - nodes[b].distance;
	});

	return nums.map(function (s) { return +s; });
};



exports.log = log;
exports.extend = extend;
exports.calculateDistances = calculateDistances;
exports.getWeightOrder = getWeightOrder;
exports.getDistanceOrder = getDistanceOrder;

exports.setDebug = function setDebug(d) {
	DEBUG = d;
};

