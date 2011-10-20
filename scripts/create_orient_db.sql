DROP DATABASE remote:localhost/asvis root root;
CREATE DATABASE remote:localhost/asvis root root local;

CREATE CLASS ASNode;
CREATE CLASS ASConn;
CREATE CLASS ASPool;

CREATE PROPERTY ASNode.in linkset ASConn;
CREATE PROPERTY ASNode.out linkset ASConn;
CREATE PROPERTY ASNode.pools linkset ASPool;
CREATE PROPERTY ASNode.name string;
CREATE PROPERTY ASNode.num integer;
CREATE PROPERTY ASNode.num_as_string string;

CREATE PROPERTY ASConn.in link ASNode;
CREATE PROPERTY ASConn.out link ASNode;
CREATE PROPERTY ASConn.up boolean;

CREATE PROPERTY ASPool.node linkset ASNode;
CREATE PROPERTY ASPool.asnetwork integer;
CREATE PROPERTY ASPool.asnetmask integer;

CREATE INDEX ASNode.num unique;
CREATE INDEX ASConn.in notunique;
CREATE INDEX ASConn.out notunique;
