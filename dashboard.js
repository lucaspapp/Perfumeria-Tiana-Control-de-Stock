document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('fechaActual').textContent = new Date().toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' });
    document.getElementById('periodo').addEventListener('change', cargarDashboard);
    cargarDashboard();
});

async function cargarDashboard() {
    const periodo = document.getElementById('periodo').value;
    try {
        const datos = await pedirJson(`estadisticas.php?periodo=${periodo}`);
        const r = datos.resumen;
        document.getElementById('ingresos').textContent = dinero(r.ingresos);
        document.getElementById('ganancia').textContent = dinero(r.ganancia);
        document.getElementById('perdidas').textContent = dinero(r.perdidas);
        document.getElementById('existencias').textContent = Number(r.existencias).toLocaleString('es-AR');
        document.getElementById('productos').textContent = Number(r.productos || 0).toLocaleString('es-AR');
        document.getElementById('periodoTexto').textContent = `${fechaBonita(datos.fecha_inicio)} al ${fechaBonita(datos.fecha_fin)}`;
        const filas = datos.diario.map(item => `<tr><td>${fechaBonita(item.fecha)}</td><td>${item.entradas}</td><td>${item.salidas}</td><td>${item.ventas}</td><td class="text-danger">${item.perdidas}</td></tr>`).join('');
        document.getElementById('tablaDiaria').innerHTML = filas || '<tr><td colspan="5" class="text-center text-muted py-4">No hay movimientos en este período.</td></tr>';
    } catch (error) {
        mostrarMensaje(error.message, 'danger');
        document.getElementById('tablaDiaria').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No se pudo cargar la información.</td></tr>';
    }
}
