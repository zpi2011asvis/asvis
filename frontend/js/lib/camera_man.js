(function (exports, global, lib) {
	'use strict';

	var T = global.THREE,
		deg2Rad = global.util.deg2Rad,
		rad2Deg = global.util.rad2Deg;

	var CameraMan = function CameraMan(renderer, scene, moving_objects, width, height) {
		// consts
		var	TARGET = new T.Vector3(0, 0, 0),	// probably redundant
			NORMAL = new T.Vector3(0, 0, 1),
			UP = new T.Vector3(0, 1, 0),
			RIGHT = new T.Vector3(1, 0, 0),
			NEAR = 50,							// for camera
			FAR = 5000;							// for camera

		var that = this,
			_renderer,
			_camera,
			_moving_objects,
			_moving_objects_moved_by,
			_fog,
			_projector,
			// state -----------------------------------------------------------
			_view_width,
			_view_height,
			_lat = 0,			// (-MAX_LAT, MAX_LAT)
			_lng = 0,			// (0, PI*2)
			_distance = 750,	// distance from _target to _eye
			_fog_near = 50,		// 0-100
			_fog_far = 50,		// 0-100
			// cache -----------------------------------------------------------
			_eye;				// cached eye position on sphere

		// methods
		var _nvec,
			_newCamera,
			_updateCamera,
			_updateEye,
			_viewAbs2Rel,
			_viewCentered2Rel,
			_normalizeLatLng,
			_recalculateFog;

		/*
		 * Publics -------------------------------------------------------------
		 */

		// settings
		this.ZOOMING_STEP = 20;
		this.ROTATING_FACTOR = deg2Rad(120);	// rotation for half of a view size drag
		this.MAX_LAT = deg2Rad(60);
		this.MIN_DISTANCE = 100;
		this.DISTANCE_MOVING_FACTOR = 500;		// for this distance 1px mouse move is 1px objects move

		// fast getter
		this.camera = null;

		this.destroy = function destroy() {
		};

		this.zoom = function zoom(is_forward, pointed_at) {
			_distance += (is_forward ? -1 : 1) * this.ZOOMING_STEP;

			if (_distance < this.MIN_DISTANCE) {
				_distance = this.MIN_DISTANCE;
			}

			_updateEye();
			_recalculateFog();
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

			mvec.multiplyScalar(_distance / this.DISTANCE_MOVING_FACTOR);

			_moving_objects.forEach(function (obj) {
				obj.position.addSelf(mvec);
			});
			_moving_objects_moved_by.addSelf(mvec);
		};

		this.resize = function resize(width, height) {
			_view_width = width;
			_view_height = height;
			_renderer.setSize(width, height);
			_newCamera();
		};

		this.setFog = function setFog(near, far) {
			_fog_near = near;
			_fog_far = far;
			_recalculateFog();
		};

		this.getRayForMousePos = function getRayForMousePos(mouse_pos) {
			var x = mouse_pos.x / _view_width * 2 - 1,
				y = -mouse_pos.y / _view_height * 2 + 1,
				vector = new THREE.Vector3(x, y, 0.5),
				camera_pos = _camera.position.clone();

			_projector.unprojectVector(vector, _camera);
	
			// objects moved so test ray also should be
			vector.subSelf(_moving_objects_moved_by);
			camera_pos.subSelf(_moving_objects_moved_by);

			return new T.Ray(camera_pos, vector.subSelf(camera_pos).normalize());
		};

		/*
		 * Privates ------------------------------------------------------------
		 */

		_nvec = function _nvec(x, y, z) {
			return new T.Vector3(x, y, z);
		};

		_newCamera = function _newCamera() {
			_camera = new T.PerspectiveCamera(45, _view_width / _view_height, NEAR, FAR);
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

		_recalculateFog = function _recalculateFog() {
			_fog.near = _distance - 750 + (_fog_near - 50) * 10 + 600;
			_fog.far =  _distance - 750 + (_fog_far - 50) * 15 + 1100;

			if (_fog.near < 1) _fog.near = 1;
			if (_fog.far < 200) _fog.far = 200;
			if (_fog.near > _fog.far) _fog.near = _fog.far;
		};

		/*
		 * Init ----------------------------------------------------------------
		 */

		_renderer = renderer;
		_moving_objects = moving_objects;
		_moving_objects_moved_by = _nvec();

		_fog = scene.fog = new THREE.Fog(0x111111, 1, 1000);
		renderer.setClearColor(_fog.color, 1);

		_projector = new T.Projector();

		_recalculateFog();
	
		this.resize(width, height);
	};

	exports.CameraMan = CameraMan;

}.call({}, this.app.lib, this, this.app.lib));
