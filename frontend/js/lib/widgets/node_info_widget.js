(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		Renderer = lib.Renderer,
		x = global.x$;

	var NodeInfoWidget = Widget.create(function NodeInfoWidget() {}, {
		signals: {
			destroyed: null,
			hidden: null
		},

		_init: function _init() {
			var that = this;

			that.signals = {
				destroyed: new Signal(),
				hidden: that._view.signals.hidden
			};
		},
		_mouse_pos: null,

		onMouseMove: function onMouseMove(mouse_pos) {
			this._mouse_pos = mouse_pos;
		},

		show: function show() {
			this._view.update(this._data);
			this._view.show(this._mouse_pos);
		},
	
		hide: function hide() {
			this._view.hide();
		}
	},
	{
		multiple: false
	});

	NodeInfoWidget.View = Widget.View.create(function NodeInfoWidgetView() {}, {
		signals: {
			hidden: null
		},

		_hovered: false,
		_hiding_timers: null,

		_init: function _init() {
			this._hiding_timers = [];
			this.signals = {
				hidden: new Signal()
			};
		},

		render: function render(data) {
			if (this._el) return;

			var that = this;

			that._cel.html(
				that._position, 
				that._tpls.render('node_info')
			);
			that._el = that._cel.find('#node_info');

			that._el.on('mouseover', function () {
				that._hovered = true;
			});
			that._el.on('mouseout', function () {
				that._hovered = false;
				that.hide();
			});
		},

		update: function update(data) {
			var as_name_el = this._el.find('.as_name'),
				el;

			this._el.find('.as_num.this')
				.html('#' + data.node_num)
				.attr('href', '/node/' + data.node_num + '/' + data.depth);

			el = this._el.find('.depth_shallower')
				.html(data.depth - 1 + ' (-1)')
				.attr('href', '/node/' + data.node_num + '/' + (data.depth - 1));
			this._el.find('.depth_equal')
				.html(data.depth + ' (+0)')
				.attr('href', '/node/' + data.node_num + '/' + data.depth);
			this._el.find('.depth_deeper')
				.html(data.depth + 1 + ' (+1)')
				.attr('href', '/node/' + data.node_num + '/' + (data.depth + 1));
			if (data.depth <= 1) {
				el.addClass('hidden');
			}
			else {
				el.removeClass('hidden');
			}

			this._el.find('.paths')
				.attr('href', '/paths/' + data.root + '/' + data.node_num + '/both')
				.html('#' + data.root);

			if (data.node_meta) {
				as_name_el.html(data.node_meta.name);
				x$(as_name_el[0].parentElement).removeClass('hidden');
			}
			else {
				x$(as_name_el[0].parentElement).addClass('hidden');
			}
		},

		show: function show(pos) {
			this._hiding_timers.forEach(global.clearTimeout);
			this._hiding_timers = [];
			this._el
				// display box container under the node
				// so nodes between box and this nodes don't gain hover
				.setStyle('left', (pos.x - 2) + 'px')
				.setStyle('top', (pos.y - 2) + 'px')
				.addClass('visible');
		},

		hide: function hide() {
			var that = this;

			that._hiding_timers.push(global.setTimeout(function () {
				if (!that._hovered) {
					that._el
						.removeClass('visible')
						.setStyle('left', -100 + 'px')
						.setStyle('top', -100 + 'px');
					that.signals.hidden.dispatch();
				}
			}, 500));
		}
	});

	exports.NodeInfoWidget = NodeInfoWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
