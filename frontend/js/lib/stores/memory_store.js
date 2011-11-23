(function (exports, global, lib) {
	'use strict';

	var Store = lib.stores.Store,
		merge = global.es5ext.Object.plain.merge.call,
		clone = global.es5ext.Object.plain.clone.call;
	
	var MemoryStore = Store.create(function MemoryStore() {}, {
		_cache: {},

		init: function init(opts) {
			this._cache = {};
			this._sInit(opts);
		},

		get: function get(opts) {
			var that = this,
				params = clone(opts.params),
				resource_name = opts.resource_name,
				key_field = opts.key_field,
				type_array = (opts.type === 'array'),
				type_object = (opts.type === 'object'),
				item_id,
				key_values,
				not_found_keys,
				not_found_keys_indexes,
				not_found_items_ids,
				cached_results,
				value,
				d;
	
			// no cache - forward
			if (!opts.cache) {
				return that._next.get(opts);
			}
	
			// init cache for this resource if not done yet
			if (!that._cache[resource_name]) {
				that._cache[resource_name] = {};
			}
			
			if (type_array || type_object) {
				key_values = params[key_field];
				cached_results = type_array ? [] : {};
				not_found_keys_indexes = [];
				not_found_items_ids = [];

				not_found_keys = key_values.reduce(function (acc, key_value, key_index) {
					params[key_field] = key_value;
					item_id = that._generateItemID(params);

					if (that._hasItem(resource_name, item_id)) {
						cached_results[type_array ? key_index : key_value] = that._getItem(resource_name, item_id);
					}
					else {
						acc.push(key_value);
						not_found_keys_indexes.push(key_index);
						not_found_items_ids.push(item_id);
					}

					return acc;
				}, []);
	
				// everything cached
				if (not_found_keys.length === 0) {
					d = global.deferred();
					d.resolve(cached_results);
					return d.promise;
				}
				else {
					// replace params with the new - trimmed one
					params[key_field] = not_found_keys;
					opts.params = params;
				}
			}
			// plain type
			else {
				item_id = that._generateItemID(params);
				if (that._hasItem(resource_name, item_id)) {
					d = global.deferred();
					d.resolve(that._getItem(resource_name, item_id));
					return d.promise;
				}
			}
		
			// query next store		
			d = that._next.get(opts);

			// handle (cache and merge if needed) results
			d = d(function (uncached_results) {
				var ur_i, ur_il, key;

				if (type_array) {
					ur_il = uncached_results.length;

					// merge uncached_results into cached_results using proper keys
					for (ur_i = 0; ur_i < ur_il; ++ur_i) {
						cached_results[not_found_keys_indexes[ur_i]] = uncached_results[ur_i];
						that._cacheItem(resource_name, not_found_items_ids[ur_i], uncached_results[ur_i]);
					}
				}
				else if (type_object) {
					ur_i = 0;

					// merge uncached_results into cached_results using proper keys
					for (key in uncached_results) {
						cached_results[key] = uncached_results[key];
						that._cacheItem(resource_name, not_found_items_ids[ur_i], uncached_results[key]);
						ur_i += 1;
					}
				}				
				// plain type
				else {
					that._cacheItem(resource_name, item_id, uncached_results);
					cached_results = uncached_results;
				}

				return cached_results;
			});

			return d;
		},

		_generateItemID: function _generateItemID(params) {
			return JSON.stringify(params);
		},

		_getItem: function _getItem(resource_name, item_id) {
			var value = this._cache[resource_name][item_id];

			return JSON.parse(value);
		},

		_hasItem: function _hasItem(resource_name, item_id) {
			return (item_id in this._cache[resource_name]);
		},

		_cacheItem: function _cacheItem(resource_name, item_id, value) {
			this._cache[resource_name][item_id] = JSON.stringify(value);
		}
	});

	exports.MemoryStore = MemoryStore;

}.call({}, this.app.lib.stores, this, this.app.lib));
