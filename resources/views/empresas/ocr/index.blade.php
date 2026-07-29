<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MasterSoft - Extracción IA de Comprobantes</title>
    
    <!-- Bootstrap 4 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body { background-color: #f4f6f9; }
        .drop-zone {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background: #ffffff;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .drop-zone:hover, .drop-zone.dragover {
            background: #e9ecef;
        }
        .preview-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: #fff;
            padding: 10px;
        }
        .preview-container img, .preview-container iframe {
            width: 100%;
            border-radius: 4px;
        }
        .spinner-overlay {
            display: none;
            position: absolute;
            top:0; left:0; width:100%; height:100%;
            background: rgba(255,255,255,0.85);
            z-index: 10;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="font-weight-bold text-dark">
                <i class="fas fa-robot text-primary"></i> Lectura Inteligente de Comprobantes (IA)
            </h3>
            <p class="text-muted">Carga una foto o PDF de tu factura o boleta para rellenar los datos automáticamente.</p>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Carga y Vista Previa -->
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white font-weight-bold">
                    <i class="fas fa-upload"></i> Documento
                </div>
                <div class="card-body">
                    <form id="ocrForm" enctype="multipart/form-data">
                        @csrf
                        <div class="drop-zone" id="dropZone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                            <p class="mb-1 font-weight-bold">Arrastra tu archivo aquí o haz clic para examinar</p>
                            <small class="text-muted">Formatos aceptados: JPG, PNG, PDF (Máx 10MB)</small>
                            <input type="file" id="fileInput" name="document" accept="image/jpeg,image/png,application/pdf" style="display: none;">
                        </div>
                    </form>

                    <div class="mt-3 preview-container d-none" id="previewBox">
                        <h6 class="font-weight-bold text-secondary mb-2">Vista previa:</h6>
                        <div id="previewContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario de Datos Extraídos -->
        <div class="col-md-7">
            <div class="card shadow-sm position-relative">
                
                <!-- Loading Overlay -->
                <div class="spinner-overlay" id="loadingOverlay">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="font-weight-bold text-primary">Procesando documento con IA...</h5>
                    <span class="text-muted">Analizando RUC, serie, montos e ítems...</span>
                </div>

                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice"></i> Datos Extraídos</span>
                    <span class="badge badge-success d-none" id="successBadge"><i class="fas fa-check"></i> Extracción Completada</span>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">RUC Emisor</label>
                            <input type="text" class="form-control" id="ruc_emisor" placeholder="RUC">
                        </div>
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Razón Social</label>
                            <input type="text" class="form-control" id="razon_social" placeholder="Nombre de la empresa">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tipo Doc.</label>
                            <input type="text" class="form-control" id="tipo_documento" placeholder="Factura / Boleta">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Serie y Número</label>
                            <input type="text" class="form-control" id="serie_numero" placeholder="F001-000000">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Fecha Emisión</label>
                            <input type="date" class="form-control" id="fecha_emision">
                        </div>
                    </div>

                    <h6 class="font-weight-bold mt-3 mb-2"><i class="fas fa-list"></i> Detalle de Productos / Servicios</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Descripción</th>
                                    <th width="80">Cant.</th>
                                    <th width="110">P. Unit.</th>
                                    <th width="110">Importe</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Sin datos extraídos aún</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-row justify-content-end mt-3">
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">Op. Gravada</label>
                            <input type="text" class="form-control text-right" id="monto_op_gravada" readonly>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="font-weight-bold">IGV (18%)</label>
                            <input type="text" class="form-control text-right" id="monto_igv" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold text-primary">Monto Total</label>
                            <input type="text" class="form-control text-right font-weight-bold border-primary" id="monto_total" readonly>
                        </div>
                    </div>

                    <hr>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">Limpiar</button>
                        <button type="button" class="btn btn-success"><i class="fas fa-save"></i> Guardar Comprobante</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const $dropZone = $('#dropZone');
    const $fileInput = $('#fileInput');

    // 1. Evitar bucle infinito deteniendo la propagación al hacer clic en el input
    $dropZone.on('click', function(e) {
        if (e.target !== $fileInput[0]) {
            $fileInput.trigger('click');
        }
    });

    $fileInput.on('click', function(e) {
        e.stopPropagation();
    });

    // 2. Drag & Drop
    $dropZone.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    $dropZone.on('dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    $dropZone.on('drop', function(e) {
        const files = e.originalEvent.dataTransfer.files;
        if (files && files.length > 0) {
            $fileInput[0].files = files;
            handleFileSelect(files[0]);
        }
    });

    $fileInput.on('change', function() {
        if (this.files && this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    function handleFileSelect(file) {
        showPreview(file);
        uploadAndProcess(file);
    }

    function showPreview(file) {
        const reader = new FileReader();
        const $previewBox = $('#previewBox');
        const $previewContent = $('#previewContent');

        $previewBox.removeClass('d-none');
        $previewContent.empty();

        if (file.type.startsWith('image/')) {
            reader.onload = function(e) {
                $previewContent.html(`<img src="${e.target.result}" alt="Preview" class="img-fluid">`);
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            const url = URL.createObjectURL(file);
            $previewContent.html(`<iframe src="${url}" height="400px" style="width:100%; border:none;"></iframe>`);
        }
    }

    function uploadAndProcess(file) {
        const formData = new FormData();
        formData.append('document', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: "{{ route('ocr.process') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#loadingOverlay').hide();
                if (response.success && response.data) {
                    fillForm(response.data);
                    $('#successBadge').removeClass('d-none');
                } else {
                    alert('No se pudieron extraer datos del documento.');
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                const err = xhr.responseJSON ? xhr.responseJSON.message : 'Error al procesar el archivo';
                alert('Error: ' + err);
            }
        });
    }

    function fillForm(data) {
        $('#ruc_emisor').val(data.ruc_emisor || '');
        $('#razon_social').val(data.razon_social || '');
        $('#tipo_documento').val(data.tipo_documento || '');
        $('#serie_numero').val(data.serie_numero || '');
        $('#fecha_emision').val(data.fecha_emision || '');
        $('#monto_op_gravada').val(data.monto_op_gravada || '0.00');
        $('#monto_igv').val(data.monto_igv || '0.00');
        $('#monto_total').val(data.monto_total || '0.00');

        const $tbody = $('#itemsTableBody');
        $tbody.empty();

        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                $tbody.append(`
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" value="${item.descripcion || ''}"></td>
                        <td><input type="text" class="form-control form-control-sm text-center" value="${item.cantidad || 1}"></td>
                        <td><input type="text" class="form-control form-control-sm text-right" value="${item.precio_unitario || '0.00'}"></td>
                        <td><input type="text" class="form-control form-control-sm text-right" value="${item.importe || '0.00'}"></td>
                    </tr>
                `);
            });
        } else {
            $tbody.html('<tr><td colspan="4" class="text-center text-muted">No se detectaron ítems individuales.</td></tr>');
        }
    }
});
</script>
</body>
</html>