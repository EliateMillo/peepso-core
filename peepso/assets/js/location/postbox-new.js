import $ from 'jquery';
import { hooks, template } from 'peepso';
import { location as locationData } from 'peepsodata';

const API_KEY = locationData.api_key;

class PostboxLocation extends peepso.class('PostboxOption') {
	constructor(postbox) {
		super(postbox);

		this.location = null;

		this.postbox = postbox;
		this.$postbox = postbox.$postbox;
		this.$toggle = this.$postbox.find('[data-ps=option][data-id=location]');
		this.$dropdown = this.$postbox.find('[data-ps=dropdown][data-id=location]');
		this.$input = this.$dropdown.find('input[type=text]');
		this.$loading = this.$dropdown.find('.ps-js-location-loading');
		this.$result = this.$dropdown.find('.ps-js-location-result');
		this.$map = this.$dropdown.find('.ps-js-location-map');
		this.$select = this.$dropdown.find('.ps-js-select');
		this.$remove = this.$dropdown.find('.ps-js-remove');

		// item template
		this.listItemTemplate = template(this.$dropdown.find('[data-tmpl=item]').text());
		this.titleTemplateSingle = template(this.$dropdown.find('[data-tmpl=title_single]').text());
		this.titleTemplateMulti = template(this.$dropdown.find('[data-tmpl=title_multi]').text());

		this.$toggle.on('click', () => this.toggle());
		this.$input.on('input', e => this.onInputInput(e));
		this.$result.on('click', '[data-place-id]', e => this.onItemClick(e));
		this.$select.on('click', e => this.onSelectClick(e));
		this.$remove.on('click', e => this.onRemoveClick(e));

		hooks.addFilter('postbox_data', 'loc', (...args) => this.onPostboxData(...args));
		hooks.addFilter('postbox_title_extra', 'loc', (...args) => this.onPostboxTitle(...args));
		hooks.addFilter('postbox_is_empty', (...args) => this.onFilterIsEmpty(...args));
		hooks.addAction('postbox_reset', 'loc', (...args) => this.onPostboxReset(...args));
	}

	show() {
		super.show();
		this.$input.focus();

		// Initialize on first run.
		if (this.initialize) return;
		this.initialize = true;
		this.$result.empty().append(this.$loading.clone());
		this.search('').done(results => {
			this.updateList(results);
			this.detectLocation().done((lat, lng) => {
				this.updateMarker(null);
				this.updateMap(lat, lng);
				this.$select.hide();
				this.$remove.hide();
			});
		});
	}

	set(location) {
		if (!location) {
			this.location = null;
			this.updateMarker(null);
			this.$toggle.removeClass('pso-postbox__option--active');
			this.$remove.hide();
		} else {
			this.location = location;
			this.$toggle.addClass('pso-postbox__option--active');
			this.$remove.show();
		}

		this.postbox.render();
		this.postbox.$textarea.trigger('input');
	}

	search(input = '') {
		if (!input) return this.searchNearby();

		let token = (this.searchToken = Date.now());

		return $.Deferred(defer => {
			if (!input) token === this.searchToken && defer.reject();

			this.loadService('autocomplete').done(service => {
				service.getPlacePredictions({ input }, (results, status) => {
					if (token === this.searchToken) {
						if (status === 'OK') defer.resolve(results);
						else defer.reject();
					}
				});
			});
		});
	}

	searchNearby() {
		let token = (this.searchToken = Date.now());

		return $.Deferred(defer => {
			this.detectLocation()
				.done((lat, lng) => {
					this.loadService('places').done(service => {
						let request = {
							location: new google.maps.LatLng(lat, lng),
							types: ['establishment'],
							rankBy: google.maps.places.RankBy.DISTANCE
						};

						service.nearbySearch(request, (results, status) => {
							if (token === this.searchToken) {
								if (status === 'OK') defer.resolve(results);
								else defer.reject();
							}
						});
					});
				})
				.fail(() => token === this.searchToken && defer.reject());
		});
	}

	placeDetail(placeId) {
		return $.Deferred(defer => {
			this.placeDetailCache = this.placeDetailCache || {};
			if (this.placeDetailCache[placeId]) {
				return defer.resolve(this.placeDetailCache[placeId]);
			}

			this.loadService('places').done(service => {
				service.getDetails({ placeId }, (place, status) => {
					if (status === 'OK') {
						this.placeDetailCache[placeId] = place;
						defer.resolve(place);
					} else {
						defer.reject();
					}
				});
			});
		});
	}

	onPostboxData(data, postbox) {
		if (postbox === this.postbox) {
			if (this.location) {
				let { name, latitude, longitude } = this.location;
				data.location = { name, latitude, longitude };
			}
		}

		return data;
	}

	onPostboxTitle(list = [], data, postbox) {
		if (postbox === this.postbox) {
			if (this.location) {
				let tmpl = data.mood ? this.titleTemplateMulti : this.titleTemplateSingle;
				let html = tmpl(this.location);
				list.push(html);
			}
		}

		return list;
	}

	onFilterIsEmpty(empty, postbox, data) {
		if (postbox === this.postbox) {
			if (data.location) empty = false;
		}

		return empty;
	}

	onPostboxReset(postbox) {
		if (postbox === this.postbox) {
			this.set(null);
			this.$select.hide();
		}
	}

	onInputInput(e) {
		this.$result.empty().append(this.$loading.clone());

		// Debounce input.
		clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(() => {
			this.search(this.$input.val().trim()).done(results => {
				this.updateList(results);
				this.updateMarker(null);
				this.$select.hide();
				this.$remove.hide();
			});
		}, 1000);
	}

	onItemClick(e) {
		e.preventDefault();
		e.stopPropagation();

		let data = $(e.currentTarget).data();
		this.placeDetail(data.placeId).done(place => {
			let name = data.name;
			let location = place.geometry.location;
			let viewport = place.geometry.viewport;
			let latitude = location.lat();
			let longitude = location.lng();

			this.updateMap(latitude, longitude, viewport);
			this.updateMarker(latitude, longitude);
			this.$select.show();
			this.$remove.hide();

			// Temporariry store location data in this button.
			this.$select.data({ placeId: data.placeId, name, latitude, longitude });
		});
	}

	onSelectClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.$select.hide();
		this.set(this.$select.data());
		this.toggle(false);
	}

	onRemoveClick(e) {
		e.preventDefault();
		e.stopPropagation();

		this.set(null);
		this.toggle(false);
	}

	detectLocation() {
		return $.Deferred(defer => {
			const save = (...args) => {
				this.detectLocationCache = args;
				defer.resolve(...args);
			};

			if (this.detectLocationCache) return defer.resolve(...this.detectLocationCache);

			this.detectLocationByDevice()
				.done((lat, lng) => save(lat, lng))
				.fail(() => {
					this.detectLocationByAPI()
						.done((lat, lng) => save(lat, lng))
						.fail(() => {
							this.detectLocationByIP()
								.done((lat, lng) => save(lat, lng))
								.fail(() => defer.reject());
						});
				});
		});
	}

	detectLocationByDevice() {
		return $.Deferred(defer => {
			if (window.location.protocol !== 'https:') return defer.reject();

			navigator.geolocation.getCurrentPosition(
				pos => defer.resolve(pos.coords.latitude, pos.coords.longitude),
				() => defer.reject(),
				{ timeout: 10000 }
			);
		});
	}

	detectLocationByAPI() {
		return $.Deferred(defer => {
			if (!API_KEY) return defer.reject();

			$.post(`https://www.googleapis.com/geolocation/v1/geolocate?key=${API_KEY}`, coords => {
				defer.resolve(coords.location.lat, coords.location.lng);
			}).fail(error => defer.reject(error));
		});
	}

	detectLocationByIP() {
		return $.Deferred(defer => {
			let success;

			$.ajax({
				url: 'https://ipapi.co/jsonp',
				dataType: 'jsonp',
				success: json => {
					let { latitude, longitude } = json || {};
					if (latitude && longitude) {
						success = true;
						defer.resolve(latitude, longitude);
					}
				},
				complete: () => success || defer.reject()
			});
		});
	}

	loadLibrary() {
		return $.Deferred(defer => {
			if (this.loadLibraryLoaded) return defer.resolve();
			this.loadLibraryCallbacks = this.loadLibraryCallbacks || [];
			this.loadLibraryCallbacks.push(defer);
			if (this.loadLibraryLoading) return;

			let gmapCallback = `fn_${Date.now()}`;
			window[gmapCallback] = () => {
				this.loadLibraryLoaded = true;
				this.loadLibraryLoading = false;
				while (this.loadLibraryCallbacks.length)
					this.loadLibraryCallbacks.shift().resolve();
				delete window[gmapCallback];
			};

			let script = document.createElement('script');
			script.async = true;
			script.type = 'text/javascript';
			script.src = `https://maps.googleapis.com/maps/api/js?libraries=places&key=${API_KEY}&loading=async&callback=${gmapCallback}`;
			document.body.appendChild(script);
		});
	}

	loadService(name) {
		return $.Deferred(defer => {
			this.loadServiceObjects = this.loadServiceObjects || {};
			if (this.loadServiceObjects[name]) defer.resolve(this.loadServiceObjects[name]);

			this.loadLibrary().done(() => {
				if ('autocomplete' === name) {
					this.loadServiceObjects[name] = new google.maps.places.AutocompleteService();
					defer.resolve(this.loadServiceObjects[name]);
				} else if ('places' === name) {
					let div = document.createElement('div');
					document.body.appendChild(div);
					this.loadServiceObjects[name] = new google.maps.places.PlacesService(div);
					defer.resolve(this.loadServiceObjects[name]);
				} else {
					defer.reject();
				}
			});
		});
	}

	updateMap(lat, lng, viewport) {
		this.loadLibrary().done(() => {
			let location = new google.maps.LatLng(lat, lng);

			if (this.map) {
				this.map.setCenter(location);
			} else {
				this.map = new google.maps.Map(this.$map[0], {
					center: location,
					zoom: 15,
					draggable: false,
					scrollwheel: false,
					disableDefaultUI: true
				});
			}

			if (viewport) {
				this.map.fitBounds(viewport);
			} else {
				this.map.setZoom(15);
			}
		});
	}

	updateMarker(lat, lng) {
		// No need to update marker if map is not available.
		if (!this.map) return $.Deferred(defer => defer.resolve());

		this.loadLibrary().done(() => {
			this.marker && this.marker.setMap(null);

			if (this.map && lat && lng) {
				this.marker = new google.maps.Marker({
					position: new google.maps.LatLng(lat, lng),
					map: this.map,
					title: 'You are here (more or less)'
				});
			}
		});
	}

	updateList(list) {
		let html = list.map(item => this.listItemTemplate(this.mapData(item))).join('');
		this.$result.html(html);
	}

	mapData(data) {
		let place_id, name, description;

		if (data.name) {
			place_id = data.place_id;
			name = data.name;
			description = data.vicinity;
		} else {
			place_id = data.place_id;
			description = data.description.split(', ');
			name = description.shift();
			description = description.join(', ');
		}

		return { place_id, name, description };
	}
}

hooks.addAction('postbox_init', 'location', postbox => new PostboxLocation(postbox));
