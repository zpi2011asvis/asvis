var mysql = require('mysql'),
	deferred = require('deferred');

var importer = (function () {
	var DEBUG = true;
	var MYSQL_DB = {
		HOST:	'localhost',
		NAME:	'asmap',
		USER:	'root',
		PASSWD:	'root',
	};
	var _client;

	var connect = function connect() {
		DEBUG && console.log('Connecting to MySQL db...');
		_client = mysql.createClient({
			user:		MYSQL_DB.USER,
			password:	MYSQL_DB.PASSWD,
			database:	MYSQL_DB.NAME,
			host:		MYSQL_DB.HOST,
		});
	};

	var getASes = function getASes() {
		DEBUG && console.log('Querying...');

		var d = deferred();

		_client.query(
			'SELECT * FROM ases LIMIT 100',
			function sel(err, results, fields) {
				d.resolve(err || results);
			}
		);
		
		return d.promise;
	};

	var end = function end() {
		_client.end();
	};

	return {
		connect:	connect,
		getASes:	getASes,
		end:		end
	};
}());


importer.connect();
importer.getASes()
(function (data) {
	console.log(data);
	importer.end();
}).end(importer.end);

