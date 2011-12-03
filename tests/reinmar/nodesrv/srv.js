var mysql = require('mysql'),
	deferred = require('deferred'),
	Graph = require('./graph'),
	Node = Graph.Node,
	DEBUG = true;

var _log = function _log() {
	DEBUG && console.log.apply(console, arguments);
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
		return _query('SELECT * FROM aspool');
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
		_conn_up,
		_conn_down;

	var _getNodes = function _getNodes() {
		return (
			mysql_db.getASes()
			(function (data) {
				var i, il, node, node_data;

				for (i = 0, il = data.length; i < il; ++i) {
					node_data = data[i];
					_graph.add(
						new Node({
							'num':		node_data.ASNum,
							'name':		node_data.ASName,
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
				_conn_up = data;
			}),

			mysql_db.getConnections('down')
			(function (data) {
				_conn_down = data;
			})
		);
	};

	var _getPools = function _getPools() {
		return (
			mysql_db.getPools()
			(function (pools) {
				console.log(pools[0]);
			})
		);
	};

	mysql_db.connect();

	deferred.all(
		_getNodes(),
		_getConnections()
	)
	(_getPools)
	(function () {
		_log(_graph.size());
		_log(_conn_up.length, _conn_down.length);
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
