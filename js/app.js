const API = 'api/';

function mostrarMensaje(texto, tipo = 'success') {
    const contenedor = document.getElementById('mensaje');
    if (!contenedor) return;
    contenedor.innerHTML = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">${escapar(texto)}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>`;
}

function escapar(texto) {
    return String(texto ?? '').replace(/[&<>'"]/g, caracter => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;'}[caracter]));
}

function dinero(valor) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 2 }).format(Number(valor) || 0);
}

async function pedirJson(url, opciones = {}) {
    const configuracion = {
        ...opciones,
        headers: {
            Accept: 'application/json',
            ...(opciones.headers || {})
        }
    };
    const respuesta = await fetch(API + url, configuracion);
    const datos = await respuesta.json();
    if (!respuesta.ok || datos.success === false) throw new Error(datos.message || 'Ocurrió un error.');
    return datos;
}

function fechaBonita(fecha) {
    if (!fecha) return '-';
    const partes = fecha.substring(0, 10).split('-');
    return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : fecha;
}
