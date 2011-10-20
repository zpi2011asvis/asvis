this.app.lib.widgets = {};

(function (exports, global, lib) {
	'use strict';

	var Signal = global.signals.Signal,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call,
		create = global.es5ext.Object.plain.create;

	var Widget = function Widget(el, position) {
		this._el = el;
		this._position = position;
		this.destroyed = new Signal();
	};

	Widget.prototype = {
		destroyed: null,
		_el: null,
		_position: null,

		destroy: function destroy() {
			this._destroy();
			this.destroyed.dispatch();
		},
	};

	Widget._statics = {
		//are multiple instances of this widget allowed
		multiple: true,

		_repository: null,

		new: function () {
			var obj,
				repository = this._repository;

			// first usage
			if (!repository) {
				this._repository = repository = [];
			}

			// check if only one instance allowed
			// and if it already exists
			if (!this.multiple && repository[0]) {
				obj = repository[0];
			}
			else {
				obj = this(arguments);
				repository.push(obj);
				obj.destroyed.add(function () {
					var i = repository.indexOf(obj);
					if (!~i) {
						throw new Error('Couldn\'t find widget in repository');
					}
					repository.splice(i, 1);
				});
			}
			return obj;
		},
	};

	// simple factory
	Widget.create = function create(constructor, prototype, statics) {
		constructor.prototype = merge(new Widget(), prototype);
		var fn = function () {
			var obj = new constructor();
			Widget.apply(obj, arguments);
			return obj;
		};
		return merge(merge(fn, Widget._statics), statics);
	};


	var Renderer = function WidgetRenderer() {
	};
	Renderer.prototype = {
		b: 1
	};
	Renderer.create = function (constructor, prototype) {
		constructor.prototype = merge(new Renderer(), prototype);
		return function () {
			var obj = new constructor();
			Renderer.apply(obj, arguments);
			return obj;
		}
	};

	Widget.Renderer = Renderer;
	exports.Widget = Widget;

}.call({}, this.app.lib.widgets, this, this.app.lib));
