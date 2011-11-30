(function (exports, global, lib) {
	'use strict';

	/*
	 * context: app
	 */
	var Routes = function () {
		var that = this,
			dispatcher = that.dispatcher,
			widgets = lib.widgets,
			curr_number, curr_depth;

		dispatcher.get('/', function routerRoot() {
			var w = widgets.StartFormWidget.new(that._container_el);

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
				curr_depth = null;
				curr_number = null;
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
				curr_depth = null;
				curr_number = null;
				that.dispatcher.get('/trees/{from}/{height}/{type}', params);
			});
			w.signals.closed.add(function routerFindTrees_onClosed() {
				_backToGraph(w);
			});

			that.widgets.add(w);
			that.render();
		});

		dispatcher.get('/paths/{from}/{to}/{type}', function routerPaths(request) {
			var from = request.get.from,
				to = request.get.to,
				type = request.get.type;

		});		

		dispatcher.get('/trees/{from}/{height}/{type}', function routerTrees(request) {
			var from = request.get.from,
				height = request.get.height,
				type = request.get.type;

		});		
	
		dispatcher.get('/node/{number}/{depth}', function routerNode(request) {
			var number = request.get.number,
				depth = request.get.depth,
				graph_w, info_w;

			if (curr_number === number && curr_depth === depth) {
				return;
			}

			curr_number = number;
			curr_depth = depth;

			// TODO remove in the future - should modify current graph
			// and widget should manage what to do with new data
			that.widgets.destroy();


			that.db.get('structure/graph', {
				number: number,
				depth: depth
			})
			(function routerNode_promise1(graph_data) {
				graph_w = widgets.GraphWidget.new(
					that._container_el.find('#graph_renderer')
				);
				graph_w.set('graph', graph_data);
				graph_w.set('root', number);
				graph_w.set('depth', depth);

				that.widgets.add(graph_w);
				that.render();

				return deferred.all(
					that.db.get('connections/meta', {
						for_node: number
					}),
					that.db.get('nodes/meta', {
						// don't ask for more than 10000 nodes - get the closest
						numbers: graph_data.distance_order.slice(0, 10000)
					})
				);
			})
			(function routerNode_promise2(data) {
				var conns_meta = data[0],
					nodes_meta = data[1];

				info_w = widgets.InfobarWidget.new(
					that._container_el.find('#sidebar')					
				);
				// TODO add batch set (with object)
				info_w.set('nodes_meta', nodes_meta);
				graph_w.set('nodes_meta', nodes_meta);
				info_w.set('connections_meta', conns_meta);
				info_w.set('root', number);
				info_w.set('depth', depth);
				
				that.widgets.add(info_w);
				that.render();

				info_w.signals.connection_hovered.add(function (from, to) {
					graph_w.markConnectionTo(from, to);
				});
				info_w.signals.connection_unhovered.add(function () {
					graph_w.unmarkConnection();
				});
			}).end(that.err);
		});


		var _backToGraph = function _backToGraph(widget) {
			if (curr_number && curr_depth) {
				widget.destroy();
				that.dispatcher.get('/node/{number}/{depth}', {
					number: curr_number,
					depth: curr_depth
				});
			}
		};
	};
	
	exports.Routes = Routes;
}.call({}, this, this, this.app.lib));
