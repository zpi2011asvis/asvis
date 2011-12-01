INSERT INTO ases (ASNum, ASName)
SELECT DISTINCT(ASNum), CONCAT('AS', ASNum) FROM (
	SELECT asnum FROM aspool
	UNION
	SELECT asnum FROM asdown
	UNION
	SELECT asnum FROM asup 
	UNION
	SELECT asnumdown AS asnum FROM asdown
	UNION
	SELECT asnumup AS asnum FROM asup
) AS asnums WHERE asnum > -1;

DELETE FROM ases WHERE asnum NOT IN (
SELECT DISTINCT(asnum) as asnum FROM (
	SELECT asnum FROM asup WHERE asnumup <> -1 UNION
	SELECT asnum FROM asdown WHERE asnumdown <> -1 UNION
	SELECT asnumup AS asnum FROM asup WHERE asnumup <> -1 UNION
	SELECT asnumdown AS asnum FROM asdown WHERE asnumdown <> -1
) AS asnums); 
