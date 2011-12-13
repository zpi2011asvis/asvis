(function (global) {

	var tests = {
		'Obsługi listy słów kluczowych zgodnych z ES5': (function () {
			try { 
				var obj = {}; 
				eval('obj = ({ if: 1 })'); 
				return obj['if'] === 1;
			} 
			catch (err) { 
				return false;
			}
		}()),

		'Metody Object.keys()': (function () {
			return (typeof Object.keys === 'function');
		}()),

		'Metod tablicowych forEach(), map(), filter()': (function () {
			return (
				typeof Array.prototype.forEach === 'function' &&
				typeof Array.prototype.map === 'function' &&
				typeof Array.prototype.filter === 'function'
			);
		}()),
	
		'Wsparcia dla WebGL-a': (function () {
			var canvas_el = global.document.createElement('canvas');
			try {
				var r = !!canvas_el.getContext('experimental-webgl');
				return r;
			}
			catch (err) {
				return false;
			}
		}())
	};

	var errors = '',
		errors_count = 0;

	for (var n in tests) {
		if (tests.hasOwnProperty(n)) {
			if (!tests[n]) {
				errors_count += 1;
				errors += '* ' + n + '\n';
			}
		}
	}

	if (errors_count > 0) {
		global.alert('Twoja przeglądarka nie spełnia wymagań aplikacji ASVis. Brak: \n' + errors);
	}

	global.browser_compatible = (errors_count === 0);
}(this));
