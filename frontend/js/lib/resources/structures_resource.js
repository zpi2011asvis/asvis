(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var StructureGraphResource = Resource.create(function StructureGraphResource() {}, {
		name: 'structure/graph',
		_url: 'structure/graph/:number/:depth',
		_method: 'get',
		_cache: true,
	});

	exports.structures = {
		StructureGraphResource: StructureGraphResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
