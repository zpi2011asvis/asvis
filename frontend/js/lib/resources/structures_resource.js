(function (exports, global, lib) {
	'use strict';
	
	var Resource = lib.resources.Resource;

	var StructureGraphResource = Resource.create(function StructureGraphResource() {}, {
		name: 'structure/graph',
		_url: 'structure/graph/:number/:depth',
		_method: 'get',
		_cache: true,
	});

	var StructurePathsResource = Resource.create(function StructurePathsResource() {}, {
		name: 'structure/paths',
		_url: 'structure/paths/:from/:to/:type',
		_method: 'get',
		_cache: true,
	});

	var StructureTreeResource = Resource.create(function StructureTreeResource() {}, {
		name: 'structure/tree',
		_url: 'structure/paths/:number/:height/:type',
		_method: 'get',
		_cache: true,
	});

	exports.structures = {
		StructureGraphResource: StructureGraphResource,
		StructurePathsResource: StructurePathsResource,
		StructureTreeResource: StructureTreeResource
	};

}.call({}, this.app.lib.resources, this, this.app.lib));
