this.app.lib.widgets = {};

(function (exports, global, lib) {
	'use strict';

	var Signal = global.signals.Signal,
		classy = global.util.classy,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call,
		create = global.es5ext.Object.plain.create;

	var Widget = classy(function Widget(el, position) {}, {
		destroyed: null,
		_el: null,
		_position: null,

		init: function init(el, position) {
			this._el = el;
			this._position = position;
			this.destroyed = new Signal();
		},

		destroy: function destroy() {
			this.destroyed.dispatch();
		},
	},
	{
		//are multiple instances of this widget allowed
		multiple: true,
		_repository: null,
	
		new: function neww() {
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
				obj = this._sNew.apply(this, arguments);
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
		}
	});

	var Renderer = classy(function WidgetRenderer() {}, {
	});

	Widget.Renderer = Renderer;
	exports.Widget = Widget;

}.call({}, this.app.lib.widgets, this, this.app.lib));
