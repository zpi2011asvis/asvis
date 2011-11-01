(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		deg2Rad = global.util.deg2Rad,
		rad2Deg = global.util.rad2Deg;

	var CameraMan = function CameraMan(renderer, width, height) {
		var that = this,
			_renderer,
			_camera,
			_up = new T.Vector3(0, 1, 0),
			_view_width,
			_view_height,
			_eye = new T.Vector3(0, 0, 500),
			_target = new T.Vector3(0, 0, 0);

		/*
		 * TODO
		 * Set max and min right-axis angle.
		 * Keep not an eye vector, but the right, top (lat, lng) angle
		 * Cache resulting eye vector
		 */
	
		// methods
		var _newCamera,
			_updateCamera,
			_viewAbs2Rel,
			_nvec,
			_getEyeTop,
			_getEyeRight;

		/*
		 * Publics -------------------------------------------------------------
		 */

		// settings
		this.ZOOMING_FACTOR = 1.1;

		// fast getter
		this.camera = null;

		this.zoom = function zoom(is_forward, pointed_at) {
			/*
			var right = _getEyeRight(),
				rel_pos = _viewAbs2Rel(pointed_at),
				move_vec = _nvec(
					rel_pos.x * this.ZOOMING_MOVE_FACTOR,
					rel_pos.y * this.ZOOMING_MOVE_FACTOR,
					0
				);

			//if (!is_forward) {
			//	move_vec.negate();
			//}
			
			_eye.addSelf(move_vec);
			// moving target is a little confusing
			//_target.addSelf(move_vec);
			*/

			_eye.multiplyScalar(is_forward ? 1 / this.ZOOMING_FACTOR : this.ZOOMING_FACTOR);

			// rotating while zooming also is a little bit confusing
			//this.rotate({ x: -rel_pos.x * 5, y: rel_pos.y * 5 });
		};

		this.rotate = function rotate(change) {
			var top = _getEyeTop(),
				right,
				rot_m = new T.Matrix4().setRotationAxis(top, -deg2Rad(change.x));
			_eye = rot_m.multiplyVector3(_eye);

			right = _getEyeRight();
			rot_m = new T.Matrix4().setRotationAxis(right, -deg2Rad(change.y));
			_eye = rot_m.multiplyVector3(_eye);

			_updateCamera();
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
			_updateCamera()
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

		_updateCamera = function _updateCamera() {
			_camera.position = _eye;
			_camera.lookAt(_target);
		};

		_getEyeRight = function _getEyeRight() {
			return _nvec().cross(_up, _eye).normalize();
		};

		_getEyeTop = function _getEyeTop() {
			return _nvec().cross(_eye, _getEyeRight()).normalize();
		};


		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = renderer;
		this.resize(width, height);
	};

	exports.CameraMan = CameraMan;

}.call({}, this.app.lib, this, this.app.lib));
