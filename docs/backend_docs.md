# Shalala

## Parsowanie wyników z OrientDB

Kolejność działań:

1. json_decode
2. ObjectsMapper

## ObjectsMapper

Zwraca AS\Graph

Kolejność działań:

1. przepisanie drzewiastej struktury na strukturę array(rid{string} => AS\Node)
    * AS\Node jednak ma tablice in i out zawierające ridy
2. przepisanie tablicy na porządaną wynikową (in, out zawierające inty (numy) i indeksowaną po numach)
3. `return new AS\Graph($struct)`

## AS\Structure

* `get(num) -> AS\Node`
* `toJSON() -> array(num{int} => ASNode)`

## AS\Graph extends AS\Structure

* `toJSON() -> array(structure => array(int => ASNode), weight_order => array(int), distance_order => array(int))`

