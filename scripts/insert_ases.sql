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
