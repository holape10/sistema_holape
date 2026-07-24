<div class="card">
    <div class="card-header">
        <h3 class="card-title">Flujo de Pedidos por Hora</h3>
        <p class="text-muted">Desde {{ $fec_ini }} hasta {{ $fec_fin }}</p>
    </div>
    <div class="card-body">
        <div style="position: relative; height:40vh; width:100%">
            <canvas id="chartFlujoHoras"></canvas>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleHora" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Pedidos - <span id="tituloHoraModal"></span>:00</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenidoModalDetalle">
                <div class="text-center"><div class="spinner-border text-primary" role="status"></div><br>Cargando...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>

<script>
function cargarDetallePorHora(hora) {
    // Abrimos el modal mostrando estado de carga
    $('#tituloHoraModal').text(hora);
    $('#contenidoModalDetalle').html('<div class="text-center">Cargando datos...</div>');
    $('#modalDetalleHora').modal('show');

    // Preparamos los datos a enviar (reutilizando las variables de tu vista)
    let payload = {
        hora: hora,
        fec_ini: '{{ $fec_ini }}',
        fec_fin: '{{ $fec_fin }}',
        suc_id: '{{ $suc_id ?? "" }}',
        _token: '{{ csrf_token() }}'
    };

    // Petición al backend
    fetch('/reportes/ventas/detalle-hora', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.text())
    .then(html => {
        // Metemos la tabla renderizada dentro del modal
        $('#contenidoModalDetalle').html(html);
    })
    .catch(error => {
        $('#contenidoModalDetalle').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
    });
}
</script>

<script>
    var ctx = document.getElementById('chartFlujoHoras').getContext('2d');
    
    // Creamos un degradado para las barras
    var gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
    gradient.addColorStop(1, 'rgba(54, 162, 235, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Cantidad de Pedidos',
                data: {!! json_encode($data) !!},
                // Usamos los colores dinámicos que enviamos desde PHP
                backgroundColor: {!! json_encode($backgroundColors) !!},
                borderColor: {!! json_encode($borderColors) !!},
                borderWidth: 2,
                borderRadius: 5, // Bordes redondeados (funciona mejor en versiones nuevas, en 2.7 es sutil)
                hoverBackgroundColor: 'rgba(255, 99, 132, 0.8)', // Cambio de color al pasar el mouse
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: {
                display: false // Ocultamos la leyenda para ganar espacio
            },

            onClick: function(evt, elements) {
                if (elements.length > 0) {
                    // elements[0] es la barra a la que le dimos clic
                    var index = elements[0]._index;
                    var labelHora = this.data.labels[index]; // Ej: "14:00"
                    var totalPedidos = this.data.datasets[0].data[index];

                    // Solo abrir el modal si hay pedidos en esa hora
                    if (totalPedidos > 0) {
                        // Extraemos solo el número (de "14:00" sacamos "14")
                        var horaSeleccionada = labelHora.split(':')[0]; 
                        cargarDetallePorHora(horaSeleccionada);
                    }
                }
            },



            scales: {
                yAxes: [{
                    gridLines: {
                        drawBorder: false,
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: {
                        beginAtZero: true,
                        fontStyle: 'bold',
                        padding: 10
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false // Limpiamos el fondo
                    },
                    ticks: {
                        fontStyle: 'bold'
                    }
                }]
            },
            tooltips: {
                backgroundColor: '#1e293b', // Color oscuro elegante
                titleFontSize: 14,
                titleFontColor: '#fff',
                bodyFontColor: '#fff',
                bodyFontSize: 13,
                displayColors: false,
                padding: 10,
                intersect: false,
                callbacks: {
                    label: function(tooltipItem, data) {
                        return ' 📦 Pedidos: ' + tooltipItem.yLabel;
                    }
                }
            }
        }
    });
</script>