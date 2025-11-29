# Envíos por ciudad de Uruguay para WooCommerce

Plugin de WordPress/WooCommerce que permite seleccionar ciudades uruguayas en el checkout y configurar métodos de envío específicos por ciudad.

## Descripción

Este plugin agrega un selector de ciudades para Uruguay en WooCommerce, reemplazando el campo de texto libre por un desplegable con todas las ciudades organizadas por departamento. Además, permite configurar reglas para mostrar solo ciertos métodos de envío según la ciudad seleccionada por el cliente.

### Características principales

- ✅ Selector de ciudades uruguayas organizado por departamentos
- ✅ Configuración de métodos de envío por ciudad
- ✅ Soporte para múltiples ciudades en una misma regla
- ✅ Filtrado automático de métodos según zonas de envío de WooCommerce
- ✅ Compatible con WooCommerce HPOS (High-Performance Order Storage)
- ✅ Integración con Select2 para búsqueda rápida

## Requisitos

- WordPress 5.0 o superior
- WooCommerce 2.2 o superior (probado hasta 9.7)
- PHP 7.0 o superior

## Instalación

Tenés dos formas de instalar el plugin:

### Método 1: Instalación desde el panel de WordPress (recomendado)

1. Descargá el plugin desde GitHub o cloná el repositorio:
   ```bash
   git clone https://github.com/carpicoder/woo-city-shipping-uy.git
   ```

2. Comprimí la carpeta `woo-city-shipping-uy` en un archivo ZIP.

3. En tu panel de WordPress, andá a **Plugins → Añadir nuevo → Subir plugin**.

4. Seleccioná el archivo ZIP y hacé clic en **Instalar ahora**.

5. Una vez instalado, hacé clic en **Activar plugin**.

### Método 2: Instalación manual por FTP

1. Descargá el plugin desde GitHub o cloná el repositorio:
   ```bash
   git clone https://github.com/carpicoder/woo-city-shipping-uy.git
   ```

2. Comprimí la carpeta en un archivo ZIP y luego descomprimilo.

3. Subí la carpeta `woo-city-shipping-uy` al directorio `/wp-content/plugins/` de tu sitio WordPress usando FTP o el administrador de archivos de tu hosting.

4. Andá a **Plugins** en tu panel de WordPress y activá el plugin.

## Cómo usarlo

### Paso 1: Configurar las zonas de envío en WooCommerce

Antes de usar el plugin, necesitás configurar tus zonas y métodos de envío en WooCommerce.

1. Andá a **WooCommerce → Ajustes → Envíos**.

2. Creá las zonas de envío que necesites para cada departamento de Uruguay.

3. Dentro de cada zona, agregá los métodos de envío que querés ofrecer (por ejemplo: envío gratis, precio fijo, retiro en local, etc.).

![Configuración de zonas de envío](assets/screenshots/001-zonas-envio.png)

### Paso 2: Acceder a la configuración del plugin

Una vez que tengas tus zonas de envío configuradas:

1. Andá a **WooCommerce → Envíos por Ciudad** en el menú lateral.

### Paso 3: Crear reglas de envío por ciudad

Ahora vas a configurar qué métodos de envío están disponibles para cada ciudad:

1. Seleccioná el **País** (Uruguay).

2. Elegí el **Departamento** donde querés aplicar la regla.

3. Seleccioná una o más **Ciudades** (podés usar "Seleccionar todas" si querés aplicar la misma regla a todas las ciudades del departamento).

4. Marcá los **Métodos de envío permitidos** que querés que estén disponibles para esas ciudades específicas.

5. Hacé clic en **Agregar regla**.

![Configuración de regla por ciudad](assets/screenshots/002-crear-regla.png)

### Paso 4: Verificar las reglas configuradas

Una vez guardada, tu regla aparecerá en la tabla de reglas configuradas, donde podrás editarla o eliminarla cuando quieras.

![Reglas configuradas](assets/screenshots/003-regla-guardada.png)

### Paso 5: Comportamiento en el checkout

**Para ciudades SIN reglas configuradas:**

Si un cliente selecciona una ciudad que no tiene reglas específicas, verá todos los métodos de envío disponibles para su departamento según las zonas de WooCommerce.

Por ejemplo, si selecciona Montevideo y una ciudad sin reglas:

![Checkout sin reglas](assets/screenshots/004-sin-reglas.png)

**Para ciudades CON reglas configuradas:**

Si un cliente selecciona una ciudad con reglas específicas, solo verá los métodos de envío que configuraste para esa ciudad.

![Checkout con reglas](assets/screenshots/005-con-reglas.png)

## Gestión de reglas

### Editar una regla existente

1. En la tabla de reglas configuradas, hacé clic en el botón **Editar** de la regla que querés modificar.

2. Realizá los cambios necesarios en el formulario.

3. Hacé clic en **Guardar cambios**.

### Eliminar una regla

1. En la tabla de reglas configuradas, hacé clic en el botón **Eliminar**.

2. Confirmá la acción en el mensaje que aparece.

La regla será eliminada y las ciudades afectadas volverán a mostrar todos los métodos de envío disponibles según las zonas de WooCommerce.

## Estructura del plugin

```
woo-city-shipping-uy/
├── assets/
│   ├── css/
│   │   └── admin-city-shipping.css
│   └── js/
│       ├── admin-city-shipping.js
│       └── city-select.js
├── cities/
│   └── UY.php
├── envio-ciudad-uruguay-woocommerce.php
├── uninstall.php
├── README.md
├── LICENSE
└── .gitignore
```

## Preguntas frecuentes

### ¿Puedo agregar más ciudades?

Sí, podés editar el archivo `cities/UY.php` y agregar las ciudades que necesites en el array correspondiente a cada departamento.

### ¿Funciona con otros países?

El plugin está preparado para soportar múltiples países. Podés crear archivos similares a `UY.php` en la carpeta `cities/` usando el código del país (por ejemplo, `AR.php` para Argentina).

### ¿Qué pasa si una ciudad no tiene reglas configuradas?

Si no hay reglas específicas para una ciudad, se mostrarán todos los métodos de envío disponibles según las zonas de WooCommerce.

### ¿Es compatible con otros plugins de envío?

Sí, el plugin funciona con cualquier método de envío de WooCommerce, incluyendo plugins de terceros que agreguen métodos personalizados.

## Desarrollo

### Agregar nuevas ciudades

Editá el archivo `cities/UY.php`:

```php
$cities['UY'] = array(
    'UY-XX' => array(  // Código del departamento
        'Ciudad 1',
        'Ciudad 2',
        // ...
    ),
);
```

## Changelog

### 1.0.0 - 2025-11-29
- Versión inicial
- Selector de ciudades para Uruguay
- Configuración de métodos de envío por ciudad
- Soporte para múltiples ciudades por regla
- Compatibilidad con HPOS

## Créditos

Desarrollado por [Carpicoder](https://carpicoder.com)

Basado en el plugin [WC City Select](https://github.com/8manos/wc-city-select) de 8manos.

## Donaciones

Si este plugin te resulta útil y querés apoyar su desarrollo, podés hacer una donación en [carpicoder.com/donaciones](https://carpicoder.com/donaciones) ☕

## Licencia

Este plugin está licenciado bajo GPLv2 o posterior. Consultá el archivo [LICENSE](LICENSE) para más detalles.

## Soporte

Si encontrás algún problema, alguna ciudad que esté mal, que falte o que sobre, o tenés sugerencias, por favor abrí un [issue en GitHub](https://github.com/carpicoder/woo-city-shipping-uy/issues) o contactame a [hola@carpicoder.com](mailto:hola@carpicoder.com).