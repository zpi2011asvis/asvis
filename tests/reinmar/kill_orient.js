'use strict';

var http = require('http'),
	escape = require('querystring').escape;

var nums1 = [],
	nums2 = [];
for (var i = 0; i < 10000;) {
	i += ~~(Math.random() * 5);
	nums1.push(i);
}
for (var i = 0; i < 10000;) {
	i += ~~(Math.random() * 10);
	nums2.push(i);
}

var queries = [
	{
		sql: 'SELECT FROM ASNode WHERE num = 6',
		limit: -1,
		fp: '*:4 ASNode.pools:0',
	},
	{
		sql: 'SELECT FROM ASConn WHERE to = #5:1',
		limit: -1,
		fp: '*:1 ASConn.{$field}:0 ASNode.in:0 ASNode.out:0 ASNode.pools:0'
	},
	{
		sql: 'SELECT FROM ASNode WHERE num = 3',
		limit: -1,
		fp: '*:4 ASNode.pools:0',
	},
	{
		sql: 'SELECT FROM ASNode WHERE num IN [' + nums2.join(',') + ']',
		limit: -1,
	},
	{
		sql: 'SELECT FROM ASNode WHERE num = 1456',
		limit: -1,
		fp: '*:5 ASNode.pools:0',
	},
	{
		sql: 'SELECT FROM ASNode WHERE num = 699',
		limit: -1,
		fp: '*:5 ASNode.pools:0',
	},
	{
		sql: 'SELECT FROM ASNode WHERE num IN [' + nums1.join(',') + ']',
		limit: -1,
	}
];

var makePath = function (query) {
	var path = '/query/asvis/sql/' + escape(query.sql) + '/' + escape(query.limit);

	if (query.fp) {
		path += '/' + escape(query.fp);
	}
	return path;
};

var i = 0;

var query = function query() {
	var path = makePath(queries[i]);
	//var path = makePath(queries[~~(Math.random() * queries.length)]);
	var start = +new Date();

	var req = http.request(
		{
			host:		'localhost',
			port:		2480,
			method:		'GET',
			path:		path,
			auth:		'admin:admin',
			headers: {
				connection: 'keep-alive'
			}
		},
		function (res) {
			var body = '';

			res.setEncoding('utf8');
			res.on('data', function (chunk) {
				body += chunk;
			});
			res.on('end', function () {
				if (++i < queries.length) query();

				console.log('----------------------------------------------');
				console.log('QUERY: ' + path.slice(0, 150));
				console.log('STATUS: ' + res.statusCode);
				console.log('HEADERS: ' + JSON.stringify(res.headers));
				console.log('BODY.LENGTH: ' + body.length);
				try {
					JSON.parse(body);
					console.log('JSON: properly formatted');
				}
				catch (e) {
					console.log('ERROR while parsing JSON: ' + e.message);
				}
				console.log('TIME: ' + (new Date() - start));
			});
		}
	);

	req.on('error', function (e) {
		console.log('ERROR: ' + e.message);
	});

	req.end();
};

query();

