CREATE CLASS ASNode EXTENDS OGraphVertex;
CREATE CLASS ASConn EXTENDS OGraphEdge;
CREATE PROPERTY ASNode.name string;
CREATE PROPERTY ASNode.num integer;
CREATE PROPERTY ASConn.up boolean;

CREATE DATABASE remote:localhost/asvis root root local
CREATE CLASS ASNode;
CREATE CLASS ASConn;
CREATE PROPERTY ASNode.in linkset ASConn;
CREATE PROPERTY ASNode.out linkset ASConn;
CREATE PROPERTY ASNode.name string;
CREATE PROPERTY ASNode.num integer;
CREATE PROPERTY ASConn.in link ASNode;
CREATE PROPERTY ASConn.out link ASNode;
CREATE PROPERTY ASConn.up boolean;
