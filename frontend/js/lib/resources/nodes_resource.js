(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var NodesFindResource = Resource.create(function NodesFindResource() {}, {
		name: 'nodes/find',
		_url: 'nodes/find/:number',
		_cache: true,
		_method: 'get',
	});

	exports.nodes = {
		NodesFindResource: NodesFindResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
