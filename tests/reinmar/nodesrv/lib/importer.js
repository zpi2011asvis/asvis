'use strict';

var mysql = require('mysql'),
	deferred = require('deferred'),
	Graph = require('./graphdb/graph'),
	Node = Graph.Node,
	log = require('./utils').log('IMPORTER');

/*
var _int2ip = function _int2ip(ip) {
	var str = [], r;

	while (ip > 0) {
		r = ip % 256;
		ip -= r;
		ip /= 256;
		str.push(r);
	}
	return str.reverse().join('.');
};
*/

var MySQLDB = function (config) {
	var _client;

	var _query = function _query(sql) {
		var d = deferred();

		_client.query(
			sql,
			function (err, results) {
				d.resolve(err || results);
			}
		);
		
		return d.promise;
	};

	var connect = function connect() {
		log('Connecting to MySQL db...');
		_client = mysql.createClient({
			user:		config.user,
			password:	config.password,
			database:	config.name,
			host:		config.host,
		});
	};

	var getASes = function getASes() {
		log('Querying for ASes...');
		return _query('SELECT * FROM ases');
	};

	var getConnections = function getConnections(dir) {
		log('Querying for connections...');
		return _query('SELECT * FROM as' + dir + ' WHERE asnum' + dir + ' <> -1');
	};

	/*var getPools = function getPools() {
		log('Querying for pools...');
		return _query('SELECT * FROM aspool LIMIT 100');
	};*/

	var end = function end() {
		log('Bye...');
		_client.end();
	};

	return {
		connect:			connect,
		getASes:			getASes,
		getConnections:		getConnections,
		// getPools:			getPools,
		end:				end,
	};
};

var Importer = function Importer(mysql_config) {
	var _mysql_db,
		_graph = new Graph('num'),
		_main_d = deferred(),
		_conns_up,
		_conns_down;

	var _getNodes = function _getNodes() {
		return (
			_mysql_db.getASes()
			(function (data) {
				var i, il, node, node_data;

				for (i = 0, il = data.length; i < il; ++i) {
					node_data = data[i];
					_graph.add(
						new Node({
							num:		node_data.ASNum,
							name:		node_data.ASName,
							pools:		[]
						})
					);
				}
			})
		);
	};

	var _getConnections = function _getConnections() {
		return deferred.all(
			_mysql_db.getConnections('up')
			(function (data) {
				_conns_up = data;
			}),

			_mysql_db.getConnections('down')
			(function (data) {
				_conns_down = data;
			})
		);
	};

	/*
	var _getPools = function _getPools() {
		return (
			_mysql_db.getPools()
			(function (pools) {
				var i, il, pool, node;

				for (i = 0, il = pools.length; i < il; ++i) {
					pool = pools[i];
					node = _graph.get(pool.ASNum);
					node.pools.push({
						ip:			_int2ip(pool.ASNetwork),
						netmask:	pool.ASNetmask
					});
				}
			})
		);
	};
	*/

	var _addConnection = function _addConnection(node1, node2, dir) {
		var dir_opp = { up: 'down', down: 'up' }[dir],
			// check if this connections are already set
			edge1 = node1.getTo(node2).filter(function (edge) { return edge.dir === dir; })[0],
			edge2 = node2.getTo(node1).filter(function (edge) { return edge.dir === dir_opp; })[0];

		if (!edge1 && !edge2) {
			node1.addTo(node2, {
				dir: dir,
				status: 1
			});
			node2.addTo(node1, {
				dir: dir_opp,
				status: 2
			});
		}
		else {
			edge1.status = 0;
			edge2.status = 0;
		}
	};

	var _addConnections = function _addConnections() {
		var i, il, conn;

		for (i = 0, il = _conns_up.length; i < il; ++i) {
			conn = _conns_up[i];
			_addConnection(_graph.get(conn.ASNum), _graph.get(conn.ASNumUp), 'up');
		}
		for (i = 0, il = _conns_down.length; i < il; ++i) {
			conn = _conns_down[i];
			_addConnection(_graph.get(conn.ASNum), _graph.get(conn.ASNumDown), 'down');
		}
	};

	_mysql_db = MySQLDB(mysql_config);
	_mysql_db.connect();

	deferred.all(
		_getNodes(),
		_getConnections()
	)
	(_addConnections)
	// (_getPools)
	(function () {
		log('SUCCESS!')
		log('Graph size: ' + _graph.getSize());
		_mysql_db.end();

		_main_d.resolve(_graph);
	})
	.end(function (err) {
		log('ERROR!');
		log(err.message);
		log(err.stack);
		_mysql_db.end();

		_main_d.resolve(new Error('Error while importing graph'));
	});

	return _main_d.promise;
};

module.exports = Importer;
