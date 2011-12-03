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
		_log('Querying...');

		var d = deferred();

		_client.query(
			'SELECT * FROM ases',
			function (err, results, fields) {
				d.resolve(err || results);
			}
		);
		
		return d.promise;
	};

	var end = function end() {
		_log('Bye...');
		_client.end();
	};

	return {
		connect:	connect,
		getASes:	getASes,
		end:		end
	};
}());

var Importer = function Importer() {
	var _graph = new Graph('num');

	mysql_db.connect();
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
	(function () {
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
