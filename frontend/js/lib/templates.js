(function (exports, global, lib) {
	'use strict';

	var EJS = global.EJS;

	var _templates = {};

	var Templates = {
		load: function load(els) {
			els.each(function (el) {
				_templates[el.dataset.name] = el.innerHTML;
			});
		},

		get: function get(name) {
			return new EJS({ text: _templates[name], name: name });
		},

		render: function render(name, data) {
			return this.get(name).render(data);
		},

		update: function update(el, name, data) {
			return this.get(name).update(el, data);
		}
	};

	exports.Templates = Templates;

}.call({}, this.app.lib, this, this.app.lib));
