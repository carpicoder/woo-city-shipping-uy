<?php
/**
 * Plugin Name: Envíos por ciudad de Uruguay para WooCommerce
 * Plugin URI:  https://github.com/carpicoder/woo-city-shipping-uy
 * Description: Selector de ciudades para WooCommerce. Muestra un desplegable de ciudades y permite configurar métodos de envío específicos por ciudad en Uruguay.
 * Version:     1.0.0
 * Author:      CarpiCoder
 * Author URI:  https://carpicoder.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woo-city-shipping-uy
 *
 * WC requires at least: 2.2
 * WC tested up to:      9.7
 * Requires at least:   5.0
 * Tested up to:        6.8
 * 
 * Basado en: WC City Select (https://github.com/8manos/wc-city-select)
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
if ( ( is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option( 'active_sitewide_plugins', array() ) ) ) ||
	in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	class WC_City_Select {

		// plugin version
		const VERSION = '1.0.0';

		private $plugin_path;
		private $plugin_url;

		private $cities;
		private $dropdown_cities;

		// option name for shipping rules by city
		private $shipping_rules_option = 'wc_city_select_shipping_rules';

		public function __construct() {
			add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields' ), 10, 2 );
			add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields' ), 10, 2 );
			add_filter( 'woocommerce_form_field_city', array( $this, 'form_field_city' ), 10, 4 );

			//js scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Add HPOS / Blocks compatibility
			add_action('before_woocommerce_init', array($this, 'compatibility'));

			// Admin settings for shipping by city
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			}

			// Filter shipping methods by selected city
			add_filter( 'woocommerce_package_rates', array( $this, 'filter_shipping_rates_by_city' ), 10, 2 );
		}

		public function billing_fields( $fields, $country ) {
			$fields['billing_city']['type'] = 'city';

			return $fields;
		}

		public function shipping_fields( $fields, $country ) {
			$fields['shipping_city']['type'] = 'city';

			return $fields;
		}

		public function get_cities( $cc = null ) {
			if ( empty( $this->cities ) ) {
				$this->load_country_cities();
			}

			if ( ! is_null( $cc ) ) {
				return isset( $this->cities[ $cc ] ) ? $this->cities[ $cc ] : false;
			} else {
				return $this->cities;
			}
		}

		public function load_country_cities() {
			global $cities;

			// Load only the city files the shop owner wants/needs.
			$allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );

			if ( $allowed ) {
				foreach ( $allowed as $code => $country ) {
					if ( ! isset( $cities[ $code ] ) && file_exists( $this->get_plugin_path() . '/cities/' . $code . '.php' ) ) {
						include( $this->get_plugin_path() . '/cities/' . $code . '.php' );
					}
				}
			}

			$this->cities = apply_filters( 'wc_city_select_cities', $cities );
		}

		private function add_to_dropdown($item) {
			$this->dropdown_cities[] = $item;
		}

	public function form_field_city( $field, $key, $args, $value ) {
		// Reset dropdown cities to avoid inheriting from previous calls
		$this->dropdown_cities = null;

		// Do we need a clear div?
			if ( ( ! empty( $args['clear'] ) ) ) {
				$after = '<div class="clear"></div>';
			} else {
				$after = '';
			}

		// Required markup
		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required = ' <abbr class="required" title="requerido">*</abbr>';
			} else {
				$required = '';
			}

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Validate classes
			if ( ! empty( $args['validate'] ) ) {
				foreach( $args['validate'] as $validate ) {
					$args['class'][] = 'validate-' . $validate;
				}
			}

			// field p and label
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
			}

			// Get Country
			$country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
			$current_cc  = WC()->checkout->get_value( $country_key );

			$state_key   = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
			$current_sc  = WC()->checkout->get_value($state_key);

			if ($current_cc) {
				// Get cities for selected country
				$countryCities = $this->get_cities($current_cc);

				if (is_array($countryCities)) {
					if (isset($countryCities[0])) {
						// Populate country cities if it has no states (array is sequencial)
						$this->dropdown_cities = $countryCities;
					} elseif ($current_sc && $countryCities[$current_sc]) {
						// Populate selected state cities
						$this->dropdown_cities = $countryCities[$current_sc];
					}
				}
			}

		if (is_array($this->dropdown_cities)) {
			$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="city_select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' placeholder="' . esc_attr($args['placeholder']) . '">
				<option value="">Seleccioná una opción&hellip;</option>';

				foreach ( $this->dropdown_cities as $city_name ) {
					$field .= '<option value="' . esc_attr( $city_name ) . '" '.selected( $value, $city_name, false ) . '>' . $city_name .'</option>';
				}

				$field .= '</select>';

			} else {

				$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
			}

			// field description and close wrapper
			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			return $field;
		}

		public function load_scripts() {
			if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {

			$city_select_path = $this->get_plugin_url() . 'assets/js/city-select.js';
			wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), self::VERSION, true );

			wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
				'cities' => $this->get_cities(),
				'i18n_select_city_text' => 'Seleccioná una opción&hellip;'
			) );
			}
		}

		public function get_plugin_path() {

			if ( $this->plugin_path ) {
				return $this->plugin_path;
			}

			return $this->plugin_path = plugin_dir_path( __FILE__ );
		}

		public function get_plugin_url() {

			if ( $this->plugin_url ) {
				return $this->plugin_url;
			}

			return $this->plugin_url = plugin_dir_url( __FILE__ );
		}

		public function compatibility() {
			if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, false);
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
			}
		}

		/**
		 * Add submenu under WooCommerce for City Shipping configuration.
		 */
		public function add_admin_menu() {
			// Only users who can manage WooCommerce settings
			$capability = apply_filters( 'wc_city_select_shipping_capability', 'manage_woocommerce' );

			add_submenu_page(
				'woocommerce',
				'Envíos por Ciudad',
				'Envíos por Ciudad',
				$capability,
				'wc-city-select-shipping',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Enqueue admin scripts for the shipping by city page.
		 */
		public function enqueue_admin_scripts( $hook ) {
			if ( 'woocommerce_page_wc-city-select-shipping' !== $hook ) {
				return;
			}

			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'wc-city-select-admin', $this->get_plugin_url() . 'assets/css/admin-city-shipping.css', array(), self::VERSION );
			wp_enqueue_script( 'wc-city-select-admin', $this->get_plugin_url() . 'assets/js/admin-city-shipping.js', array( 'jquery' ), self::VERSION, true );

			// Pass cities data and shipping methods with zone info to JS
			$cities_data = $this->get_cities();
			$methods_with_zones = $this->get_shipping_methods_with_zone_data();
			
			// Get editing rule data if applicable
			$editing_rule_data = null;
			if ( isset( $_GET['edit'] ) ) {
				$editing_rule_id = intval( $_GET['edit'] );
				$rules = $this->get_shipping_rules();
				if ( isset( $rules[ $editing_rule_id ] ) ) {
					$editing_rule_data = $rules[ $editing_rule_id ];
				}
			}
			
			wp_localize_script( 'wc-city-select-admin', 'wcCitySelectAdmin', array(
				'cities' => $cities_data,
				'states' => WC()->countries->get_states(),
				'methodsWithZones' => $methods_with_zones,
				'editingRule' => $editing_rule_data,
			) );
		}

		/**
		 * Render settings page.
		 */
		public function settings_page() {
			if ( ! current_user_can( apply_filters( 'wc_city_select_shipping_capability', 'manage_woocommerce' ) ) ) {
				return;
			}

			// Handle form submission
			if ( isset( $_POST['wc_city_select_save_rules'] ) && check_admin_referer( 'wc_city_select_shipping_rules' ) ) {
				$this->save_shipping_rules();
				echo '<div class="notice notice-success is-dismissible"><p>Reglas guardadas correctamente.</p></div>';
			}

			// Handle delete action
			if ( isset( $_GET['action'] ) && 'delete' === sanitize_text_field( $_GET['action'] ) && isset( $_GET['rule_id'] ) && check_admin_referer( 'delete_rule_' . $_GET['rule_id'] ) ) {
				$this->delete_rule( intval( $_GET['rule_id'] ) );
				echo '<div class="notice notice-success is-dismissible"><p>Regla eliminada correctamente.</p></div>';
			}

			$rules = $this->get_shipping_rules();
			$countries = WC()->countries->get_allowed_countries();
			$shipping_methods = $this->get_all_shipping_methods();
			
			// Check if we're editing a rule
			$editing_rule_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : -1;
			$editing_rule = ( $editing_rule_id >= 0 && isset( $rules[ $editing_rule_id ] ) ) ? $rules[ $editing_rule_id ] : null;

			?>
			<div class="wrap wc-city-select-shipping-wrap">
				<h1>Métodos de envío por Ciudad</h1>
				<p>Configurá qué métodos de envío están disponibles para cada ciudad.</p>

				<!-- Existing Rules -->
				<?php if ( ! empty( $rules ) ) : ?>
					<h2>Reglas configuradas</h2>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>País</th>
								<th>Departamento</th>
								<th>Ciudad</th>
								<th>Métodos permitidos</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rules as $index => $rule ) : 
								// Get cities for display (support both old and new format)
								$rule_cities = array();
								if ( isset( $rule['cities'] ) && is_array( $rule['cities'] ) ) {
									$rule_cities = $rule['cities'];
								} elseif ( isset( $rule['city'] ) && ! empty( $rule['city'] ) ) {
									$rule_cities = array( $rule['city'] );
								}
								$cities_display = ! empty( $rule_cities ) ? implode( ', ', $rule_cities ) : '—';
								?>
								<tr>
									<td><?php echo esc_html( isset( $countries[ $rule['country'] ] ) ? $countries[ $rule['country'] ] : $rule['country'] ); ?></td>
									<td><?php echo esc_html( $rule['state'] ?: '—' ); ?></td>
									<td>
										<?php 
										if ( count( $rule_cities ) > 3 ) {
											echo esc_html( implode( ', ', array_slice( $rule_cities, 0, 3 ) ) . '... (' . count( $rule_cities ) . ' ciudades)' );
										} else {
											echo esc_html( $cities_display );
										}
										?>
									</td>
									<td>
										<?php
										if ( ! empty( $rule['allowed_methods'] ) ) {
											$method_names = array();
											foreach ( $rule['allowed_methods'] as $method_key ) {
												$method_names[] = $this->get_shipping_method_label( $method_key, $shipping_methods );
											}
											if ( count( $method_names ) > 2 ) {
												echo esc_html( implode( ', ', array_slice( $method_names, 0, 2 ) ) . '... (' . count( $method_names ) . ' métodos)' );
											} else {
												echo esc_html( implode( ', ', $method_names ) );
											}
										} else {
											echo '—';
										}
										?>
									</td>
									<td>
										<a href="<?php echo esc_url( add_query_arg( 'edit', $index ) ); ?>" class="button button-small">
											Editar
										</a>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'rule_id' => $index ) ), 'delete_rule_' . $index ) ); ?>" class="button button-small" onclick="return confirm('¿Estás seguro de eliminar esta regla?');">
											Eliminar
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<br>
				<?php endif; ?>

				<!-- Add New Rule Form -->
				<h2><?php echo $editing_rule ? 'Editar regla' : 'Agregar nueva regla'; ?></h2>
				<?php if ( $editing_rule ) : ?>
					<p><a href="<?php echo esc_url( remove_query_arg( 'edit' ) ); ?>" class="button">&larr; Cancelar edición</a></p>
				<?php endif; ?>
				<form method="post" action="" id="wc-city-select-form">
					<?php wp_nonce_field( 'wc_city_select_shipping_rules' ); ?>
					<input type="hidden" name="wc_city_select_save_rules" value="1">
					<input type="hidden" name="rule_id" value="<?php echo esc_attr( $editing_rule_id ); ?>">

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="rule_country">País <span class="required">*</span></label>
							</th>
							<td>
								<select name="rule_country" id="rule_country" class="regular-text" required>
									<option value="">Seleccioná un país</option>
									<?php foreach ( $countries as $code => $name ) : ?>
										<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $editing_rule ? $editing_rule['country'] : '', $code ); ?>><?php echo esc_html( $name ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="rule_state">Departamento</label>
							</th>
							<td>
								<select name="rule_state" id="rule_state" class="regular-text">
									<option value="">Seleccioná primero un país</option>
								</select>
								<p class="description">Opcional. Dejá vacío si el país no tiene departamentos.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Ciudades <span class="required">*</span></label>
							</th>
							<td>
								<div id="cities-list-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #fff;">
									<p class="no-cities-message">Seleccioná primero país/departamento para ver las ciudades disponibles.</p>
								</div>
								<p class="description">
									Seleccioná una o más ciudades para esta regla. Todas las ciudades seleccionadas compartirán los mismos métodos de envío.
									<br>
									<a href="#" id="select-all-cities" style="display:none;">Seleccionar todas</a> | 
									<a href="#" id="deselect-all-cities" style="display:none;">Deseleccionar todas</a>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label>Métodos de envío permitidos <span class="required">*</span></label>
							</th>
							<td>
								<?php if ( ! empty( $shipping_methods ) ) : ?>
									<fieldset id="shipping-methods-list">
										<?php foreach ( $shipping_methods as $method_key => $method_label ) : ?>
											<label style="display: block; margin-bottom: 5px;" class="shipping-method-option" data-method-key="<?php echo esc_attr( $method_key ); ?>">
												<input type="checkbox" name="rule_methods[]" value="<?php echo esc_attr( $method_key ); ?>">
												<?php echo esc_html( $method_label ); ?>
											</label>
										<?php endforeach; ?>
									</fieldset>
									<p class="description">Seleccioná los métodos de envío que estarán disponibles para esta ciudad. Los métodos se filtran según el país/departamento seleccionado.</p>
								<?php else : ?>
									<p>No hay métodos de envío configurados. Por favor, configurá zonas de envío primero.</p>
								<?php endif; ?>
							</td>
						</tr>
					</table>

					<?php submit_button( $editing_rule ? 'Guardar cambios' : 'Agregar regla' ); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Get all configured shipping methods from all zones.
		 *
		 * @return array
		 */
		private function get_all_shipping_methods() {
			$methods = array();

			// Get all shipping zones
			$zones = WC_Shipping_Zones::get_zones();

			foreach ( $zones as $zone ) {
				$zone_obj = new WC_Shipping_Zone( $zone['id'] );
				$shipping_methods = $zone_obj->get_shipping_methods( true ); // true = enabled only

				foreach ( $shipping_methods as $instance_id => $method ) {
					$method_id = $method->id;
					$method_title = $method->get_title();
					$zone_name = $zone['zone_name'];

					// Store as "method_id:instance_id" => "Zone Name - Method Title"
					$key = $method_id . ':' . $instance_id;
					$methods[ $key ] = $zone_name . ' - ' . $method_title;
				}
			}

			// Also get methods from "Rest of the World" zone (zone 0)
			$zone_0 = new WC_Shipping_Zone( 0 );
			$shipping_methods_0 = $zone_0->get_shipping_methods( true );

			foreach ( $shipping_methods_0 as $instance_id => $method ) {
				$method_id = $method->id;
				$method_title = $method->get_title();

				$key = $method_id . ':' . $instance_id;
				$methods[ $key ] = 'Resto del mundo - ' . $method_title;
			}

			return $methods;
		}

		/**
		 * Get shipping methods with their zone location data for filtering.
		 *
		 * @return array
		 */
		private function get_shipping_methods_with_zone_data() {
			$methods_data = array();

			// Get all shipping zones
			$zones = WC_Shipping_Zones::get_zones();

			foreach ( $zones as $zone ) {
				$zone_obj = new WC_Shipping_Zone( $zone['id'] );
				$zone_locations = $zone_obj->get_zone_locations();
				$shipping_methods = $zone_obj->get_shipping_methods( true );

				// Extract countries and states from zone locations
				$zone_countries = array();
				$zone_states = array();

				foreach ( $zone_locations as $location ) {
					if ( 'country' === $location->type ) {
						$zone_countries[] = $location->code;
					} elseif ( 'state' === $location->type ) {
						// State codes are in format "COUNTRY:STATE"
						$parts = explode( ':', $location->code );
						if ( count( $parts ) === 2 ) {
							$zone_countries[] = $parts[0];
							$zone_states[] = $location->code;
						}
					}
				}

				$zone_countries = array_unique( $zone_countries );

				foreach ( $shipping_methods as $instance_id => $method ) {
					$method_id = $method->id;
					$method_title = $method->get_title();
					$zone_name = $zone['zone_name'];

					$key = $method_id . ':' . $instance_id;
					
					$methods_data[ $key ] = array(
						'label' => $zone_name . ' - ' . $method_title,
						'countries' => $zone_countries,
						'states' => $zone_states,
					);
				}
			}

			// Also get methods from "Rest of the World" zone (zone 0)
			$zone_0 = new WC_Shipping_Zone( 0 );
			$shipping_methods_0 = $zone_0->get_shipping_methods( true );

			foreach ( $shipping_methods_0 as $instance_id => $method ) {
				$method_id = $method->id;
				$method_title = $method->get_title();

				$key = $method_id . ':' . $instance_id;
				
				$methods_data[ $key ] = array(
					'label' => 'Resto del mundo - ' . $method_title,
					'countries' => array(), // Empty means "rest of the world"
					'states' => array(),
				);
			}

			return $methods_data;
		}

		/**
		 * Get label for a shipping method key.
		 *
		 * @param string $method_key
		 * @param array $all_methods
		 * @return string
		 */
		private function get_shipping_method_label( $method_key, $all_methods ) {
			if ( isset( $all_methods[ $method_key ] ) ) {
				return $all_methods[ $method_key ];
			}

			return $method_key;
		}

		/**
		 * Get shipping rules from database.
		 *
		 * @return array
		 */
		private function get_shipping_rules() {
			$rules = get_option( $this->shipping_rules_option, array() );

			if ( ! is_array( $rules ) ) {
				return array();
			}

			return $rules;
		}

		/**
		 * Save shipping rules from form submission.
		 */
		private function save_shipping_rules() {
			$rules = $this->get_shipping_rules();

			$country = isset( $_POST['rule_country'] ) ? sanitize_text_field( $_POST['rule_country'] ) : '';
			$state = isset( $_POST['rule_state'] ) ? sanitize_text_field( $_POST['rule_state'] ) : '';
			$cities = isset( $_POST['rule_cities'] ) && is_array( $_POST['rule_cities'] ) ? array_map( 'sanitize_text_field', $_POST['rule_cities'] ) : array();
			$allowed_methods = isset( $_POST['rule_methods'] ) && is_array( $_POST['rule_methods'] ) ? array_map( 'sanitize_text_field', $_POST['rule_methods'] ) : array();
			$rule_id = isset( $_POST['rule_id'] ) ? intval( $_POST['rule_id'] ) : -1;

			// Validate required fields
			if ( empty( $country ) || empty( $cities ) || empty( $allowed_methods ) ) {
				return;
			}

			// Create one grouped rule for all selected cities
			$new_rule = array(
				'country'         => $country,
				'state'           => $state,
				'cities'          => $cities, // Array of cities
				'allowed_methods' => $allowed_methods,
			);

			if ( $rule_id >= 0 && isset( $rules[ $rule_id ] ) ) {
				// Edit existing rule
				$rules[ $rule_id ] = $new_rule;
			} else {
				// Add new rule
				$rules[] = $new_rule;
			}

			update_option( $this->shipping_rules_option, $rules );
		}

		/**
		 * Delete a rule by index.
		 *
		 * @param int $rule_id
		 */
		private function delete_rule( $rule_id ) {
			$rules = $this->get_shipping_rules();

			if ( isset( $rules[ $rule_id ] ) ) {
				unset( $rules[ $rule_id ] );
				$rules = array_values( $rules ); // Re-index
				update_option( $this->shipping_rules_option, $rules );
			}
		}

		/**
		 * Filter shipping rates based on configured rules and selected city.
		 *
		 * @param array $rates
		 * @param array $package
		 * @return array
		 */
		public function filter_shipping_rates_by_city( $rates, $package ) {
			$rules = $this->get_shipping_rules();

			if ( empty( $rules ) ) {
				return $rates;
			}

			$destination_country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';
			$destination_state   = isset( $package['destination']['state'] ) ? $package['destination']['state'] : '';
			$destination_city    = isset( $package['destination']['city'] ) ? $package['destination']['city'] : '';

			if ( '' === $destination_city ) {
				// Try to get from customer object as a fallback
				if ( WC()->customer ) {
					$destination_city = WC()->customer->get_shipping_city();
				}
			}

			if ( '' === $destination_city ) {
				return $rates;
			}

			$destination_city_lc = wc_strtolower( $destination_city );
			$allowed_methods     = array();

			foreach ( $rules as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}

				$rule_country = isset( $rule['country'] ) ? $rule['country'] : '';
				$rule_state   = isset( $rule['state'] ) ? $rule['state'] : '';
				
				// Support both old format (single city) and new format (multiple cities)
				$rule_cities = array();
				if ( isset( $rule['cities'] ) && is_array( $rule['cities'] ) ) {
					// New format: array of cities
					$rule_cities = $rule['cities'];
				} elseif ( isset( $rule['city'] ) && ! empty( $rule['city'] ) ) {
					// Old format: single city (for backward compatibility)
					$rule_cities = array( $rule['city'] );
				}

				// Country match (empty means "any")
				if ( $rule_country && strtoupper( $rule_country ) !== strtoupper( $destination_country ) ) {
					continue;
				}

				// State match (empty means "any")
				if ( $rule_state && strtoupper( $rule_state ) !== strtoupper( $destination_state ) ) {
					continue;
				}

				// City match (required, case-insensitive)
				if ( empty( $rule_cities ) ) {
					continue;
				}

				// Check if destination city is in the rule's cities array
				$city_matched = false;
				foreach ( $rule_cities as $rule_city ) {
					if ( wc_strtolower( $rule_city ) === $destination_city_lc ) {
						$city_matched = true;
						break;
					}
				}

				if ( ! $city_matched ) {
					continue;
				}

				if ( ! empty( $rule['allowed_methods'] ) && is_array( $rule['allowed_methods'] ) ) {
					foreach ( $rule['allowed_methods'] as $method_key ) {
						$method_key = trim( (string) $method_key );
						if ( '' !== $method_key ) {
							$allowed_methods[] = $method_key;
						}
					}
				}
			}

			if ( empty( $allowed_methods ) ) {
				// No matching rules for this city, leave rates unchanged
				return $rates;
			}

			$allowed_methods = array_unique( $allowed_methods );

			foreach ( $rates as $rate_id => $rate ) {
				if ( ! $rate instanceof WC_Shipping_Rate ) {
					continue;
				}

				// Full instance id, e.g. "flat_rate:1"
				$instance_key = $rate->get_id();
				// Method id only, e.g. "flat_rate"
				$method_id    = $rate->get_method_id();

				if ( ! in_array( $instance_key, $allowed_methods, true ) && ! in_array( $method_id, $allowed_methods, true ) ) {
					unset( $rates[ $rate_id ] );
				}
			}

			return $rates;
		}
	}

	$GLOBALS['wc_city_select'] = new WC_City_Select();
}
