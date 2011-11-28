(function (exports, global, lib) {
	'use strict';

	var extend = global.es5ext.Object.plain.extend.call,
		isFunction = global.es5ext.Function.isFunction;
	
	exports.DispatcherAdapter = function DispatcherAdapter(container_el) {
			// used for fixing the 
			// difference between Fx and Chrome - Fx does not 
			// automaticly fire onpopstate on page load
		var _parsed = false;

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
					path = this._create(path, fn || {});
					var params = {
						method: 'get',
						path: path
					};
					global.history.pushState(params, '', path);
					this.parse(params);
				}
			},

			post: function post(path, obj) {
				if (isFunction(obj)) {
					this.addRoute('post', path, obj);
				}
				// obj is post data
				else {
					var params = {
						method: 'post',
						path: path,
						post: obj
					};
					global.history.pushState(params, '', path);
					this.parse(params);
				}
			},

			parse: function parse(params) {
				var path = params.path;
				_parsed = true;

				global.DEBUG && console.log('Dispatcher parsing: ' + path);

				var request = _methodedPath(params.method, path),
					route = this._getMatchedRoute(request);

				if (route) {
					// getParamsArray ommision disables routes rules.normalize option
					params.get = route._getParamValuesObject(request);
					route.matched.dispatch(params);
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
			var l = global.location,
				params = event.state || {};

			// if state provided then use it, defaults otherwise
			params.method = params.method || 'get';
			params.path = params.path || (l.pathname + l.search + l.hash);

			global.DEBUG2 && console.log('Popped state: ' + params.path);
		
			dispatcher.parse(params);
		}, false);

		dispatcher.bypassed.add(function (path, params) {
			// TODO move this to app
			alert('Błąd. Strona o podanym adresie nie istnieje.');
		});

		// fixing difference in behaviour on page load
		// see comment for _parsed
		setTimeout(function() {
			var l = global.location;
			if (!_parsed) {
				dispatcher.get(l.pathname + l.search + l.hash);
			}
		}, 100);

		return dispatcher;
	};

}.call({}, this.app.lib, this, this.app.lib));
