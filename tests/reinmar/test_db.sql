connect remote:localhost/asvis admin admin 

INSERT INTO ASNode (num, name) VALUES (0, 'AS0');
INSERT INTO ASNode (num, name) VALUES (1, 'AS1');
INSERT INTO ASNode (num, name) VALUES (2, 'AS2');
INSERT INTO ASNode (num, name) VALUES (3, 'AS3');
INSERT INTO ASNode (num, name) VALUES (4, 'AS4');

INSERT INTO ASConn (in, out, up) VALUES (#5:0, #5:1, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:0, #5:2, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:1, #5:2, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:2, #5:3, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:3, #5:1, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:3, #5:4, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:4, #5:1, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:2, #5:0, true);

UPDATE #5:0 SET in = [#6:7], out = [#6:0,#6:1];
UPDATE #5:1 SET in = [#6:0,#6:4,#6:6], out = [#6:2];
UPDATE #5:2 SET in = [#6:1,#6:2], out = [#6:3,#6:7];
UPDATE #5:3 SET in = [#6:3], out = [#6:4,#6:5];
UPDATE #5:4 SET in = [#6:5], out = [#6:6];

