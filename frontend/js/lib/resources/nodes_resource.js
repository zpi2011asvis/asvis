(function (exports, global) {
	'use strict';
	
	var Resource = global.app.lib.resources.Resource,
		merge = global.es5ext.Object.plain.merge.call;

	var NodesFindResource = function NodesFindResource(opts) {
		Resource.call(this, opts);
	};

	NodesFindResource.prototype = merge(new Resource(), {
		name: 'nodes_find',
		_url: 'nodes/find/:number',
		_cache: true,
		_method: 'get'
	});

	//TODO exports can be object
	//so we have to handle objects while initialization
	exports.NodesFindResource = NodesFindResource;

}.call({}, this.app.lib.resources, this));
