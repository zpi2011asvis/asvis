(function (exports, global) {
	'use strict';

	var	exts = {
		first: function first() {
			return this[0];
		},

		last: function last() {
			return this[this.length - 1];
		},

		delegate: function delegate(type, q, fn) {
			var that = this;
			that.on(type, function delegate_on(event) {
				// event target matches given selector
				if (that.find(q).has(event.target).length > 0) {
					fn(event, event.target);
				}
			});
		},

		/*
		 * @param fn {Function} callback(event, down)
		 */
		onscroll: function onscroll(fn) {
			var that = this;

			// reason: http://www.quirksmode.org/dom/events/scroll.html

			// Firefox ;/
			that.on('DOMMouseScroll', function (event) {
				fn(event, event.detail > 0);
			});

			// Chrome, Safari, Opera, MS 8,9 (only on document or element)
			that.on('mousewheel', function (event) {
				fn(event, event.wheelDeltaY < 0);
			});

		},

		ondrag: function ondrag(fn) {
			var that = this,
				last_mouse_poses = [];

			that.each(function (el, i) {
				el.addEventListener('mousedown', function (event) {
					last_mouse_poses[i] = {
						x: event.screenX,
						y: event.screenY
					};
				}, false);

				el.addEventListener('mousemove', function (event) {
					var pos = last_mouse_poses[i];
					if (!pos) return;

					var diff = { x: event.screenX - pos.x, y: event.screenY - pos.y };
					if (diff.x || diff.y) {
						fn(event, diff);
					}

					pos.x = event.screenX;
					pos.y = event.screenY;
				}, false);

				el.addEventListener('mouseup', function (event) {
					last_mouse_poses[i] = null;
				}, false);
			});
		}
	};
	global.xui.extend(exts);
	
}.call({}, this, this));
