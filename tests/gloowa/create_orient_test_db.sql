DROP DATABASE remote:localhost/asvis root root;
CREATE DATABASE remote:localhost/asvis root root local;

CREATE CLASS ASNode;
CREATE CLASS ASConn;
CREATE CLASS ASPool;

CREATE PROPERTY ASNode.in linkset ASNode;
CREATE PROPERTY ASNode.out linkset ASNode;
CREATE PROPERTY ASNode.pools linkset ASPool;
CREATE PROPERTY ASNode.name string;
CREATE PROPERTY ASNode.num integer;
CREATE PROPERTY ASNode.num_as_string string;

CREATE PROPERTY ASConn.from link ASNode;
CREATE PROPERTY ASConn.to link ASNode;
CREATE PROPERTY ASConn.status integer;

CREATE PROPERTY ASPool.node link ASNode;
CREATE PROPERTY ASPool.network long;
CREATE PROPERTY ASPool.netmask integer;
CREATE PROPERTY ASPool.network_as_string string;

CREATE INDEX ASNode.num unique;
CREATE INDEX ASNode.num_as_string unique;
CREATE INDEX ASConn.from notunique;
CREATE INDEX ASConn.to notunique;

INSERT INTO ASNode (num, name, in, out) VALUES (0, 'AS0', [#5:1, #5:2],	[#5:1, #5:2]);
INSERT INTO ASNode (num, name, in, out) VALUES (1, 'AS1', [#5:0, #5:4],	[#5:0, #5:4]);
INSERT INTO ASNode (num, name, in, out) VALUES (2, 'AS2', [#5:0],		[#5:0]);
INSERT INTO ASNode (num, name, in, out) VALUES (3, 'AS3', [#5:4],		[#5:4]);
INSERT INTO ASNode (num, name, in, out) VALUES (4, 'AS4', [#5:1, #5:3],	[#5:1, #5:3]);

