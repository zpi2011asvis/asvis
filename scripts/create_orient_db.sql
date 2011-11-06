DROP DATABASE remote:localhost/asvis root root;
CREATE DATABASE remote:localhost/asvis root root local;

CREATE CLASS ASNode;
CREATE CLASS ASConn;
CREATE CLASS ASPool;

CREATE PROPERTY ASNode.conns linkset ASNode;
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
