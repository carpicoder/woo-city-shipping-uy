=== Métodos de envío por ciudad en Uruguay para WooCommerce ===
Contributors: urutienda
Donate link: https://urutienda.com
Tags: woocommerce, city select, cities select, city dropdown, cities dropdown, uruguay, shipping methods
Requires at least: 4.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Selector de ciudades para WooCommerce con configuración de métodos de envío específicos por ciudad.

== Description ==

WooCommerce utiliza un campo de texto para que los clientes ingresen la ciudad. Con este plugin puedes proporcionar una lista de ciudades que se mostrará como un selector desplegable.

Además, este plugin te permite configurar qué métodos de envío están disponibles para cada ciudad o grupo de ciudades, brindándote control total sobre las opciones de envío según la ubicación del cliente.

El selector de ciudades se mostrará en las páginas de checkout, edición de direcciones y calculadora de envío si está configurada.

### Características principales

* **Selector de ciudades**: Muestra un desplegable con las ciudades disponibles en lugar de un campo de texto libre.
* **Métodos de envío por ciudad**: Configura qué métodos de envío están disponibles para cada ciudad o grupo de ciudades.
* **Gestión visual**: Interfaz amigable con selectores y checkboxes, sin necesidad de editar código.
* **Edición de reglas**: Modifica reglas existentes, agrega o quita ciudades y métodos de envío fácilmente.
* **Reglas agrupadas**: Crea una regla para múltiples ciudades a la vez.

### WooCommerce Cart and Checkout Blocks

Este plugin aún no es compatible con Blocks.
Funciona usando los shortcodes legacy: `[woocommerce_cart]` y `[woocommerce_checkout]`.

Para que este plugin funcione, puedes usar estos shortcodes en lugar de los bloques para tus páginas de Carrito y Checkout.

### Cómo agregar ciudades

El plugin ya incluye las ciudades de Uruguay. Si necesitas agregar ciudades de otros países, puedes hacerlo en el archivo functions.php de tu tema.

Usa el filtro `wc_city_select_cities` para cargar tus ciudades. Esto se hace de manera similar a [agregar estados/provincias](https://docs.woothemes.com/document/addmodify-states/).
Debe agregarse en tu functions.php o en un plugin personalizado.

`
add_filter( 'wc_city_select_cities', 'my_cities' );
/**
 * Replace XX with the country code. Instead of YYY, ZZZ use actual  state codes.
 */
function my_cities( $cities ) {
	$cities['XX'] = array(
		'YYY' => array(
			'City ',
			'Another City'
		),
		'ZZZ' => array(
			'City 3',
			'City 4'
		)
	);
	return $cities;
}
`

También es posible usar una lista de ciudades sin agruparlas por departamento/estado:

`
add_filter( 'wc_city_select_cities', 'my_cities' );
function my_cities( $cities ) {
	$cities['XX'] = array(
		'Ciudad 1',
		'Ciudad 2'
	);
	return $cities;
}
`

### Configurar métodos de envío por ciudad

1. Ve a **WooCommerce → Shipping by City** en el panel de administración.
2. Selecciona el país y departamento/estado.
3. Marca las ciudades para las que quieres configurar métodos de envío.
4. Selecciona los métodos de envío permitidos para esas ciudades.
5. Haz clic en "Agregar regla".

Para editar una regla existente, simplemente haz clic en el botón "Editar" junto a la regla que deseas modificar.

== Changelog ==

= 1.0.0 =
* Versión inicial adaptada para Uruguay
* Selector de ciudades con desplegable
* Configuración de métodos de envío por ciudad
* Interfaz visual con selectores y checkboxes
* Funcionalidad de edición de reglas
* Soporte para reglas agrupadas (múltiples ciudades)
* Filtrado automático de métodos según país/departamento
* Incluye todas las ciudades de Uruguay organizadas por departamento
* Compatible con WooCommerce HPOS
* Basado en WC City Select 1.0.10
