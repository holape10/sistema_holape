@extends('layouts.empresas')

@section('contenido')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white border-0 mt-2">
            <h4 class="mb-0">Emitir Guía de Remisión Remitente</h4>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('guias.store') }}" method="POST" id="formfact">
                @csrf
                
                <h5 class="text-info border-bottom pb-2">1. Datos del Comprobante</h5>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">Serie</label>
                        <input type="text" name="serieguia" class="form-control text-center font-weight-bold" value="{{ $serie }}" readonly>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="font-weight-bold">Número</label>
                        <input type="text" name="numeroguia" class="form-control text-center font-weight-bold text-danger" value="{{ $numero }}" readonly>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Fecha de Emisión</label>
                        <input type="date" name="fechaemision" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Fecha de Traslado</label>
                        <input type="date" name="fechatraslado" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Motivo de Traslado</label>
                        <select name="motivo" id="motivo" class="form-control" required>
                            <option value="01">Venta</option>
                            <option value="14">Venta sujeta a confirmación</option>
                            <option value="02">Compra</option>
                            <option value="04">Traslado entre establecimientos</option>
                            <option value="13">Otros</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Modalidad de Transporte</label>
                        <select name="modalidad" id="modalidad" class="form-control" required>
                            <option value="01">Transporte Público (Terceros)</option>
                            <option value="02" selected>Transporte Privado (Propio)</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Peso Bruto Total (KGM)</label>
                        <input type="number" step="0.01" name="pesobruto" class="form-control text-right" value="0.00" required>
                    </div>
                </div>

                <h5 class="text-info border-bottom pb-2 mt-4">2. Datos del Destinatario</h5>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">RUC</label>
                        <div class="input-group">
                            <input type="text" name="ruccliente" id="ruccliente" class="form-control" maxlength="11" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="buscarRUC('cliente')"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 form-group">
                        <label class="font-weight-bold">Razón Social</label>
                        <input type="text" name="nomcliente" id="nomcliente" class="form-control" required>
                    </div>
                </div>

                <h5 class="text-info border-bottom pb-2 mt-4">3. Datos del Transportista / Conductor</h5>
                
                <div class="row" id="div_dat_transp" style="display: none;">
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">RUC Transportista</label>
                        <div class="input-group">
                            <input type="text" name="transportistanum" id="transportistanum" class="form-control" maxlength="11">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="buscarRUC('transportista')"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 form-group">
                        <label class="font-weight-bold">Nombre Transportista</label>
                        <input type="text" name="transportistanom" id="transportistanom" class="form-control">
                    </div>
                </div>

                <div class="row" id="div_dat_cond">
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">DNI Conductor</label>
                        <div class="input-group">
                            <input type="text" name="conductornum" id="conductornum" class="form-control" maxlength="8">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="buscarDoc('conductor')"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">Nombre Conductor</label>
                        <input type="text" name="conductornom" id="conductornom" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="font-weight-bold">Placa Vehículo</label>
                        <input type="text" name="placa" id="placa" class="form-control">
                    </div>
                </div>

                <h5 class="text-info border-bottom pb-2 mt-4">4. Direcciones</h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Ubigeo Partida</label>
                        <select name="ubigeopartida" id="ubigeopartida" class="form-control select2-ubigeo" required>
                            <option value="160101" selected>IQUITOS - MAYNAS (160101)</option>
                        </select>
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">Dirección Partida</label>
                        <input type="text" name="direccionpartida" class="form-control" value="{{ $empresa->DirEmpresa ?? '' }}" required>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">Ubigeo Llegada</label>
                        <select name="ubigeollegada" id="ubigeollegada" class="form-control select2-ubigeo" required>
                        </select>
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">Dirección Llegada</label>
                        <input type="text" name="direccionllegada" id="direccionllegada" class="form-control" required>
                    </div>
                </div>

                <h5 class="text-info border-bottom pb-2 mt-4">5. Bienes a Transportar</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="tablaDetalles">
                        <thead class="thead-light text-center">
                            <tr>
                                <th width="15%">Código</th>
                                <th width="50%">Descripción del Bien</th>
                                <th width="15%">Cantidad</th>
                                <th width="15%">Peso (KGM)</th>
                                <th width="5%"><button type="button" class="btn btn-success btn-sm" id="btnAgregarProducto"><i class="fas fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4 mb-5">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('guias.index') }}" class="btn btn-secondary shadow-sm px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary shadow-sm px-4"><i class="fas fa-paper-plane"></i> Emitir Guía a SUNAT</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. LÓGICA DE MODALIDAD ---
        const modalidadSelect = document.getElementById('modalidad');
        const divTransp = document.getElementById('div_dat_transp');
        const divCond = document.getElementById('div_dat_cond');

        function toggleModalidad() {
            if (modalidadSelect.value === '01') {
                divTransp.style.display = 'flex'; divCond.style.display = 'none';
                document.getElementById('conductornum').value = '';
                document.getElementById('conductornom').value = '';
                document.getElementById('placa').value = '';
            } else {
                divTransp.style.display = 'none'; divCond.style.display = 'flex';
                document.getElementById('transportistanum').value = '';
                document.getElementById('transportistanom').value = '';
            }
        }
        toggleModalidad();
        modalidadSelect.addEventListener('change', toggleModalidad);

        // --- 2. LÓGICA PARA AGREGAR PRODUCTOS DINÁMICOS ---
        const btnAgregar = document.getElementById('btnAgregarProducto');
        const tbody = document.querySelector('#tablaDetalles tbody');

        btnAgregar.addEventListener('click', function() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="procod[]" class="form-control form-control-sm" placeholder="Cod." required></td>
                <td><input type="text" name="pronom[]" class="form-control form-control-sm" placeholder="Descripción del producto..." required></td>
                <td><input type="number" step="0.01" name="cantidad[]" class="form-control form-control-sm text-right" value="1.00" required></td>
                <td><input type="number" step="0.01" name="peso[]" class="form-control form-control-sm text-right" value="0.00" required></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm btnEliminarFila"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(tr);
        });

        tbody.addEventListener('click', function(e) {
            if (e.target.closest('.btnEliminarFila')) {
                e.target.closest('tr').remove();
            }
        });

        // --- 3. INICIALIZAR SELECT2 PARA UBIGEOS ---
        // Se ejecuta si tienes jQuery y Select2 cargados
        if($.fn.select2){
            $('.select2-ubigeo').select2({
                placeholder: 'Buscar distrito o ubigeo...',
                ajax: {
                    url: '{{ route("guias.buscarUbigeo") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        }
    });

    // --- 4. FUNCIÓN AJAX PARA CONSULTAR RUC ---
    function buscarRUC(tipo) {
        let inputRuc = tipo === 'cliente' ? '#ruccliente' : '#transportistanum';
        let inputNom = tipo === 'cliente' ? '#nomcliente' : '#transportistanom';
        let ruc = $(inputRuc).val();

        if(ruc.length === 11) {
            // Ponemos en modo carga
            $(inputNom).val('Buscando en SUNAT...');

            $.ajax({
                url: '{{ route("guias.consultaRuc") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ruc: ruc
                },
                success: function(response) {
                    if(response.error) {
                        alert(response.error);
                        $(inputNom).val('');
                    } else {
                        $(inputNom).val(response.nom);
                        
                        // Si es el cliente, llenamos la dirección y el ubigeo
                        if(tipo === 'cliente') {
                            $('#direccionllegada').val(response.dir);
                            
                            // Insertamos el Ubigeo con su nombre real de la base de datos
                            if(response.ubigeo && $.fn.select2) {
                                let newOption = new Option(response.ubigeo_des + ' (' + response.ubigeo + ')', response.ubigeo, true, true);
                                $('#ubigeollegada').append(newOption).trigger('change');
                            }
                        }
                    }
                },
                error: function() {
                    alert('Error en el servidor al consultar el RUC');
                    $(inputNom).val('');
                }
            });
        } else {
            alert('El RUC debe tener 11 dígitos.');
        }
    }

    function buscarDoc(tipo) {
        let inputDoc = '';
        let inputNom = '';

        if(tipo === 'cliente') { inputDoc = '#ruccliente'; inputNom = '#nomcliente'; }
        else if(tipo === 'transportista') { inputDoc = '#transportistanum'; inputNom = '#transportistanom'; }
        else if(tipo === 'conductor') { inputDoc = '#conductornum'; inputNom = '#conductornom'; }

        let documento = $(inputDoc).val();

        if(documento.length === 8 || documento.length === 11) {
            $(inputNom).val('Buscando...');

            $.ajax({
                url: '{{ route("guias.consultaDoc") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    documento: documento
                },
                success: function(response) {
                    if(response.error) {
                        alert(response.error);
                        $(inputNom).val('');
                    } else {
                        $(inputNom).val(response.nom);
                        
                        // Solo si es cliente y trajo ubigeo/dirección
                        if(tipo === 'cliente' && response.dir !== undefined) {
                            $('#direccionllegada').val(response.dir);
                            
                            if(response.ubigeo && $.fn.select2) {
                                let newOption = new Option(response.ubigeo_des + ' (' + response.ubigeo + ')', response.ubigeo, true, true);
                                $('#ubigeollegada').append(newOption).trigger('change');
                            }
                        }
                    }
                },
                error: function() {
                    alert('Error de conexión con el servidor.');
                    $(inputNom).val('');
                }
            });
        } else {
            alert('El documento debe tener 8 o 11 dígitos.');
        }
    }
</script>
@endsection