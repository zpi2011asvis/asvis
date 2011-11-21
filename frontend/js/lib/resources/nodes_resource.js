(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var NodesFindResource = Resource.create(function NodesFindResource() {}, {
		name: 'nodes/find',
		_url: 'nodes/find/:number',
		_cache: true,
		_method: 'get',
	});

	var NodesMetaResource = Resource.create(function NodesMetaResource() {}, {
		name: 'nodes/meta',
		_url: 'nodes/meta',
		_cache: true,
		_method: 'post'
	});

	exports.nodes = {
		NodesFindResource: NodesFindResource,
		NodesMetaResource: NodesMetaResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
