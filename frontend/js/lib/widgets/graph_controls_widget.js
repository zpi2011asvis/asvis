(function (exports, global, lib) {
	'use strict';

	var Widget = lib.widgets.Widget,
		Signal = global.signals.Signal,
		x = global.x$;

	var GraphControlsWidget = Widget.create(function GraphControlsWidget() {}, {
		signals: {
			destroyed: null,
			settings_changed: null
		},

		_init: function _init() {
			var that = this;

			that.signals = {
				destroyed: new Signal(),
				settings_changed: that._view.signals.settings_changed
			};
		}
	},
	{
		multiple: false
	});

	GraphControlsWidget.View = Widget.View.create(function GraphControlsWidgetView() {}, {
		signals: {
			settings_changed: null
		}, 

		_init: function _init() {
			this.signals = {
				settings_changed: new Signal()
			};
		},
			
		render: function render(data) {
			if (this._el) return;

			var that = this,
				fog_near_el,
				fog_far_el,
				show_nodes_el,
				lines_opacity_el,
				nodes_size_el;

			that._cel.html(
				that._position, 
				that._tpls.render('graph_controls')
			);
			that._el = that._cel.find('#graph_controls');

			fog_near_el = that._el.find('.fog .near');
			fog_far_el = that._el.find('.fog .far');
			show_nodes_el = that._el.find('.show_nodes');
			lines_opacity_el = that._el.find('.lines_opacity');
			nodes_size_el = that._el.find('.nodes_size');

			// defaults
			fog_near_el.first().value = data.settings.fog_near;
			fog_far_el.first().value = data.settings.fog_far;
			show_nodes_el.first().value = data.settings.show_nodes;
			lines_opacity_el.first().value = data.settings.lines_opacity;
			nodes_size_el.first().value = data.settings.nodes_size_el;

			[
				fog_near_el,
				fog_far_el,
				show_nodes_el,
				lines_opacity_el,
				nodes_size_el
			].forEach(function (el) {
				el.on('change', function () {
					that.signals.settings_changed.dispatch({
						fog_near:			+fog_near_el.first().value,
						fog_far:			+fog_far_el.first().value,
						show_nodes:			show_nodes_el.first().checked,
						lines_opacity:		+lines_opacity_el.first().value,
						nodes_size:			+nodes_size_el.first().value
					});
				});
			});
		}
	});

	exports.GraphControlsWidget = GraphControlsWidget;
}.call({}, this.app.lib.widgets, this, this.app.lib));
