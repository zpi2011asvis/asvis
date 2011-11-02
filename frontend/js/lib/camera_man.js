(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		deg2Rad = global.util.deg2Rad,
		rad2Deg = global.util.rad2Deg;

	var CameraMan = function CameraMan(renderer, moving_objects, width, height) {
		// consts
		var	TARGET = new T.Vector3(0, 0, 0), // probably redundant
			NORMAL = new T.Vector3(0, 0, 1),
			UP = new T.Vector3(0, 1, 0),
			RIGHT = new T.Vector3(1, 0, 0);

		var that = this,
			_renderer,
			_camera,
			_moving_objects,
			// state -----------------------------------------------------------
			_view_width,
			_view_height,
			_lat = 0,			// (-MAX_LAT, MAX_LAT)
			_lng = 0,			// (0, PI*2)
			_distance = 500,	// distance from _target to _eye
			// cache -----------------------------------------------------------
			_eye;				// cached eye position on sphere

		/*
		 * TODO
		 * Set max and min right-axis angle.
		 * Keep not an eye vector, but the right, top (lat, lng) angle
		 * Cache resulting eye vector
		 */
	
		// methods
		var _nvec,
			_newCamera,
			_updateCamera,
			_updateEye,
			_viewAbs2Rel,
			_viewCentered2Rel,
			_normalizeLatLng;

		/*
		 * Publics -------------------------------------------------------------
		 */

		// settings
		this.ZOOMING_STEP = 20;
		this.ROTATING_FACTOR = deg2Rad(120); // rotation for half of a view size drag
		this.MOVING_FACTOR = 1;
		this.MAX_LAT = deg2Rad(60);
		this.MIN_DISTANCE = 100;

		// fast getter
		this.camera = null;

		this.zoom = function zoom(is_forward, pointed_at) {
			_distance += (is_forward ? -1 : 1) * this.ZOOMING_STEP;

			if (_distance < this.MIN_DISTANCE) {
				_distance = this.MIN_DISTANCE;
			}

			_updateEye();
		};

		this.rotate = function rotate(change) {
			change = _viewCentered2Rel(change);
			_lng -= change.x * this.ROTATING_FACTOR;
			_lat += change.y * this.ROTATING_FACTOR;
			
			_normalizeLatLng();			

			_updateEye();
		};

		this.move = function move(change) {
			var mvec,
				top, // top in eye perspective
				right, // right in eye perspective
				rotm = new T.Matrix4();

			right = _nvec(1, 0, 0);
			rotm.setRotationAxis(UP, _lng).multiplyVector3(right);
			top = _eye.clone().normalize().crossSelf(right);

			top.multiplyScalar(-change.y);
			right.multiplyScalar(change.x);
			mvec = top.add(top, right);

			_moving_objects.forEach(function (obj) {
				obj.position.addSelf(mvec);
			});
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

		_nvec = function _nvec(x, y, z) {
			return new T.Vector3(x, y, z);
		};

		_newCamera = function _newCamera() {
			_camera = new T.PerspectiveCamera(45, _view_width / _view_height, 100, 10000);
			that.camera = _camera;
			_updateEye();
				
			// var distance = _view.camera_position.length();
			//_scene.fog = new T.Fog(FOG.color, ~~(distance / 3), distance * 3);
		};

		/* 
		 * axes - (1, 1)  - (right, top); (0, 0) - (center, center)
		 */
		_viewAbs2Rel = function _viewAbs2Rel(pos_abs) {
			var half_x = _view_width / 2,
				half_y = _view_height / 2;
			return {
				x: (pos_abs.x / half_x - 1),
				y: -(pos_abs.y / half_y - 1)
			};
		};

		_viewCentered2Rel = function _viewCentered2Rel(pos_centered) {
			var half_x = _view_width / 2,
				half_y = _view_height / 2;
			return {
				x: (pos_centered.x / half_x),
				y: -(pos_centered.y / half_y)
			};
		};

		_updateCamera = function _updateCamera() {
			_camera.position = _eye;
			_camera.lookAt(TARGET);
		};

		_updateEye = function _updateEye() {
			var rotm = new T.Matrix4();
				
			if (!_eye) _eye = _nvec();
			_eye.copy(NORMAL);

			rotm.setRotationAxis(UP, _lng).multiplyVector3(_eye);
			rotm.setRotationAxis(_nvec().cross(UP, _eye), _lat).multiplyVector3(_eye);
			_eye.multiplyScalar(_distance);

			_updateCamera();
		};

		_normalizeLatLng = function _normalizeLatLng() {
			if (_lat > that.MAX_LAT) {
				_lat = that.MAX_LAT
			}
			else if (_lat < -that.MAX_LAT) {
				_lat = -that.MAX_LAT;
			}

			var a360 = deg2Rad(360);
			while (_lng < 0) {
				_lng += a360;
			}
			while (_lng > a360) {
				_lng -= a360;
			}
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = renderer;
		_moving_objects = moving_objects;
		this.resize(width, height);
	};

	exports.CameraMan = CameraMan;

}.call({}, this.app.lib, this, this.app.lib));
