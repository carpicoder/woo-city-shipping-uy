jQuery(document).ready(function($) {
	'use strict';

	var cities = wcCitySelectAdmin.cities || {};
	var states = wcCitySelectAdmin.states || {};
	var methodsWithZones = wcCitySelectAdmin.methodsWithZones || {};
	var editingRule = wcCitySelectAdmin.editingRule || null;

	// When country changes, update state dropdown and filter methods
	$('#rule_country').on('change', function() {
		var countryCode = $(this).val();
		var $stateSelect = $('#rule_state');

		// Reset state and city
		$stateSelect.html('<option value="">Seleccioná un departamento</option>');
		clearCitiesList();

		if (!countryCode) {
			// Show all methods if no country selected
			filterShippingMethods('', '');
			return;
		}

		// Check if country has states
		if (states[countryCode] && Object.keys(states[countryCode]).length > 0) {
			// Country has states
			$.each(states[countryCode], function(stateCode, stateName) {
				$stateSelect.append('<option value="' + stateCode + '">' + stateName + '</option>');
			});
		} else {
			// Country has no states, load cities directly
			$stateSelect.html('<option value="">No aplica</option>');
			loadCities(countryCode, '');
		}

		// Filter methods by country
		filterShippingMethods(countryCode, '');
	});

	// When state changes, update city checkboxes and filter methods
	$('#rule_state').on('change', function() {
		var countryCode = $('#rule_country').val();
		var stateCode = $(this).val();

		loadCities(countryCode, stateCode);
		
		// Filter methods by country and state
		filterShippingMethods(countryCode, stateCode);
	});

	// Select all cities
	$('#select-all-cities').on('click', function(e) {
		e.preventDefault();
		$('#cities-list-container input[type="checkbox"]').prop('checked', true);
	});

	// Deselect all cities
	$('#deselect-all-cities').on('click', function(e) {
		e.preventDefault();
		$('#cities-list-container input[type="checkbox"]').prop('checked', false);
	});

	function clearCitiesList() {
		var $container = $('#cities-list-container');
		$container.html('<p class="no-cities-message">Seleccioná primero país/departamento para ver las ciudades disponibles.</p>');
		$('#select-all-cities, #deselect-all-cities').hide();
	}

	function loadCities(countryCode, stateCode) {
		var $container = $('#cities-list-container');
		clearCitiesList();

		if (!countryCode) {
			return;
		}

		var countryCities = cities[countryCode];

		if (!countryCities) {
			$container.html('<p class="no-cities-message">No hay ciudades configuradas para este país.</p>');
			return;
		}

		var citiesToShow = [];

		// Check if cities are organized by state or not
		if (Array.isArray(countryCities)) {
			// Cities are a simple array (no states)
			citiesToShow = countryCities;
		} else {
			// Cities are organized by state
			if (stateCode && countryCities[stateCode]) {
				citiesToShow = countryCities[stateCode];
			} else if (!stateCode) {
				$container.html('<p class="no-cities-message">Seleccioná primero un departamento.</p>');
				return;
			} else {
				$container.html('<p class="no-cities-message">No hay ciudades para este departamento.</p>');
				return;
			}
		}

		// Build checkboxes for cities
		if (citiesToShow.length > 0) {
			var html = '';
			$.each(citiesToShow, function(index, cityName) {
				var safeId = 'city_' + index + '_' + cityName.replace(/[^a-zA-Z0-9]/g, '_');
				var escapedCity = $('<div>').text(cityName).html(); // Escape HTML
				html += '<label style="display: block; margin-bottom: 5px;">';
				html += '<input type="checkbox" name="rule_cities[]" value="' + escapedCity + '" id="' + safeId + '">';
				html += ' ' + escapedCity;
				html += '</label>';
			});
			$container.html(html);
			$('#select-all-cities, #deselect-all-cities').show();
		} else {
			$container.html('<p class="no-cities-message">No hay ciudades disponibles.</p>');
		}
	}

	function filterShippingMethods(countryCode, stateCode) {
		var $methodsList = $('#shipping-methods-list');
		var $allMethods = $methodsList.find('.shipping-method-option');
		var visibleCount = 0;

		if (!countryCode) {
			// No country selected, show all methods
			$allMethods.show();
			return;
		}

		// Build the state key in format "COUNTRY:STATE"
		var stateKey = stateCode ? (countryCode + ':' + stateCode) : '';

		$allMethods.each(function() {
			var $label = $(this);
			var methodKey = $label.data('method-key');
			var methodData = methodsWithZones[methodKey];

			if (!methodData) {
				// If no data, hide by default
				$label.hide();
				return;
			}

			var shouldShow = false;

			// If method has no countries defined, it's "rest of the world" - show it
			if (!methodData.countries || methodData.countries.length === 0) {
				shouldShow = true;
			} else {
				// Check if the selected country is in this method's zone
				if (methodData.countries.indexOf(countryCode) !== -1) {
					// Country matches
					if (stateCode && methodData.states && methodData.states.length > 0) {
						// If state is selected and method has specific states, check state match
						if (methodData.states.indexOf(stateKey) !== -1) {
							shouldShow = true;
						}
					} else if (!stateCode || !methodData.states || methodData.states.length === 0) {
						// If no state selected or method has no specific states, show it
						shouldShow = true;
					}
				}
			}

			if (shouldShow) {
				$label.show();
				visibleCount++;
			} else {
				$label.hide();
				// Uncheck hidden methods
				$label.find('input[type="checkbox"]').prop('checked', false);
			}
		});

		// Show message if no methods available
		if (visibleCount === 0) {
			if ($methodsList.find('.no-methods-message').length === 0) {
				$methodsList.append('<p class="no-methods-message" style="color: #d63638;">No hay métodos de envío configurados para este país/departamento. Por favor, configurá una zona de envío primero.</p>');
			}
		} else {
			$methodsList.find('.no-methods-message').remove();
		}
	}

	// Initial filter on page load (show all)
	filterShippingMethods('', '');

	// If editing a rule, populate the form
	if (editingRule) {
		var country = editingRule.country || '';
		var state = editingRule.state || '';
		var ruleCities = editingRule.cities || (editingRule.city ? [editingRule.city] : []);
		var allowedMethods = editingRule.allowed_methods || [];

		// Set country (this will trigger state loading)
		if (country) {
			$('#rule_country').val(country).trigger('change');
			
			// Wait a bit for states to load, then set state
			setTimeout(function() {
				if (state) {
					$('#rule_state').val(state).trigger('change');
				} else {
					// If no state, trigger change to load cities
					$('#rule_state').trigger('change');
				}
				
				// Wait for cities to load, then check the appropriate ones
				setTimeout(function() {
					if (ruleCities.length > 0) {
						ruleCities.forEach(function(cityName) {
							$('#cities-list-container input[type="checkbox"][value="' + cityName + '"]').prop('checked', true);
						});
					}
				}, 300);
			}, 300);
		}

		// Check the allowed methods
		if (allowedMethods.length > 0) {
			allowedMethods.forEach(function(methodKey) {
				$('#shipping-methods-list input[type="checkbox"][value="' + methodKey + '"]').prop('checked', true);
			});
		}
	}
});

