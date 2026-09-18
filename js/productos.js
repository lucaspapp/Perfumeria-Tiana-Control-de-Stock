let productos = [];

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('formProducto').addEventListener('submit', guardarProducto);
    document.getElementById('formMovimiento').addEventListener('submit', guardarMovimiento);
    document.getElementById('tipoMovimiento').addEventListener('change', actualizarCamposPrecio);
    document.getElementById('periodoProducto').addEventListener('change', cargarDetalle);
    document.getElementById('productoEstadistica').addEventListener('change', cargarDetalle);
    cargarProductos();
});

async function cargarProductos() {
    try {
        const datos = await pedirJson('productos.json');
        productos = datos.productos;
        const opciones = productos.map(p => `<option value="${p.id}">${escapar(p.codigo)} - ${escapar(p.nombre)} (stock: ${p.existencias})</option>`).join('');
        document.getElementById('movProducto').innerHTML = '<option value="">Seleccionar producto...</option>' + opciones;
        document.getElementById('productoEstadistica').innerHTML = '<option value="">Seleccionar producto...</option>' + opciones;
        document.getElementById('tablaProductos').innerHTML = productos.map(p => `<tr><td><strong>${escapar(p.codigo)}</strong> - ${escapar(p.nombre)}</td><td><span class="badge text-bg-light">${p.existencias} unidades</span></td><td>${fechaBonita(p.fecha_registro)}</td><td><span class="badge badge-entrada">Activo</span></td></tr>`).join('') || '<tr><td colspan="4" class="text-center text-muted py-4">Todavía no hay productos registrados.</td></tr>';
        document.getElementById('listaProductosMovil').innerHTML = productos.map(p => `<div class="list-group-item"><div class="d-flex justify-content-between gap-2"><strong>${escapar(p.codigo)} - ${escapar(p.nombre)}</strong><span class="badge text-bg-light">${p.existencias} unidades</span></div><div class="d-flex justify-content-between mt-2 small text-secondary"><span>Registrado: ${fechaBonita(p.fecha_registro)}</span><span class="text-success">Activo</span></div></div>`).join('') || '<div class="list-group-item text-center text-secondary py-4">Todavía no hay productos registrados.</div>';
    } catch (error) { mostrarMensaje(error.message, 'danger'); }
}

async function guardarProducto(evento) {
    evento.preventDefault();
    try {
        const datos = await pedirJson('productos.json', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ codigo: document.getElementById('codigo').value.trim(), nombre: document.getElementById('nombre').value.trim() }) });
        mostrarMensaje(datos.message);
        evento.target.reset();
        cargarProductos();
    } catch (error) { mostrarMensaje(error.message, 'danger'); }
}

async function guardarMovimiento(evento) {
    evento.preventDefault();
    const tipo = document.getElementById('tipoMovimiento').value;
    const cantidad = Number(document.getElementById('cantidad').value);
    const precioCompra = Number(document.getElementById('precioCompra').value || 0);
    const precioVenta = Number(document.getElementById('precioVenta').value || 0);
    if (!document.getElementById('movProducto').value || cantidad <= 0 || (tipo === 'entrada' && precioCompra <= 0) || (tipo === 'venta' && precioVenta <= 0)) { mostrarMensaje('Completa los datos obligatorios con valores válidos.', 'danger'); return; }
    try {
        const datos = await pedirJson('movimientos.json', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ producto_id: Number(document.getElementById('movProducto').value), tipo_movimiento: tipo, cantidad, precio_compra: precioCompra, precio_venta: precioVenta, observacion: document.getElementById('observacion').value.trim() }) });
        mostrarMensaje(datos.message); evento.target.reset(); actualizarCamposPrecio(); cargarProductos();
    } catch (error) { mostrarMensaje(error.message, 'danger'); }
}

function actualizarCamposPrecio() { const venta = document.getElementById('tipoMovimiento').value === 'venta'; document.getElementById('grupoCompra').classList.toggle('d-none', !(!venta)); document.getElementById('grupoVenta').classList.toggle('d-none', !venta); }

async function cargarDetalle() {
    const id = document.getElementById('productoEstadistica').value;
    const detalle = document.getElementById('detalleProducto');
    if (!id) { detalle.classList.add('d-none'); return; }
    try { const datos = await pedirJson(`estadisticas.json?periodo=${document.getElementById('periodoProducto').value}&producto_id=${id}`); const producto = productos.find(p => Number(p.id) === Number(id)); const r = datos.resumen; detalle.classList.remove('d-none'); detalle.innerHTML = `<h3 class="section-title mb-3">${escapar(producto.codigo)} - ${escapar(producto.nombre)}</h3><div class="row g-2"><div class="col-6 col-md-3"><div class="detail-item"><small>Entradas</small><strong>${r.entradas} unidades</strong></div></div><div class="col-6 col-md-3"><div class="detail-item"><small>Ventas</small><strong>${r.salidas - r.perdidas} unidades</strong></div></div><div class="col-6 col-md-3"><div class="detail-item"><small>Pérdidas</small><strong>${r.perdidas} unidades</strong></div></div><div class="col-6 col-md-3"><div class="detail-item"><small>Stock actual</small><strong>${r.existencias} unidades</strong></div></div><div class="col-6 col-md-4"><div class="detail-item"><small>Ingresos por ventas</small><strong>${dinero(r.ingresos)}</strong></div></div><div class="col-6 col-md-4"><div class="detail-item"><small>Costo estimado</small><strong>${dinero(r.costo_ventas)}</strong></div></div><div class="col-12 col-md-4"><div class="detail-item"><small>Ganancia estimada</small><strong>${dinero(r.ganancia)}</strong></div></div></div>`; } catch (error) { mostrarMensaje(error.message, 'danger'); }
}
