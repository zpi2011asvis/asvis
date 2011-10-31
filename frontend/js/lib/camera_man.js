(function (exports, global, lib) {
	'use strict';

	var T = global.THREE;

	var CameraMan = function CameraMan(renderer, width, height) {
		var that = this,
			_renderer,
			_camera,
			_up = new T.Vector3(0, 1, 0),
			_view_width,
			_view_height,
			_eye = new T.Vector3(0, 0, 500),
			_target = new T.Vector3(0, 0, 0);
	
		// methods
		var _newCamera,
			_viewAbs2Rel,
			_nvec;

		/*
		 * Publics -------------------------------------------------------------
		 */

		// settings
		this.ZOOMING_FACTOR = 1.1;
		this.ZOOMING_MOVE_FACTOR = 50;

		// fast getter
		this.camera = null;

		this.zoom = function zoom(is_forward, pointed_at) {
			var right = _nvec().cross(_up, _eye).normalize(),
				rel_pos = _viewAbs2Rel(pointed_at),
				move_vec = _nvec(
					rel_pos.x * this.ZOOMING_MOVE_FACTOR,
					rel_pos.y * this.ZOOMING_MOVE_FACTOR,
					0
				);
			
			_eye.addSelf(move_vec);
			_target.addSelf(move_vec);
			_eye.multiplyScalar(is_forward ? 1 / this.ZOOMING_FACTOR : this.ZOOMING_FACTOR);
			_newCamera();
		};

		this.rotate = function rotate(change) {

		};

		this.move = function move(change) {
		};

		this.resize = function resize(width, height) {
			_view_width = width;
			_view_height = height;
			_renderer.setSize(width, height);
			_newCamera();
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_newCamera = function _newCamera() {
			_camera = new T.PerspectiveCamera(45, _view_width / _view_height, 100, 10000);
			_camera.position = _eye;
			_camera.lookAt(_target);
			that.camera = _camera;
				
			// var distance = _view.camera_position.length();
			//_scene.fog = new T.Fog(FOG.color, ~~(distance / 3), distance * 3);
		};

		_nvec = function _nvec(x, y, z) {
			return new T.Vector3(x, y, z);
		};

		/* 
		 * axis - (1, 1)  - (right, top); (0, 0) - (center, center)
		 */
		_viewAbs2Rel = function _viewAbs2Rel(pos_abs) {
			var half_x = _view_width / 2,
				half_y = _view_height / 2;
			return {
				x: (pos_abs.x / half_x -  1),
				y: -(pos_abs.y / half_y -  1)
			};
		};


		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = renderer;
		this.resize(width, height);
	};

	exports.CameraMan = CameraMan;

}.call({}, this.app.lib, this, this.app.lib));
