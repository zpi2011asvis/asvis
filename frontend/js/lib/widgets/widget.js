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
		signals: {
			destroyed: null,
		},
		_container_el: null,
		_data: null,
		_dirty: true,
		_dirty_keys: null,
		_view: null,

		init: function init(container_el, position) {
			this._container_el = container_el;
			this.signals = {
				destroyed: new Signal()
			};
			this._data = {};
			this._dirty_keys = [];
			this._view = this.constructor.View.new(container_el, position || 'bottom');
			this._init && this._init();
		},

		destroy: function destroy() {
			this._view.destroy();
			this._view = null;
			this.signals.destroyed.dispatch();
		},

		set: function set(key, data) {
			this._data[key] = data;
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
				obj.signals.destroyed.add(function () {
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
		// array of { target: els, type: 'click', callback: fn } for all subscribed events
		_events: null,


		init: function init(container_el, position) {
			this._cel = container_el;
			this._position = position;
			this._tpls = Templates;
			this._events = [];
			this._init && this._init();
		},

		destroy: function destroy() {
			this._unsubsribeAllEvents();

			this._el && this._el.html('remove');
			this._el = this._cel = null;
		},

		_addEvent: function _addEvent(els, type, callback) {
			// TODO handle custom events (onscroll, ondrag)
			// these xui's methods should return object with
			// one method - un()
			
			this._events.push({
				target: els,
				type: type,
				callback: callback
			});
			els.on(type, callback);
		},

		_unsubsribeAllEvents: function _unsubsribeAllEvents() {
			this._events.forEach(function (event) {
				event.target.un(event.type, event.callback);
			});
			this._events = [];
		}
	});

	Widget.View = View;
	exports.Widget = Widget;

}.call({}, this.app.lib.widgets, this, this.app.lib));
