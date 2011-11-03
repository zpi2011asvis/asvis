DROP DATABASE remote:localhost/asvis_exp root root;
CREATE DATABASE remote:localhost/asvis_exp root root local;

CREATE CLASS ASNode;
CREATE CLASS ASPool;

CREATE PROPERTY ASNode.in linkset ASNode;
CREATE PROPERTY ASNode.up linkset ASNode;
CREATE PROPERTY ASNode.dn linkset ASNode;
CREATE PROPERTY ASNode.name string;
CREATE PROPERTY ASNode.num integer;
CREATE PROPERTY ASNode.pools linkset ASPool;

CREATE PROPERTY ASPool.node link ASNode;
CREATE PROPERTY ASPool.network long;
CREATE PROPERTY ASPool.netmask integer;
CREATE PROPERTY ASPool.network_as_string string;

CREATE INDEX ASNode.num unique;

INSERT INTO ASNode (num, name) VALUES (0, 'AS0');
INSERT INTO ASNode (num, name) VALUES (1, 'AS1');
INSERT INTO ASNode (num, name) VALUES (2, 'AS2');
INSERT INTO ASNode (num, name) VALUES (3, 'AS3');
INSERT INTO ASNode (num, name) VALUES (4, 'AS4');

UPDATE #5:0 SET in = [#5:2], up = [#5:1,#5:2];
UPDATE #5:1 SET in = [#5:0,#5:3,#5:4], up = [#5:2];
UPDATE #5:2 SET in = [#5:0,#5:1], up = [#5:0,#5:3];
UPDATE #5:3 SET in = [#5:2], up = [#5:1,#5:4];
UPDATE #5:4 SET in = [#5:3], up = [#5:1];

