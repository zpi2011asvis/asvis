(function (exports, global) {
	'use strict';

	var merge = global.es5ext.Object.plain.merge.call;

	var NodesResource = function NodesResource() {
	};

	NodesResource.prototype = merge(new global.app.lib.resources.Resource(), {
		name: 'nodes',
		cache: true
	});

	exports.NodesResource = NodesResource;

}.call({}, this.app.lib.resources, this));
