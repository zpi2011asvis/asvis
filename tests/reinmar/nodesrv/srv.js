var mysql = require('mysql'),
	deferred = require('deferred'),
	Graph = require('./graph'),
	Node = Graph.Node,
	DEBUG = true;

var _log = function _log() {
	DEBUG && console.log.apply(console, arguments);
};

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

var mysql_db = (function () {
	var MYSQL_DB = {
		HOST:	'localhost',
		NAME:	'asmap',
		USER:	'root',
		PASSWD:	'root',
	};
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
		_log('Connecting to MySQL db...');
		_client = mysql.createClient({
			user:		MYSQL_DB.USER,
			password:	MYSQL_DB.PASSWD,
			database:	MYSQL_DB.NAME,
			host:		MYSQL_DB.HOST,
		});
	};

	var getASes = function getASes() {
		_log('Querying for ASes...');
		return _query('SELECT * FROM ases');
	};

	var getConnections = function getConnections(dir) {
		_log('Querying for connections...');
		return _query('SELECT * FROM as' + dir + ' WHERE asnum' + dir + ' <> -1');
	};

	var getPools = function getPools() {
		_log('Querying for pools...');
		return _query('SELECT * FROM aspool LIMIT 100');
	};

	var end = function end() {
		_log('Bye...');
		_client.end();
	};

	return {
		connect:			connect,
		getASes:			getASes,
		getConnections:		getConnections,
		getPools:			getPools,
		end:				end,
	};
}());

var Importer = function Importer() {
	var _graph = new Graph('num'),
		_conns_up,
		_conns_down;

	var _getNodes = function _getNodes() {
		return (
			mysql_db.getASes()
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
			mysql_db.getConnections('up')
			(function (data) {
				_conns_up = data;
			}),

			mysql_db.getConnections('down')
			(function (data) {
				_conns_down = data;
			})
		);
	};

	var _getPools = function _getPools() {
		return (
			mysql_db.getPools()
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

	var _addConnection = function _addConnection(node1, node2, dir) {
		var conns;

		conns = node1.getAllTo(node2);

		if (conns.length === 0) {
			node1.addTo(node2, {
				type: dir,
				status: 1
			});
		}
		else {
			conns.forEach(function (conn) {
				conn.edge.status = 0;
			});
		}

		conns = node2.getAllTo(node1);

		if (conns.length === 0) {
			node2.addTo(node1, {
				type: dir,
				status: 2
			});
		}
		else {
			conns.forEach(function (conn) {
				conn.edge.status = 0;
			});
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

	mysql_db.connect();

	deferred.all(
		_getNodes(),
		_getConnections()
	)
	(_addConnections)
	(_getPools)
	(function () {
		_log(_graph.size());
		_log(_conns_up.length, _conns_down.length);
		_log(process.memoryUsage());
		mysql_db.end();
	})
	.end(function (err) {
		_log('FUCK!');
		_log(err);
		mysql_db.end();
	});
};

Importer();
