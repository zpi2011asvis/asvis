(function (exports, global, lib) {
	'use strict';

	var T = global.THREE;
	
	var Vizir = function Vizir() {
		var _root,
			_graph;
	
		/*
		 * Publics -------------------------------------------------------------
		 */

		this.setGraph = function setGraph(graph) {
			_graph = graph;
		};

		this.setRoot = function setRoot(root) {
			_root = root;
		};

		this.recalculatePositions = function recalculatePositions() {
		};

		this.getVertices = function getVertices() {
		};

		this.getEdges = function getEdges() {
		};
	};

	exports.Vizir = Vizir;

}.call({}, this.app.lib, this, this.app.lib));
