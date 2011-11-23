(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var ConnectionsMetaResource = Resource.create(function ConnectionsMetaResource() {}, {
		name: 'connections/meta',
		_url: 'connections/meta/:for_node',
		_method: 'get',
		_cache: true,
	});

	exports.connections = {
		ConnectionsMetaResource: ConnectionsMetaResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
