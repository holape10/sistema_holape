@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cutlery"></i> Asignar Stock de Platos Preparados del Día</h3>
        </div>
        
        <div class="panel-body">
            <!-- Mensaje de éxito al guardar -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Buscador -->
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="buscadorPlatos" class="form-control" placeholder="Buscar plato o producto por nombre...">
                    </div>
                </div>
            </div>

            <!-- Formulario que envía los datos al controlador -->
            <form id="formStock" action="{{ route('kiosko.stock_preparados.guardar') }}" method="POST">
                @csrf
                
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-bordered table-striped table-hover" id="tablaPlatos">
                        <thead class="bg-primary">
                            <tr>
                                <th width="15%">Código</th>
                                <th>Nombre del Plato / Producto</th>
                                <th width="20%">Stock Disponible Hoy</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $pro)
                            <tr>
                                <td>{{ $pro->procod }}</td>
                                <!-- La clase 'nombre-plato' nos servirá para buscar -->
                                <td class="nombre-plato">{{ $pro->pronom }}</td>
                                <td>
                                    <!-- El name="stocks[ID]" crea un array que recibimos en el backend -->
                                    <input type="number" 
                                           name="stocks[{{ $pro->IdProducto }}]" 
                                           class="form-control text-center" 
                                           value="{{ number_format($pro->stock_preparados, 0) }}" 
                                           min="0" 
                                           step="1">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-right" style="margin-top: 20px;">
                    <!-- Cambiamos el type="submit" a type="button" para controlar el evento con JavaScript -->
                    <button type="button" class="btn btn-success btn-lg" onclick="confirmarGuardado()">
                        <i class="fa fa-save"></i> Guardar Stock del Día
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Agregamos SweetAlert2 para la alerta bonita de confirmación -->
<link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">

<!-- Script local -->
<script src="{{ asset('js/sweetalert2/sweetalert2.all.min.js') }}"></script>

<script>
    // 1. LÓGICA DEL BUSCADOR EN TIEMPO REAL
    document.getElementById('buscadorPlatos').addEventListener('keyup', function() {
        // Obtenemos lo que el usuario escribe y lo pasamos a mayúsculas
        let filtro = this.value.toUpperCase();
        let filas = document.getElementById('tablaPlatos').getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        // Recorremos todas las filas de la tabla
        for (let i = 0; i < filas.length; i++) {
            // Obtenemos la columna del nombre del plato (la segunda columna)
            let celdaNombre = filas[i].getElementsByClassName('nombre-plato')[0];
            
            if (celdaNombre) {
                let textoPlato = celdaNombre.textContent || celdaNombre.innerText;
                // Si el nombre coincide con lo escrito, mostramos la fila, si no, la ocultamos
                if (textoPlato.toUpperCase().indexOf(filtro) > -1) {
                    filas[i].style.display = "";
                } else {
                    filas[i].style.display = "none";
                }
            }
        }
    });

    // 2. LÓGICA DE LA ALERTA DE CONFIRMACIÓN
    function confirmarGuardado() {
        Swal.fire({
            title: '¿Estás completamente seguro?',
            text: "¡Revisa bien! Una vez guardado, este será el stock con el que trabajarán los mozos en el sistema durante el día.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745', // Color verde
            cancelButtonColor: '#d33',     // Color rojo
            confirmButtonText: 'SÍ, guardar stock',
            cancelButtonText: 'NO, revisar de nuevo'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si presiona SÍ, enviamos el formulario
                document.getElementById('formStock').submit();
            }
        });
    }
</script>
@endsection