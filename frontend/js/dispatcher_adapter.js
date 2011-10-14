(function (exports, global) {
	'use strict';

	var extend = global.es5ext.Object.plain.extend.call,
		isFunction = global.es5ext.Function.isFunction;
	
	exports.DispatcherAdapter = function DispatcherAdapter(container_el) {
		var _methodedPath = function (method, path) {
			return method + path;
		};

		var dispatcher = global.crossroads.create();
		dispatcher._superAddRoute = dispatcher.addRoute;
		dispatcher = extend(dispatcher, {
			get: function (path, fn) {
				if (fn)
					this.addRoute('get', path, fn);
				else 
					this.parse('get', path);
			},

			post: function (path, obj) {
				if (isFunction(obj))
					this.addRoute('post', path, obj);
				else 
					this.parse('post', path, obj);
			},

			parse: function (method, path, post_params) {
				var request = _methodedPath(method, path),
					route = this._getMatchedRoute(request),
					params = {
						get: route ? route._getParamsArray(request) : null,
						post: post_params || null,
						method: method
					};

				//TODO improve get params passing (wtf are input and index)

				if (route) {
					params ? route.matched.dispatch.call(route.matched, params) : route.matched.dispatch();
					this.routed.dispatch(path, route, params);
				}
				else {
					this.bypassed.dispatch(path);
				}
			},

			addRoute: function (method, path, fn) {
				this._superAddRoute(_methodedPath(method, path), fn);
			}
		});

		container_el.delegate('click', 'a', function (event, el) {
			event.preventDefault();
		});
		container_el.delegate('submit', 'form', function (event, el) {
			event.preventDefault();
		});

		return dispatcher;
	};

}.call({}, this.app.lib, this));
