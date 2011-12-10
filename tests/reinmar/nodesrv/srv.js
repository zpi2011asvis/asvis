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
			depth = +req.uriParams.depth,
			root = graph.get(number),
			q, structure, order_w, order_d;

		if (!root) {
			res.send(404, 'Brak AS-a o podanym numerze');
		}
		else {
			q = new Query.BFS(graph);
			q.root(root).depth(depth).execute();

			structure = serializer.structure(q.getNodes());
			utils.calculateDistances(root, structure);

			order_w = utils.getWeightOrder(structure);
			order_d = utils.getDistanceOrder(structure);

			res.send(200, { structure: structure, weight_order: order_w, distance_order: order_d });
		}
	});

	srv.listen(8080);
	log('Listening on port 8080');
})
.end(function (err) {
	log('ERROR!');
	log(err.message);
	log(err.stack);
});


