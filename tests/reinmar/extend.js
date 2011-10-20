'use strict';

var superMerge = function (parent, child) {
	var new_prop;
	Object.keys(child).forEach(function (p) {
		if (typeof child[p] === 'function' && typeof parent[p] === 'function') {
			new_prop = p.replace(/^_/, '');
			new_prop = '_s' + new_prop[0].toUpperCase() + new_prop.substr(1);
			parent[new_prop] = parent[p];
		}
		parent[p] = child[p];
	});

	return parent;
};

var classy = function classy(constructor, prototype, statics) {
	var parent = (typeof this === 'undefined' ? function () { return {}; } : this);

	constructor.prototype = superMerge(new parent(), prototype);
	statics && superMerge(constructor, statics);
	constructor.new = function () {
		var obj = new constructor;
		obj.init && obj.init.apply(obj, arguments);
		return obj;
	};
	constructor.create = classy;

	return constructor;
};




var Class1 = classy(function Class1() {},
	{
		v1: 11,
		v2: 12,
		_method1: function () {
			console.log('Class1.method1');
		},
		method2: function () {
			console.log('Class1.method2');
		},
		init: function (a, b, c) {
			console.log('Class1.init:', a, b, c);
		}
	},
	{
		s1: 11,
		s2: 12,
		static1: function () {},
		static2: function () {}
	}
);

var Class2 = Class1.create(function Class2() {},
	{
		_method1: function () {
			this._sMethod1();
			console.log('Class2.method1');
		},

		init: function (a, b, c, d) {
			this._sInit(a, b, d);
			console.log('Class2.init:', d);
			this._method1();
			this.method2();
		}
	},
	{
	}
);

var Class3 = Class2.create(function Class3() {},
	{	
		v1: 31,
		init: function () {
			console.log('Class3.init');
		}
	}
);

console.log('----------');
Class1.new(1, 2, 3);
console.log('----------');
Class2.new(6, 7, 8, 9);
console.log('----------');
var obj = Class3.new();
for (var p in obj) { console.log('> ' +p); console.log(obj[p]); }
