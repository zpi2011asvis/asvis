create class Vertex;
create class Edge;
create property Vertex.name string;
create property Vertex.num integer;
create propertu Edge.up boolean;
create property Vertex.out linkset Edge;
create property Vertex.in linkset Edge;
create property Edge.from link Vertex;
create property Edge.to link Vertex;
