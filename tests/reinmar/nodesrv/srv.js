'use strict';

var Importer = require('./lib/importer'),
	Query = require('./lib/graphdb/query.js'),
	config = require('./config'),
	utils = require('./lib/utils'),
	log = utils.log('SERVER'),
	serializer = require('./lib/graph_serializer'),
	restify = require('restify');

utils.setDebug(config.debug);

Importer(config.mysql)
(function (graph) {
	var srv = restify.createServer();

	srv.get('/graph/:number/:depth', function (req, res) {
		var number = +req.uriParams.number,
			depth = +req.uriParams.depth;

		var q = new Query.BFS(graph);
		q.root(graph.get(number)).depth(depth).execute();
		
		res.send(200, serializer.structure(q.getNodes()));
	});

	srv.listen(8080);
	log('Listening on port 8080');
})
.end(function (err) {
	log('ERROR!');
	log(err.message);
	log(err.stack);
});


