(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		Vizir = lib.Vizir,
		CameraMan = lib.CameraMan,
		GodsFinger = lib.GodsFinger,
		requestAnimationFrame = global.util.requestAnimationFrame;

	var Renderer = function Renderer(widget_view, opts) {
		// consts
		var that = this,
			SPRITE = {
				NODE: T.ImageUtils.loadTexture(app.opts.root + 'img/square_1.png')
			},
			MATERIAL = {
				NODE: new T.ParticleBasicMaterial({
					color: 0x77FF77,
					sizeAttenuation: false,			// true - enable perspective (what's farther is smaller)
					map: SPRITE.NODE
				}),
				LINE: new T.LineBasicMaterial({
					color: 0xFFFFFF,
				})
			},
			REFRESHING_STOP = {
				DELAY: 1000,						// how long after any action performed should rendering go on
				CHECK_INTERVAL: 500					// how often check if any action was performed
			}; 

		// properties
		var	_renderer,
			_scene,
			_control_object,						// main Object3D for control elements
			_graph_object,							// main Object3D for graph elements
			_graph_objects,							// array of Object3Ds
			_vizir,
			_components = [],
			_graph,									// whole (with orders) graph data
			_nodes_object,							// ParticleSystem with nodes
			_camera_man = null,
			_colliders,
			_gods_finger,
			// state -----------------------------------------------------------
			_started = false,
			_last_action = 0,						// last action timestamp
			_long_delay = 0,
			_refreshing_interval = null,
			_next_component_id = 0,
			_dirty_vertices = false,				// dirty nodes vertices (when fba works)
			_settings = {
				lines_opacity: 20,
				show_nodes: true,
				nodes_size: 50,
				fog_near: 50,
				fog_far: 50
			};

		// methods
		var _refresh,
			_initControlObject,
			_initGraphObject,
			_nver,
			_nvec,
			_markAsRoot,
			_updateFromSettings;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.destroy = function destroy() {
			this.stop();
			_vizir && _vizir.destroy();
			_camera_man && _camera_man.destroy();
			_renderer = _scene = _control_object = _graph_object = null
			_graph_objects = _vizir = _camera_man = widget_view = null;
		};

		this.getEl = function getEl() {
			return _renderer.domElement;
		};

		this.getSettings = function getSettings () {
			return _settings;
		};

		this.start = function start() {
			that = this;
			_last_action = +new Date();

			if (!_refreshing_interval) {
				_refreshing_interval = global.setInterval(function () {
					// if long delay is active - count with it, otherwise with standard one
					if (_last_action + (_long_delay || REFRESHING_STOP.DELAY) < +new Date()) {
						that.stop();
					}					
				}, REFRESHING_STOP.CHECK_INTERVAL);
			}
			
			if (_started) {
				return;
			}

			global.DEBUG2 && console.log('Start rendering');
			_started = true;
			_refresh();
		};

		this.startLong = function startLong(stop_delay) {
			_long_delay = stop_delay || 1e8; // if delay not given set 1e8 (~1666 minutes)
			this.start();
		};

		/*
		 * Cancel long run state and continue normal one
		 */
		this.stopLong = function stopLong() {
			_long_delay = 0;
			_last_action = +new Date();
		};

		this.stop = function stop() {
			global.DEBUG2 && console.log('Stop rendering');
			_long_delay = 0;
			_started = false;
			global.clearInterval(_refreshing_interval);
			_refreshing_interval = null;
		};
	
		/*
		 * Refresh only once
		 */
		this.refresh = function refresh() {
			if (_started) return;

			_started = true;
			_refresh();
			_started = false;
		};

		/*
		 * @param graph {Object} result of /structure/graph
		 * @param root {Integer} root vertex number
		 */
		this.setStructure = function setStructure(graph, root) {
			var SphereCollider = T.SphereCollider,
				verts_geometry = new T.Geometry(),
				edges_geometry = new T.Geometry(),
				psystem = new T.ParticleSystem(verts_geometry, MATERIAL.NODE),
				line = new T.Line(edges_geometry, MATERIAL.LINE),
				vertices,
				edges,
				sc, v,
				i, il;

			_graph = graph.structure;
			_nodes_object = psystem;

			// clear graph object3d
			_graph_objects.forEach(function (obj) {
				_graph_object.remove(obj);
			});
			_graph_objects = [];
			T.Collisions.colliders = _colliders = [];

			_vizir.clear().setGraph(graph).setRoot(root).recalculate();

			vertices = _vizir.getVertices();
			edges = _vizir.getEdges();

			for (i = 0, il = vertices.length; i < il; i++) {
				v = vertices[i];
				verts_geometry.vertices.push(v);
				sc = new SphereCollider(v.position, 5);
				// TODO let sc know about node. but how?!
				_colliders.push(sc);
			}

			for (i = 0, il = edges.length; i < il; i++) {
				edges_geometry.vertices.push(edges[i]);
			}

			line.type = T.Lines;
			_graph_object.add(psystem);
			_graph_object.add(line);
			_graph_objects.push(psystem, line);
	
			_markAsRoot(graph.structure[root].pos);	
		};

		this.addComponents = function addComponents(components) {
			var components_object = new T.Object3D();

			components.forEach(function (params) {
				components_object.add(Components[params.class](_graph, params));
			});
			
			_graph_object.add(components_object);
			_components[_next_component_id] = components_object;
			this.refresh();

			return _next_component_id++;
		};

		this.removeComponent = function removeComponent(id) {
			var components_object = _components[id];

			if (!components_object) {
				return console.log(new Error('Kompoment o podanym ID nie istnieje'));
			}
			
			_graph_object.remove(components_object);
			this.refresh();
		};

		this.setSettings = function setSettings(settings) {
			_settings = settings;
			_updateFromSettings();
		};

		this.runFBA = function runFBA(time) {
			_vizir.runFBA(time);
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_nver = function _nver(x, y, z) {
			return new T.Vertex(_nvec(x, y, z));
		};

		_nvec = function _nvec(x, y, z) {
			return new T.Vector3(x, y, z);
		};

		_refresh = function _refresh() {
			if (_started) {
				if (_dirty_vertices) {
					_graph_objects.forEach(function (obj) {
						obj.geometry.__dirtyVertices = true;
					});
				}
				_renderer.render(_scene, _camera_man.camera);
				requestAnimationFrame(_refresh, that.getEl());
			}
		};

		_initControlObject = function _initControlObject() {
			 var line_material = new T.LineBasicMaterial({
					color: 0x4444AA,
					opacity: 0.5
				}),
				line_geometry = new T.Geometry(),
				circle_geometry = new T.Geometry(),
				line = new T.Line(line_geometry, line_material),
				circle = new T.Line(circle_geometry, line_material),
				control_obj,
				i, il;
	
			// x, y, z axes
			line_geometry.vertices.push(
				_nver(-2000, 0, 0), _nver(2000, 0, 0),
				_nver(0, -2000, 0), _nver(0, 2000, 0),
				_nver(0, 0, -2000), _nver(0, 0, 2000)
			);
			line.type = T.Lines;
	
			// flat elipse
			for (i = 0, il = Math.PI * 2 + 0.1; i < il; i += 0.1) {
				circle_geometry.vertices.push(_nver(
					Math.sin(i) * 400,
					0,
					Math.cos(i) * 200
				));
			}
			
			control_obj = new T.Object3D();
			control_obj.add(line);
			control_obj.add(circle);
			_scene.add(control_obj);
			
			_control_object = control_obj;
		};

		_initGraphObject = function _initGraphObject() {
			_graph_object = new T.Object3D();
			_graph_objects = [];
			_scene.add(_graph_object);
		};

		_markAsRoot = function _markAsRoot(root_pos) {
			var root_geometry = new T.SphereGeometry(4, 10, 10),
				root_mesh = new T.Mesh(root_geometry, new T.MeshBasicMaterial({ color: 0xFF2222 }));

			root_mesh.position = root_pos;

			_graph_object.add(root_mesh);
			_graph_objects.push(root_mesh);
		};

		_updateFromSettings = function _updateFromSettings() {
			var s = _settings;
			MATERIAL.LINE.opacity = s.lines_opacity / 100;
			MATERIAL.NODE.size = s.nodes_size / 10;
			_nodes_object && (_nodes_object.visible = s.show_nodes);
			_camera_man && _camera_man.setFog(s.fog_near, s.fog_far);
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		try {
			_renderer = new T.WebGLRenderer({
				antialias: true,
				clearAlpha: 0,
			});
		}
		catch (e) {
			_renderer = new T.CanvasRenderer({
				antialias: false,
				clearAlpha: 0
			});
			alert(
				'Your browser doesn\'t support WebGL. ' +
				'Visualization\'s going to burn your CPU!'
			);
		}
		_scene = new T.Scene();
		_vizir = new Vizir();
		_updateFromSettings();

		_initGraphObject()
		_initControlObject();

		_camera_man = new CameraMan(_renderer, _scene, [ _graph_object ], opts.size.width, opts.size.height);
		_gods_finger = new GodsFinger(_camera_man);

		widget_view.signals.resized.add(function (size) {
			_camera_man.resize(size.width, size.height);
		});
		widget_view.signals.action_performed.add(function () {
			that.start();
		});
		widget_view.signals.scrolled.add(function (down, mouse_pos) {
			_camera_man.zoom(!down, mouse_pos);
		});
		widget_view.signals.dragged.add(function (change, keys) {
			if (keys.ctrl) {
				_camera_man.move(change);
			}
			else {
				_camera_man.rotate(change);
			}
		});
		widget_view.signals.mouse_moved.add(function (mouse_pos) {
			_gods_finger.onMouseMove(mouse_pos);
		});
		_vizir.signals.started.add(function () {
			that.startLong();
			_dirty_vertices = true;
		});
		_vizir.signals.ended.add(function () {
			that.stopLong();
			_dirty_vertices = false;
		});
		_gods_finger.signals.touched.add(function (collider) {
			console.log('touched', collider);
		});
		_gods_finger.signals.untouched.add(function (collider) {
			console.log('untouched', collider);
		});
	};

	var Components = {
		line: function line(graph, params) {
			// TODO refactor with params.type usage

			var line_material = new T.LineBasicMaterial({
					color: 0xFF2222,
					linewidth: 3,
				}),
				line_geometry = new T.Geometry(),
				line = new T.Line(line_geometry, line_material);

			line_geometry.vertices.push(
				new T.Vertex(graph[params.fromNode].pos), new T.Vertex(graph[params.toNode].pos)
			);
			line.type = T.Lines;

			return line;
		},

		node: function node(graph, params) {
			// TODO refactor with params.type usage
			// TODO why position does not change while fba works?
			
			var geometry = new T.SphereGeometry(3, 10, 10),
				mesh = new T.Mesh(geometry, new T.MeshBasicMaterial({ color: 0xFF2222 }));

			mesh.position = graph[params.forNode].pos;
			
			return mesh;
		}
	};

	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
