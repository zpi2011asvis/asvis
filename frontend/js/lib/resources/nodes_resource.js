(function (exports, global) {
	'use strict';
	
	var Resource = global.app.lib.resources.Resource,
		merge = global.es5ext.Object.plain.merge.call;

	var NodesResource = function NodesResource(opts) {
		Resource.call(this, opts);
	};

	NodesResource.prototype = merge(new Resource(), {
		name: 'nodes',
		_url: 'nodes/find',
		_cache: true,
		_method: 'post'
	});

	exports.NodesResource = NodesResource;

}.call({}, this.app.lib.resources, this));
