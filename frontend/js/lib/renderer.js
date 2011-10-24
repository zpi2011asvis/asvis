(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		Vizir = lib.Vizir,
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

					return material
				}())
			},
			LINE = {
				material: new THREE.LineBasicMaterial({
					color: 0xFFFFFF,
					lineWidth: 1
				})
			};

		// properties
		var	_renderer,
			_camera,
			_scene,
			_psystems = [], //particle systems
			_geometries = [], //particle geometries
			_vizir,
			_started = false,
			_view = {
				fov: 45,
				width: null,
				height: null,
				camera_position: new T.Vector3(0, 0, 500),
				zooming_factor: 1.1
			},
			_controls;

		// methods
		var _refresh,
			_resize,
			_newCamera,
			_zoomCamera,
			_rotateCamera,
			_panCamera;

		/*
		 * Publics -------------------------------------------------------------
		 */

		this.getEl = function getEl() {
			return _renderer.domElement;
		};

		this.start = function start() {
			if (_started) {
				throw new Error('Renderer is already started');
			}

			_started = true;
			_refresh();
		};

		this.stop = function stop() {
			_started = false;
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

			// needed when point texture has opacity
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
				_controls.update();
				_renderer.render(_scene, _camera);
				requestAnimationFrame(_refresh, that.getEl());
			}
		};

		_resize = function _resize(width, height) {
			_view.width = width;
			_view.height = height;

			_renderer.setSize(width, height);
			_newCamera();
		};

		_newCamera = function _newCamera() {
			_camera = new T.PerspectiveCamera(_view.fov, _view.width / _view.height, 10, 10000);
			_camera.position = _view.camera_position;
				
			var distance = _view.camera_position.length();
			_scene.fog = new T.Fog(FOG.color, ~~(distance / 3), distance * 2);
		};

		_zoomCamera = function _zoomCamera(forward) {
			_view.camera_position.multiplyScalar(forward ? _view.zooming_factor : 1/_view.zooming_factor);
			_newCamera();
		};

		_rotateCamera = function _rotateCamera(change) {

		};

		_panCamera = function _panCamera(change) {
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

		_resize(opts.size.width, opts.size.height);

		_controls = new THREE.TrackballControls(_camera, _renderer.domElement);
		_controls.rotateSpeed = 0.5;
		_controls.zoomSpeed = 0.8;
		_controls.panSpeed = 0.2;


		widget_view.signals.resized.add(function (size) {
			_resize(size.width, size.height);
		});
		/*widget_view.signals.scrolled.add(function (down) {
			_zoomCamera(down);
		});
		widget_view.signals.dragged.add(function (change, keys) {
			if (keys.ctrl) {
				_panCamera(change);
			}
			else {
				_rotateCamera(change);
			}
		});*/
	};


	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
