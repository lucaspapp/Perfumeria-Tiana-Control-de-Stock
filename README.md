# Sistema de Control de Stock - Perfumería Tiana

Aplicación académica para administrar productos, entradas, ventas, pérdidas, existencias e indicadores. Utiliza HTML5, Bootstrap 5 por CDN, JavaScript nativo, PHP con PDO y MySQL/MariaDB. No utiliza Node.js ni CSS propio.

## Estructura

- `index.html`: dashboard con indicadores y resumen diario.
- `productos.html`: alta de productos, entradas, ventas, pérdidas y detalle por producto.
- `movimientos.html`: historial con filtros.
- `administracion.html`: costos operativos, rentabilidad y valuación estimada del stock.
- `gestion.html`: precios propios, referencias de competidores y ventajas comerciales.
- `personal.html`: legajo básico, licencias y liquidaciones parametrizables.
- `api/`: endpoints JSON publicados por PHP y conexión PDO.
- `database/perfumeria_tiana.sql`: base de datos, tablas y carga inicial.
- `database/modulos_administracion_gestion_personal.sql`: migración no destructiva para bases ya instaladas.
- `database/datos_demo_infinityfree.sql`: carga idempotente de los registros ficticios de demostración, sin crear tablas temporales.
- `database/migracion_fecha_pago_liquidaciones.sql`: agrega la fecha efectiva de pago en bases que ya tienen Personal.
- `js/`: consumo de API con Fetch.

El historial muestra 50 movimientos por página y permite filtros por producto, tipo y fecha; la API acepta `pagina` y `por_pagina` (máximo 100). Los filtros se aplican en el servidor y las consultas preparadas devuelven solo esa página, ordenada por fecha e id. Usa el índice de fecha para el historial general y el índice producto/fecha al filtrar un producto. El dashboard conserva Hoy, Esta semana y Este mes, y agrega Rango manual con fechas Desde/Hasta.

## Diseño mobile-first

Las vistas empiezan con layout de una columna y navegación superior en pantallas pequeñas. Las clases `col-md-*`, `col-lg-*`, `flex-md-column`, `table-responsive` y demás utilidades oficiales de Bootstrap adaptan la interfaz en pantallas mayores. No se incluye hoja de estilos personalizada.

## Instalación en XAMPP

1. Copiar la carpeta `Nueva carpeta` dentro de `C:\xampp\htdocs\perfumeria-tiana`.
2. Abrir XAMPP y encender Apache y MySQL.
3. Importar `database/perfumeria_tiana.sql` desde phpMyAdmin o ejecutarlo con MariaDB.
4. Para una instalación nueva, el script crea la base, las tablas, 100 productos y los movimientos de ejemplo. Para una base ya instalada, importar una sola vez `database/modulos_administracion_gestion_personal.sql` y luego `database/migracion_fecha_pago_liquidaciones.sql`. No volver a importar el script principal, porque reinicia los datos de ejemplo.
5. Revisar `api/conexion.php`. La configuración inicial usa servidor `localhost`, base `perfumeria_tiana`, usuario `root` y contraseña vacía, que es la configuración común de XAMPP.
6. Abrir `http://localhost/perfumeria-tiana/`.

La base ya fue instalada y probada en el XAMPP local del equipo. Se verificó que existan 100 productos, que el rango de movimientos sea exacto y que no haya existencias negativas.

## Importación en InfinityFree

Para actualizar una instalación existente y conservar sus productos y movimientos:

1. Desde el phpMyAdmin de InfinityFree, exportar primero la base actual como respaldo. Seleccionar la base correcta en el menú izquierdo.
2. Por FTP/File Manager, subir y reemplazar en la carpeta pública del sitio las páginas `index.html`, `productos.html`, `movimientos.html`, `acerca.html`, `administracion.html`, `gestion.html` y `personal.html`.
3. Subir el contenido de `js/` y los controladores PHP de `api/`. También subir `api/.htaccess`; algunos clientes FTP ocultan archivos que empiezan con punto.
4. Configurar `api/conexion.php` con los valores exactos asignados por InfinityFree. No dejar las credenciales locales de XAMPP (`root` y contraseña vacía):

```php
$host = 'sqlXXX.infinityfree.com';
$baseDatos = 'epiz_12345678_perfumeria';
$usuario = 'epiz_12345678';
$contrasena = 'TU_CONTRASEÑA';
```

5. Importar una sola vez `database/migracion_fecha_pago_liquidaciones.sql` para agregar la fecha efectiva de pago de sueldos. Las liquidaciones reales existentes quedan sin fecha hasta que confirmes cuándo se pagaron; las liquidaciones DEMO reciben fecha 15/10/2026. En una base nueva no hace falta, porque la columna ya está en el esquema.
6. Importar la versión más reciente de `database/datos_demo_infinityfree.sql` para cargar precios, referencias, costos, movimientos, empleados, licencias y liquidaciones ficticios. No crea tablas temporales, porque InfinityFree puede no conceder ese permiso. El archivo se puede importar otra vez sin duplicar datos ni cambiar el stock.
7. Abrir el dominio y comprobar Dashboard, Administración, Gestión, Personal e Historial.

No importes `database/perfumeria_tiana.sql` ni el `perfumeria_tiana.sql` de la raíz durante una actualización: son volcados de instalación/prueba que reinician productos y movimientos. Los archivos SQL se importan desde phpMyAdmin; no hace falta subir la carpeta `database/` al directorio público del sitio.

Para una instalación completamente nueva, crear la base en **MySQL Databases**, importarla desde phpMyAdmin con el volcado correspondiente y luego aplicar la migración de módulos. InfinityFree asigna nombres parecidos a `epiz_12345678_perfumeria` y un host MySQL similar a `sqlXXX.infinityfree.com`.

## Datos iniciales

El SQL registra una compra inicial para cada producto, reposiciones cada 15 días, ventas, pérdidas periódicas y una venta final el 18/09/2026. El stock queda calculado a partir de esos movimientos.

## Cálculo de indicadores

- Ingresos: cantidad vendida por precio de venta.
- Costo de ventas: cantidad vendida por el precio de compra de la última entrada conocida del producto.
- Ganancia estimada: ingresos menos costo de ventas.
- Pérdidas: cantidad perdida por el precio de compra registrado para esa salida.
- Administración y Dashboard: la Ganancia Total suma ventas y resta compras, costos operativos y sueldos brutos según fecha efectiva de pago. No resta otra vez el costo vendido ni pérdidas de stock, porque el egreso de dinero se cuenta en la compra. Supone que los movimientos fueron cobrados/pagados en la fecha registrada. No incluye saldo inicial ni operaciones pendientes. Las liquidaciones antiguas sin fecha de pago no se descuentan hasta que se confirme esa fecha. Los sueldos se toman de Personal; los costos de categoría `Personal` no se vuelven a sumar.
- Gestión: el precio sugerido es costo unitario más el margen objetivo indicado. Las referencias y ventajas se registran manualmente; no existe consulta automática a precios externos.
- Personal: el sueldo proporcional se calcula sobre días trabajados/días del período; presentismo, productividad y retenciones se ingresan como parámetros. Presentismo suma su porcentaje al sueldo proporcional solo si se marca manualmente como correspondiente; el sistema no comprueba asistencia ni licencias. Productividad/bonus se ingresa como porcentaje del sueldo proporcional y se guarda como un importe calculado.

Es una estimación simple y explicable; no implementa FIFO, LIFO ni contabilidad por lotes.
Las liquidaciones son una ayuda de gestión, no un cálculo legal; deben verificarse con la normativa laboral, el convenio aplicable y un profesional.

## API

- `GET/POST api/productos.json`
- `GET/POST api/movimientos.json`
- `GET api/movimientos.json?pagina=1&por_pagina=50`
- `GET api/estadisticas.json?periodo=dia|semana|mes|personalizado`
- Rango manual: `GET api/estadisticas.json?periodo=personalizado&fecha_desde=AAAA-MM-DD&fecha_hasta=AAAA-MM-DD`
- Detalle: `GET api/estadisticas.json?periodo=mes&producto_id=1`
- Administración: `GET/POST api/administracion.json?periodo=AAAA-MM`
- Gestión: `GET/POST api/gestion.json`
- Personal: `GET/POST api/personal.json?periodo=AAAA-MM`

Las rutas `.json` son reescritas por Apache hacia los controladores PHP. La interfaz nunca accede directamente a MySQL: usa Fetch API para enviar y recibir JSON, y PHP usa PDO con consultas preparadas.

## Prueba rápida

1. Abrir Productos.
2. Registrar un producto con código único.
3. Registrar una entrada, una venta y una pérdida.
4. Revisar Dashboard e Historial.
5. Probar los filtros por producto, tipo y fechas.
