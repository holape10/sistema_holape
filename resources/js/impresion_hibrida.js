/**
 * Maneja la impresión recibida desde Laravel
 * @param {Array|String} data - Puede ser un string Base64 (precuenta) o un Array de objetos (comandas)
 */
function gestionarImpresionHibrida(data) {
    if (!data) return;

    // Detección simple de móvil
    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (Array.isArray(data)) {
        // ES COMANDA (Múltiples tickets posibles: Cocina, Barra, etc.)
        data.forEach(function(ticket, index) {
            setTimeout(function() {
                imprimirItem(ticket.data, isMobile);
            }, index * 1000); // Pequeño delay entre tickets para no saturar
        });
    } else {
        // ES PRECUENTA (Un solo string Base64)
        imprimirItem(data, isMobile);
    }
}

function imprimirItem(base64Data, isMobile) {
    if (isMobile) {
        // Opción 1: Usar RawBT (Recomendado para Android)
        var url = 'rawbt:base64,' + base64Data;
        window.location.href = url;
    } else {
        // Opción 2: Enviar a Agente Local en PC Windows (localhost:8080)
        // Debes tener el script PHP corriendo en la PC de caja
        $.ajax({
            url: 'http://localhost:8080/impresor_local.php',
            type: 'POST',
            data: JSON.stringify({ contenido: base64Data }),
            contentType: 'application/json',
            success: function() { console.log("Enviado a impresora local"); },
            error: function() { 
                console.warn("Agente local no detectado en puerto 8080.");
                // Fallback: Si falla el agente, intentamos RawBT por si es una PC con App
                // window.location.href = 'rawbt:base64,' + base64Data;
            }
        });
    }
}