(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource,
		merge = global.es5ext.Object.plain.merge.call;

	var NodesFindResource = Resource.create(
		function NodesFindResource(opts) {},
		{
			name: 'nodes/find',
			_url: 'nodes/find/:number',
			_cache: true,
			_method: 'get'
		}
	);

	exports.nodes = {
		NodesFindResource: NodesFindResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
