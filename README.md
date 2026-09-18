# Sistema de Control de Stock - Perfumería Tiana

Aplicación académica para administrar productos, entradas, ventas, pérdidas, existencias e indicadores. Utiliza HTML5, Bootstrap 5 por CDN, JavaScript nativo, PHP con PDO y MySQL/MariaDB. No utiliza Node.js ni CSS propio.

## Estructura

- `index.html`: dashboard con indicadores y resumen diario.
- `productos.html`: alta de productos, entradas, ventas, pérdidas y detalle por producto.
- `movimientos.html`: historial con filtros.
- `api/`: endpoints JSON publicados por PHP y conexión PDO.
- `database/perfumeria_tiana.sql`: base de datos, tablas y carga inicial.
- `js/`: consumo de API con Fetch.

## Diseño mobile-first

Las vistas empiezan con layout de una columna y navegación superior en pantallas pequeñas. Las clases `col-md-*`, `col-lg-*`, `flex-md-column`, `table-responsive` y demás utilidades oficiales de Bootstrap adaptan la interfaz en pantallas mayores. No se incluye hoja de estilos personalizada.

## Instalación en XAMPP

1. Copiar la carpeta `Nueva carpeta` dentro de `C:\xampp\htdocs\perfumeria-tiana`.
2. Abrir XAMPP y encender Apache y MySQL.
3. Importar `database/perfumeria_tiana.sql` desde phpMyAdmin o ejecutarlo con MariaDB.
4. El script crea la base, las tablas, 100 productos y 5.600 movimientos entre el 18/09/2025 y el 18/09/2026.
5. Revisar `api/conexion.php`. La configuración inicial usa servidor `localhost`, base `perfumeria_tiana`, usuario `root` y contraseña vacía, que es la configuración común de XAMPP.
6. Abrir `http://localhost/perfumeria-tiana/`.

La base ya fue instalada y probada en el XAMPP local del equipo. Se verificó que existan 100 productos, que el rango de movimientos sea exacto y que no haya existencias negativas.

## Datos iniciales

El SQL registra una compra inicial para cada producto, reposiciones cada 15 días, ventas, pérdidas periódicas y una venta final el 18/09/2026. El stock queda calculado a partir de esos movimientos.

## Cálculo de indicadores

- Ingresos: cantidad vendida por precio de venta.
- Costo de ventas: cantidad vendida por el precio de compra de la última entrada conocida del producto.
- Ganancia estimada: ingresos menos costo de ventas.
- Pérdidas: cantidad perdida por el precio de compra registrado para esa salida.

Es una estimación simple y explicable; no implementa FIFO, LIFO ni contabilidad por lotes.

## API

- `GET/POST api/productos.json`
- `GET/POST api/movimientos.json`
- `GET api/estadisticas.json?periodo=dia|semana|mes`
- Detalle: `GET api/estadisticas.json?periodo=mes&producto_id=1`

Las rutas `.json` son reescritas por Apache hacia los controladores PHP. La interfaz nunca accede directamente a MySQL: usa Fetch API para enviar y recibir JSON, y PHP usa PDO con consultas preparadas.

## Prueba rápida

1. Abrir Productos.
2. Registrar un producto con código único.
3. Registrar una entrada, una venta y una pérdida.
4. Revisar Dashboard e Historial.
5. Probar los filtros por producto, tipo y fechas.
