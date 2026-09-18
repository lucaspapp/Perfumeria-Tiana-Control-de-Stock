document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fechaActual').textContent = new Date().toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' });
    document.getElementById('periodo').addEventListener('change', cargarDashboard);
    cargarDashboard();
});

async function cargarDashboard() {
    const periodo = document.getElementById('periodo').value;
    try {
        const datos = await pedirJson(`estadisticas.json?periodo=${periodo}`);
        const r = datos.resumen;
        document.getElementById('ingresos').textContent = dinero(r.ingresos);
        document.getElementById('ganancia').textContent = dinero(r.ganancia);
        document.getElementById('perdidas').textContent = dinero(r.perdidas);
        document.getElementById('existencias').textContent = Number(r.existencias).toLocaleString('es-AR');
        document.getElementById('productos').textContent = Number(r.productos || 0).toLocaleString('es-AR');
        document.getElementById('periodoTexto').textContent = `${fechaBonita(datos.fecha_inicio)} al ${fechaBonita(datos.fecha_fin)}`;
        const filas = datos.diario.map(item => `<tr><td>${fechaBonita(item.fecha)}</td><td><strong>${escapar(item.codigo)}</strong> - ${escapar(item.nombre)}</td><td>${item.entradas}</td><td>${item.salidas}</td><td>${item.ventas}</td><td class="text-danger">${item.perdidas}</td></tr>`).join('');
        document.getElementById('tablaDiaria').innerHTML = filas || '<tr><td colspan="6" class="text-center text-muted py-4">No hay movimientos en este período.</td></tr>';
        document.getElementById('listaDiariaMovil').innerHTML = datos.diario.map(item => `<div class="list-group-item"><div class="d-flex justify-content-between gap-2"><strong>${escapar(item.codigo)} - ${escapar(item.nombre)}</strong><span class="small text-secondary">${fechaBonita(item.fecha)}</span></div><div class="row row-cols-2 row-cols-sm-4 g-2 mt-2 small"><div><span class="text-secondary d-block">Entradas</span><strong>${item.entradas}</strong></div><div><span class="text-secondary d-block">Salidas</span><strong>${item.salidas}</strong></div><div><span class="text-secondary d-block">Ventas</span><strong>${item.ventas}</strong></div><div><span class="text-secondary d-block">Pérdidas</span><strong class="text-danger">${item.perdidas}</strong></div></div></div>`).join('') || '<div class="list-group-item text-center text-secondary py-4">No hay movimientos en este período.</div>';
    } catch (error) {
        mostrarMensaje(error.message, 'danger');
        document.getElementById('tablaDiaria').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No se pudo cargar la información.</td></tr>';
        document.getElementById('listaDiariaMovil').innerHTML = '<div class="list-group-item text-center text-secondary py-4">No se pudo cargar la información.</div>';
    }
}
