(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		requestAnimationFrame = global.util.requestAnimationFrame;

	var Renderer = function Renderer(widget_view, opts) {
		// consts
		var that = this,
			FOG = { color: 0x000000, density: 0.002 },
			PARTICLE = {
				material: new T.ParticleBasicMaterial({
					color: 0x55FF55,
					size: 5
				})
			};

		// properties
		var	_renderer,
			_camera,
			_scene,
			_psystems = [], //particle systems
			_geometries = [], //particle geometries
			_started = false,
			_view = {
				fov: 45,
				fov_step: 3,
				width: null,
				height: null,
				distance: 300,
				distance_step: 20
			};

		// methods
		var _refresh,
			_resize,
			_newCamera,
			_moveCamera;

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
		 * @param as_structure {Boolean} if true this is the part of graph that
		 * should be rendered
		 */
		this.setStructure = function setStructure(graph, as_structure) {
			var was_started = _started,
				geometry = new T.Geometry(),
				psystem = new THREE.ParticleSystem(geometry, PARTICLE.material);

			this.stop();

			for (var p = 0; p < 10000; p++) {
				// create a particle with random
				// position values, -100 -> 100
				var pX = Math.random() * 400 - 200,
					pY = Math.random() * 200 - 100,
					pZ = Math.random() * 200 - 100,
					particle = new T.Vertex(
						new T.Vector3(pX, pY, pZ)
					);

				// add it to the geometry
				geometry.vertices.push(particle);
			}

			_scene.add(psystem);
			_psystems.push(psystem);
			_geometries.push(geometry);

			if (was_started) {
				this.start();
			}
		};


		/*
		 * Privates ------------------------------------------------------------
		 */

		_refresh = function _refresh() {
			if (_started) {
				for (var i = 0, l = _psystems.length; i < l; ++i) {
					_psystems[i].rotation.y += 0.001;
				}


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
			_camera = new T.PerspectiveCamera(_view.fov, _view.width / _view.height, 1, 10000);
			_camera.position.z = _view.distance;
				
			_scene.fog = new T.Fog(FOG.color, +(_view.distance / 3), _view.distance * 2);
			//_scene.fog = new T.FogExp2(FOG.color, FOG.density);
		};

		_moveCamera = function _moveCamera(forward) {
			//_view.fov += (forward ? _view.fov_step : -_view.fov_step);
			_view.distance += (forward ? _view.distance_step : -_view.distance_step);
			_newCamera();
		};


		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = new T.WebGLRenderer({
			antialias: true
		});
		_renderer.setClearColorHex(0x111111, 1.0);
		_scene = new T.Scene();

		_resize(opts.size.width, opts.size.height);


		widget_view.signals.resized.add(function (size) {
			_resize(size.width, size.height);
		});
		widget_view.signals.scrolled.add(function (down) {
			_moveCamera(down);
		});
	};


	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
