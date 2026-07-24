@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-danger box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-warning"></i> Zona Crítica: Limpiar Tablas del Sistema</h3>
                </div>
                <div class="box-body text-center" style="padding: 30px;">
                    <p class="text-muted">
                        Esta acción vaciará <strong>únicamente las tablas operativas</strong> de tu sistema actual.
                    </p>
                    
                    <div class="alert alert-warning" style="margin: 20px 0;">
                        <strong>Atención:</strong> Se cerrará tu sesión al limpiar la tabla <code>users</code>.
                    </div>

                    <div class="form-group text-left">
                        <label>Escribe <strong>LIMPIAR MI BASE DE DATOS</strong> para confirmar:</label>
                        <input type="text" id="confirm_text" class="form-control input-lg text-center" autocomplete="off">
                    </div>

                    <button type="button" id="btn-ejecutar" class="btn btn-danger btn-lg btn-block">
                        <i class="fa fa-trash"></i> LIMPIAR TABLAS DEL SISTEMA
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#btn-ejecutar').on('click', function() {
            var texto = $('#confirm_text').val();
            var btn = $(this);

            if (texto !== 'LIMPIAR MI BASE DE DATOS') {
                Swal.fire('Error', 'Escribe la frase de confirmación exacta.', 'error');
                return;
            }

            Swal.fire({
                title: '¿Confirmas la limpieza?',
                text: "Se borrarán ventas, productos y clientes. No hay marcha atrás.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, limpiar sistema'
            }).then((result) => {
                // Validación para versiones 5.x de Laravel/SweetAlert
                if (result.value || result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> Limpiando...');

                    $.ajax({
                        url: "{{ route('vaciartablas.store') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            confirmacion: texto
                        },
                        success: function(data) {
                            Swal.fire('¡Logrado!', data.msg, 'success').then(function() {
                                window.location.href = "{{ url('/') }}";
                            });
                        },
                        error: function(xhr) {
                            var errorMsg = xhr.responseJSON ? xhr.responseJSON.msg : 'Error de servidor';
                            Swal.fire('Error', errorMsg, 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-trash"></i> LIMPIAR TABLAS DEL SISTEMA');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection