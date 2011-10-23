(function (exports, global, lib) {
	'use strict';

	var extend = global.es5ext.Object.plain.extend.call,
		isFunction = global.es5ext.Function.isFunction;
	
	exports.DispatcherAdapter = function DispatcherAdapter(container_el) {
		// used for fixing the 
		// difference between Fx and Chrome - Fx does not 
		// automaticly fire onpopstate on page load
		var _popped = false;

		var _methodedPath = function _methodedPath(method, path) {
			return method + path;
		};

		var _isInternalPath = function _isInternalPath(path) {
			return path.indexOf('/') === 0;
		};

		var dispatcher = global.crossroads.create();
		dispatcher._sAddRoute = dispatcher.addRoute;
		dispatcher = extend(dispatcher, {
			/*
			 * @param fn {Function|Object} callback or params
			 */
			get: function get(path, fn) {
				if (typeof fn === 'function') {
					this.addRoute('get', path, fn);
				}
				else  {
					this.parse('get', this._create(path, fn || {}));
				}
			},

			post: function post(path, obj) {
				if (isFunction(obj)) {
					this.addRoute('post', path, obj);
				}
				else {
					this.parse('post', path, obj);
				}
			},

			parse: function parse(method, path, post_params) {
				global.DEBUG && console.log('Dispatcher parsing: ' + path);

				var request = _methodedPath(method, path),
					route = this._getMatchedRoute(request),
					//getParamsArray ommision disables routes rules.normalize option
					params = {
						get: route ? route._getParamValuesObject(request) : null,
						post: post_params || null,
						method: method
					};

				global.history.pushState(params, '', path);

				if (route) {
					params ? route.matched.dispatch.call(route.matched, params) : route.matched.dispatch();
					this.routed.dispatch(path, route, params);
				}
				else {
					global.DEBUG && console.log('Page "' + path + '" not found');
					this.bypassed.dispatch(path, params);
				}
			},

			addRoute: function addRoute(method, path, fn) {
				this._sAddRoute(_methodedPath(method, path), fn);
			},

			_create: function _create(path, params) {
				for (var p in params) {
					path = path.replace('{' + p + '}', params[p]);
				}
				return path;
			}
		});

		dispatcher.constructor = DispatcherAdapter;

		container_el.delegate('click', 'a', function onDelegate(event, el) {
			var path = el.getAttribute('href');
			if (_isInternalPath(path)) {
				event.preventDefault();
				dispatcher.get(path);
			}
		});

		container_el.delegate('submit', 'form', function onSubmit(event, el) {
			event.preventDefault();

			// TODO serialize form and dispatch post
		});

		global.addEventListener('popstate', function onPopstate(event) {
			var state = event.state,
				method = state && state.method ? state.method : 'get',
				location = global.location;

			_popped = true;
		
			dispatcher[method](location.pathname + location.search + location.hash);
		});

		dispatcher.bypassed.add(function (path, params) {
			global.history.pushState(params, '', path);
			alert('Błąd. Strona o podanym adresie nie istnieje.');
		});

		// fixing difference in behaviour on page load
		// see comment for _popped
		setTimeout(function() {
			if (!_popped) {
				dispatcher.get(location.pathname + location.search + location.hash);
			}
		}, 100);

		return dispatcher;
	};

}.call({}, this.app.lib, this, this.app.lib));
