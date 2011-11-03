# Backend - docssss

## Libs

### \asvis\lib\Engine

### \asvis\lib\H

### \asvis\lib\Resource

### \asvis\lib\Response

## Orient

### Parsowanie wyników z OrientDB

Kolejność działań:

1. `json_decode()`
2. ObjectsMapper

### \asvis\lib\orient\Engine extends \asvis\lib\Engine

### \asvis\lib\orient\ObjectsMapper

Zwraca AS\Graph

Kolejność działań:

1. przepisanie drzewiastej struktury na strukturę `array(rid{string} => Node)`
    * AS\Node jednak ma tablice in i out zawierające ridy
2. przepisanie tablicy na porządaną wynikową (in, out zawierające inty (numy) i indeksowaną po numach)
3. `return new AS\Graph($structure)`

### \asvis\lib\orient\Structure

* `__construct($structure)` - nic nie robi ze strukturą, tylko ją zapamiętuje
* `get(num)` -> `Node`
* `toJSON()` -> `array(num{int} => Node)`

### \asvis\lib\orient\Graph extends asvis\lib\orient\Structure

* `toJSON()` -> `array(structure => array(int => Node), weight_order => array(int), distance_order => array(int))`

### \asvis\lib\orient\Node

* pola:
    * -rid
    * -num
    * -name
    * out
    * in
    * distance
    * weight
* do prywatnych pól jest automatyczny getter i setter, więc dostęp jak do pól publicznych
