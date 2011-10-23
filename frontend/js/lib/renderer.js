(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		requestAnimationFrame = global.util.requestAnimationFrame;

	var Renderer = function Renderer() {
		var _width = 700,
			_height = 500,
			_bg_color = 0x333333,
			_renderer,
			_camera,
			_scene,
			_started = false,
			_refresh;

		_renderer = new T.WebGLRenderer({
			antialias: true
		});
		_renderer.setSize(_width, _height); // TODO
		_renderer.setClearColorHex(_bg_color, 1.0);
		_renderer.clear();

		_camera = new T.PerspectiveCamera(45, _width/_height, 1, 10000);
		_camera.position.z = 300;

		_scene = new T.Scene();
		var cube = new T.Mesh(
			new T.CubeGeometry(50,50,50),
			new T.MeshBasicMaterial({color: 0x000000})
		);
		_scene.add(cube);

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
			var was_started = _started;

			this.stop();



			if (was_started) {
				this.start();
			}
		};


		/*
		 * Privates ------------------------------------------------------------
		 */

		_refresh = function _refresh() {
			if (_started) {
				_renderer.render(_scene, _camera);
				requestAnimationFrame(_refresh);
			}
		};
	};


	exports.Renderer = Renderer;

}.call({}, this.app.lib, this, this.app.lib));
