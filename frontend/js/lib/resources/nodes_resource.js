(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var NodesFindResource = Resource.create(function NodesFindResource() {}, {
		name: 'nodes/find',
		_url: 'nodes/find/:number',
		_method: 'get',
		_cache: true,
	});

	/*
	 * resource params:
	 *   * numbers: Array of ints
	 */
	var NodesMetaResource = Resource.create(function NodesMetaResource() {}, {
		name: 'nodes/meta',
		_url: 'nodes/meta',
		_method: 'post',
		_cache: true,
		_type: 'object',
		_key_field: 'numbers'
	});

	exports.nodes = {
		NodesFindResource: NodesFindResource,
		NodesMetaResource: NodesMetaResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
