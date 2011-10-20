this.app.lib.widgets = {};

(function (exports, global, lib) {
	'use strict';

	var Signal = global.signals.Signal,
		Templates = lib.Templates,
		classy = global.util.classy,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call,
		create = global.es5ext.Object.plain.create;

	var Widget = classy(function Widget() {}, {
		destroyed: null,
		_container_el: null,
		_data: null,
		_dirty: true,
		_dirty_keys: null,
		_view: null,

		init: function init(container_el, position) {
			this._container_el = container_el;
			this.destroyed = new Signal();
			this._data = {};
			this._dirty_keys = [];
			this._view = this.constructor.View.new(container_el, position || 'bottom');
			this._init && this._init();
		},

		destroy: function destroy() {
			this.destroyed.dispatch();
			this._view.destroy();
		},

		set: function set(key, data) {
			this._data[key, data];
			this._dirty = true;
			this._dirty_keys.push(key);
		},

		render: function render() {
			if (!this._dirty) return;
			
			this._view.render(this._data, this._dirty_keys);
			this._dirty = false;
			this._dirty_keys = [];
		}
	},
	{
		// are multiple instances of this widget allowed
		multiple: true,
		_repository: null,
	
		new: function neww() {
			var obj,
				repository = this._repository;

			// first usage
			if (!repository) {
				this._repository = repository = [];
			}

			// check if only one instance is allowed
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

	var View = classy(function WidgetView() {}, {
		_tpls: null,
		// container element
		_cel: null,
		// position ('after', 'bottom', etc) when widget should be inserted
		// in _cel element
		_position: null,
		// main widget element
		_el: null,

		init: function init(container_el, position) {
			this._cel = container_el;
			this._position = position;
			this._tpls = Templates;
			this._init && this._init();
		},

		destroy: function destroy() {
			this._el && this._el.html('remove');
		}
	});

	Widget.View = View;
	exports.Widget = Widget;

}.call({}, this.app.lib.widgets, this, this.app.lib));
