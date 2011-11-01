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
							size: 10,
							sizeAttenuation: false, // true - enable perspective (far is smaller)
							map: sprite
						});

					return material;
				}())
			},
			LINE = {
				material: new THREE.LineBasicMaterial({
					color: 0xFFFFFF,
					lineWidth: 1,
					opacity: 0.3
				})
			},
			REFRESHING_STOP = {
				DELAY: 2000, // how long after any action performed should rendering go on
				CHECK_INTERVAL: 500 // how often check if any action was performed
			}; 

		// properties
		var	_renderer,
			_scene,
			_psystems = [], // particle systems
			_geometries = [], // particle geometries
			_vizir,
			_started = false,
			_last_action = 0, // last action timestamp
			_refreshing_interval = null,
			_camera_man = null;

		// methods
		var _refresh,
			_addControlElements,
			_nver,
			_nvec;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.getEl = function getEl() {
			return _renderer.domElement;
		};

		this.start = function start() {
			that = this;
			_last_action = +new Date();

			if (!_refreshing_interval) {
				_refreshing_interval = global.setInterval(function () {
					if (_last_action + REFRESHING_STOP.DELAY < +new Date()) {
						that.stop();
					}					
				}, REFRESHING_STOP.CHECK_INTERVAL);
			}
			
			if (_started) {
				return;
			}

			global.DEBUG && console.log('Start rendering');
			_started = true;
			_refresh();
		};

		this.stop = function stop() {
			global.DEBUG && console.log('Stop rendering');
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
			var was_started = _started,
				verts_geometry = new T.Geometry(),
				edges_geometry = new T.Geometry(),
				psystem = new T.ParticleSystem(verts_geometry, PARTICLE.material),
				line = new T.Line(edges_geometry, LINE.material),
				vertices,
				edges;

			this.stop();

			_vizir.clear().setGraph(graph).setRoot(root);

			vertices = _vizir.getVertices();
			for (var i = 0, il = vertices.length; i < il; i++) {
				verts_geometry.vertices.push(vertices[i]);
			}

			edges = _vizir.getEdges();
			for (var i = 0, il = edges.length; i < il; i++) {
				edges_geometry.vertices.push(edges[i]);
			}

			// needed when point's texture has opacity
			// veeerryyy heavy
			//psystem.sortParticles = true;
			_scene.add(psystem);
			_psystems.push(psystem);
			_geometries.push(verts_geometry);

			line.type = T.Lines;
			_scene.add(line);
			_geometries.push(edges_geometry);

			if (was_started) {
				this.start();
			}
		};


		/*
		 * Privates ------------------------------------------------------------
		 */

		_refresh = function _refresh() {
			if (_started) {
				_renderer.render(_scene, _camera_man.camera);
				requestAnimationFrame(_refresh, that.getEl());
			}
		};

		_addControlElements = function _addControlElements() {
			 var line_material = new THREE.LineBasicMaterial({
					color: 0x4444AA,
					lineWidth: 1,
					opacity: 0.75
				}),
				line_geometry = new T.Geometry(),
				circle_geometry = new T.Geometry(),
				line = new T.Line(line_geometry, line_material),
				circle = new T.Line(circle_geometry, line_material);
	
			// x, y, z axis
			line_geometry.vertices.push(
				_nver(-1000, 0, 0), _nver(1000, 0, 0),
				_nver(0, -1000, 0), _nver(0, 1000, 0),
				_nver(0, 0, -1000), _nver(0, 0, 1000)
			);
			line.type = T.Lines;
	
			// flat elipse
			for (var i = 0, l = Math.PI * 2; i < l + 0.1; i += 0.1) {
				circle_geometry.vertices.push(_nver(
					Math.sin(i) * 400,
					0,
					Math.cos(i) * 200
				));
			}

			_scene.add(line);
			_scene.add(circle);
		};

		_nver = function _nver(x, y, z) {
			return new T.Vertex(_nvec(x, y, z));
		};

		_nvec = function _nvec(x, y, z) {
			return new T.Vector3(x, y, z);
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = new T.WebGLRenderer({
			antialias: true,
			clearAlpha: 0,
		});
		_scene = new T.Scene();
		_vizir = new Vizir();
		_camera_man = new CameraMan(_renderer, opts.size.width, opts.size.height);
		
		_addControlElements();

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
	};


	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
