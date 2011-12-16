(function (exports, global, lib) {
	'use strict';

	var deferred = global.deferred;

	/*
	 * context: app
	 */
	var Routes = function () {
		var that = this,
			dispatcher = that.dispatcher,
			widgets = lib.widgets,
			curr_number,						// current root number
			curr_depth,							// current depth
			graph_w,							// GraphWidget
			info_w;								// InfobarWidget

		dispatcher.get('/', function routerRoot() {
			var w = widgets.StartFormWidget.new(that._container_el);

			w.set('from', curr_number);

			w.signals.submitted.add(function routerRoot_onSubmit(params) {
				curr_depth = null;
				curr_number = null;
				that.dispatcher.get('/node/{number}/{depth}', params);
			});
			w.signals.closed.add(function routerRoot_onClosed() {
				_backToGraph(w);
			});

			that.widgets.add(w);
			that.render();
		});

		dispatcher.get('/find/paths', function routerFindPaths() {
			var w = widgets.FindPathsFormWidget.new(that._container_el);

			w.set('from', curr_number);

			w.signals.submitted.add(function routerFindPaths_onSubmit(params) {
				that.dispatcher.get('/paths/{from}/{to}/{type}', params);
			});
			w.signals.closed.add(function routerFindPaths_onClosed() {
				_backToGraph(w);
			});

			that.widgets.add(w);
			that.render();
		});

		dispatcher.get('/find/trees', function routerFindTrees() {
			var w = widgets.FindTreesFormWidget.new(that._container_el);

			w.set('from', curr_number);

			w.signals.submitted.add(function routerFindTrees_onSubmit(params) {
				that.dispatcher.get('/trees/{from}/{height}/{type}', params);
			});
			w.signals.closed.add(function routerFindTrees_onClosed() {
				_backToGraph(w);
			});

			that.widgets.add(w);
			that.render();
		});

		dispatcher.get('/paths/{from}/{to}/{type}', function routerPaths(request) {
			var from = +request.get.from,
				to = +request.get.to,
				type = request.get.type,
				depth_left,
				depth_right,
				paths;

			var _loadPaths = function _loadPaths() {
				return that.db.get('structure/graph', {
					number: to,
					depth: depth_right
				})
				(function (graph_data) {
					return [graph_data, paths];
				});
			};
			
			that.db.get('structure/paths', {
				from: from,
				to: to,
				type: type
			})
			(function (data) {
				paths = data.paths;
				depth_left = data.depth_left;
				depth_right = data.depth_right;

				if (curr_number !== from || !curr_depth || curr_depth < depth_left) {
					return _loadGraph(from, data.depth_left, 'paths', _loadPaths);
				}
				else {
					_loadPaths()
					(function (data) {
						graph_w.additionalStructure('paths', data);					
					}).end(that.err);
				}
			}).end(that.err);
		});		

		dispatcher.get('/trees/{number}/{height}/{type}', function routerTrees(request) {
			var number = +request.get.number,
				height = +request.get.height,
				type = request.get.type;

			var _loadTrees = function () {
				return (
					that.db.get('structure/trees', {
						number: number,
						height: height,
						type: type
					})
					(function (data) {
						if (data.distance_order.length === 0) {
							alert('Brak drzew dla podanych kryteriów');
						}

						return data;
					})
				);
			};

			if (curr_number !== number || !curr_depth || curr_depth < height + 1) {
				_loadGraph(number, height + 1, 'trees', _loadTrees).end(that.err);
			}
			else {
				_loadTrees()
				(function (data) {
					graph_w.additionalStructure('trees', data);					
				}).end(that.err);
			}
		});		
	
		dispatcher.get('/node/{number}/{depth}', function routerNode(request) {
			var number = +request.get.number,
				depth = +request.get.depth;

			if (curr_number === number && curr_depth === depth) {
				return;
			}
		
			_loadGraph(number, depth).end(that.err);
		});


		var _backToGraph = function _backToGraph(widget) {
			widget.destroy();
			if (curr_number && curr_depth) {
				that.dispatcher.get('/node/{number}/{depth}', {
					number: curr_number,
					depth: curr_depth
				});
			}
		};

		var _loadGraph = function _loadGraph(number, depth, add_struct_type, add_struct_promise) {
			// TODO remove in the future - should modify current graph
			// and widget should manage what to do with new data
			that.widgets.destroy();

			curr_number = number;
			curr_depth = depth;

			return (
				that.db.get('structure/graph', {
					number: number,
					depth: depth
				})
				(function routerLoadGraph_promise1(graph_data) {
					graph_w = widgets.GraphWidget.new(
						that._container_el.find('#graph_renderer')
					);
					graph_w.set('graph', graph_data);
					graph_w.set('root', number);
					graph_w.set('depth', depth);
					that.widgets.add(graph_w);

					var promises = [
						that.db.get('connections/meta', {
							for_node: number
						}),
						that.db.get('nodes/meta', {
							// don't ask for more than 5000 nodes - get the closest
							numbers: graph_data.distance_order.slice(0, 5000)
						})						
					];
					
					add_struct_type && promises.push(add_struct_promise());
					
					return deferred.all(promises);
				})
				(function routerLoadGraph_promise2(data) {
					var conns_meta = data[0],
						nodes_meta = data[1],
						add_struct = data[2];

					info_w = widgets.InfobarWidget.new(
						that._container_el.find('#sidebar')					
					);
					// TODO add batch set (with object)
					info_w.set('nodes_meta', nodes_meta);
					graph_w.set('nodes_meta', nodes_meta);
					info_w.set('connections_meta', conns_meta);
					info_w.set('root', number);
					info_w.set('depth', depth);

					// additional structure is set
					if (add_struct_type) {
						graph_w.bufferStructure(add_struct_type, add_struct);
					}
					
					that.widgets.add(info_w);
					that.render();

					info_w.signals.connection_hovered.add(function (from, to) {
						graph_w.markConnectionTo(from, to);
					});
					info_w.signals.connection_unhovered.add(function () {
						graph_w.unmarkConnection();
					});
				})
			);
		};
	};
	
	exports.Routes = Routes;
}.call({}, this, this, this.app.lib));
