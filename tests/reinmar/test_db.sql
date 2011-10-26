INSERT INTO ASNode (num) VALUES (0);
INSERT INTO ASNode (num) VALUES (1);
INSERT INTO ASNode (num) VALUES (2);
INSERT INTO ASNode (num) VALUES (3);
INSERT INTO ASNode (num) VALUES (4);

INSERT INTO ASConn (in, out, up) VALUES (#5:0, #5:1, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:0, #5:2, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:1, #5:2, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:2, #5:3, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:3, #5:1, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:3, #5:4, true);
INSERT INTO ASConn (in, out, up) VALUES (#5:4, #5:1, true);

UPDATE #5:0 SET out = [#6:0,#6:1];
UPDATE #5:1 SET in = [#6:0,#6:4,#6:6], out = [#6:2];
UPDATE #5:2 SET in = [#6:1,#6:2], out = [#6:3];
UPDATE #5:3 SET in = [#6:3], out = [#6:4,#6:5];
UPDATE #5:4 SET in = [#6:5], out = [#6:6];


