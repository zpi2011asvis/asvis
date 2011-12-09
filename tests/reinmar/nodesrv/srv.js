'use strict';

var Importer = require('./lib/importer'),
	Query = require('./lib/graphdb/query.js'),
	config = require('./config'),
	utils = require('./lib/utils'),
	log = utils.log;

utils.setDebug(config.debug);

Importer(config.mysql)
(function (graph) {
	var d = +new Date();
	var q = new Query.BFS(graph);

	q.root(graph.get(3)).depth(2).execute();

	log(q.getNodes());
})
.end(function (err) {
	log('ERROR!');
	log(err.message);
	log(err.stack);
});


