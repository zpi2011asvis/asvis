(function (exports, global, lib) {
	'use strict';

	var T = global.THREE;

	var CameraMan = function CameraMan(renderer, width, height) {
		var that = this,
			_renderer,
			_width,
			_height,
			_camera,
			_view = {
				fov: 45,
				width: null,
				height: null,
				camera_position: new T.Vector3(0, 0, 500),
				zooming_factor: 1.1
			};
	
		// methods
		var _newCamera;

		/*
		 * Publics -------------------------------------------------------------
		 */

		// fast getter
		this.camera = null;

		this.zoom = function zoom(forward) {
			_view.camera_position.multiplyScalar(forward ? _view.zooming_factor : 1/_view.zooming_factor);
			_newCamera();
		};

		this.rotate = function rotate(change) {

		};

		this.move = function move(change) {
		};

		this.resize = function resize(width, height) {
			_width = width;
			_height = height;
			_renderer.setSize(_width, _height);
			_newCamera();
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_newCamera = function _newCamera() {
			_camera = new T.PerspectiveCamera(_view.fov, _width / _height, 100, 10000);
			_camera.position = _view.camera_position;
			that.camera = _camera;
				
			var distance = _view.camera_position.length();
			//_scene.fog = new T.Fog(FOG.color, ~~(distance / 3), distance * 3);
		};


		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = renderer;
		this.resize(width, height);
	};

	exports.CameraMan = CameraMan;

}.call({}, this.app.lib, this, this.app.lib));
