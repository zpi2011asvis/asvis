(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		Vizir = lib.Vizir,
		CameraMan = lib.CameraMan,
		requestAnimationFrame = global.util.requestAnimationFrame;

	var Renderer = function Renderer(widget_view, opts) {
		// consts
		var that = this,
			FOG = { color: 0x000000, density: 0.002 },
			PARTICLE = {
				material: (function () {
					var sprite = T.ImageUtils.loadTexture(app.opts.root + 'img/square_1.png'),
						material = new T.ParticleBasicMaterial({
							color: 0x77FF77,
							size: 5,
							sizeAttenuation: false, // true - enable perspective (what's farther is smaller)
							map: sprite
						});

					return material;
				}())
			},
			LINE = {
				material: new THREE.LineBasicMaterial({
					color: 0xFFFFFF,
					lineWidth: 1,
					opacity: 0.15
				})
			},
			REFRESHING_STOP = {
				DELAY: 2000, // how long after any action performed should rendering go on
				CHECK_INTERVAL: 500 // how often check if any action was performed
			}; 

		// properties
		var	_renderer,
			_scene,
			_control_object,
			_graph_object,
			_graph_objects,
			_vizir,
			// state -----------------------------------------------------------
			_started = false,
			_last_action = 0, // last action timestamp
			_long_delay = 0,
			_refreshing_interval = null,
			_camera_man = null;

		// methods
		var _refresh,
			_initControlObject,
			_initGraphObject,
			_nver,
			_nvec;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.destroy = function destroy() {
			this.stop();
			_vizir && _vizir.destroy();
			_renderer = _scene = _control_object = _graph_object = null
			_graph_objects = _vizir = _camera_man = widget_view = null;
		};

		this.getEl = function getEl() {
			return _renderer.domElement;
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
			_long_delay = stop_delay || 1e5; // if delay not given set 1e8 (~1666 minutes)
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
		 * @param graph {Object} result of /structure/graph
		 * @param root {Integer} root vertex number
		 * @param as_structure {Boolean} if true this is the part of graph that
		 * should be rendered (graph is at once a structure)
		 */
		this.setStructure = function setStructure(graph, root, as_structure) {
			var verts_geometry = new T.Geometry(),
				edges_geometry = new T.Geometry(),
				psystem = new T.ParticleSystem(verts_geometry, PARTICLE.material),
				line = new T.Line(edges_geometry, LINE.material),
				vertices,
				edges,
				i, il;

			// for FBA, TODO - only set by some event fired by FBA
			verts_geometry.dynamic = true;
			edges_geometry.dynamic = true;

			// clear graph object3d
			_graph_objects.forEach(function (obj) {
				_graph_object.remove(obj);
			});
			_graph_objects = [];

			_vizir.clear().setGraph(graph).setRoot(root);

			vertices = _vizir.getVertices();
			for (i = 0, il = vertices.length; i < il; i++) {
				verts_geometry.vertices.push(vertices[i]);
			}

			edges = _vizir.getEdges();
			for (i = 0, il = edges.length; i < il; i++) {
				edges_geometry.vertices.push(edges[i]);
			}

			// needed when point's texture has opacity
			// veeerryyy heavy
			//psystem.sortParticles = true;

			line.type = T.Lines;
			_graph_object.add(psystem);
			_graph_object.add(line);
			_graph_objects.push(psystem, line);
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
				_graph_objects.forEach(function (obj) {
					obj.geometry.__dirtyVertices = true;
				});
				_renderer.render(_scene, _camera_man.camera);
				requestAnimationFrame(_refresh, that.getEl());
			}
		};

		_initControlObject = function _initControlObject() {
			 var line_material = new THREE.LineBasicMaterial({
					color: 0x4444AA,
					lineWidth: 1,
					opacity: 0.75
				}),
				line_geometry = new T.Geometry(),
				circle_geometry = new T.Geometry(),
				line = new T.Line(line_geometry, line_material),
				circle = new T.Line(circle_geometry, line_material),
				control_obj,
				i, il;
	
			// x, y, z axes
			line_geometry.vertices.push(
				_nver(-1000, 0, 0), _nver(1000, 0, 0),
				_nver(0, -1000, 0), _nver(0, 1000, 0),
				_nver(0, 0, -1000), _nver(0, 0, 1000)
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
			
			control_obj = new THREE.Object3D();
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
			/*alert(
				'Your browser doesn\'t support WebGL. ' +
				'Visualization\'s going to burn your CPU!'
			);*/
		}
		_scene = new T.Scene();
		_vizir = new Vizir();

		_initGraphObject()
		_initControlObject();

		_camera_man = new CameraMan(_renderer, [ _graph_object ], opts.size.width, opts.size.height);
		

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
		_vizir.signals.started.add(function () {
			that.startLong();
		});
		_vizir.signals.ended.add(function () {
			that.stopLong();
		});
	};


	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
