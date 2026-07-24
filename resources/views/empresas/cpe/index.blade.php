<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Comprobantes - {{ isset($empresa) ? $empresa->NomEmpresa : 'Empresa' }}</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; }
        .table-detalle th { background-color: #f1f3f5; font-size: 0.9rem; }
        .table-detalle td { font-size: 0.9rem; vertical-align: middle; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">{{ isset($empresa) ? $empresa->NomEmpresa : 'Empresa' }}</h2>
                <h6 class="text-dark fw-bold">RUC: {{ isset($empresa) ? $empresa->IdEmpresa : '' }}</h6>
                <p class="text-muted small mb-2">{{ isset($empresa) ? $empresa->DirEmpresa : '' }}</p>
                <hr class="w-25 mx-auto">
                <p class="text-muted">Consulta la validez de tu comprobante electrónico y descarga tus archivos</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger shadow-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('cpe.search') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipo de Comprobante</label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="03" {{ old('tipo_documento') == '03' ? 'selected' : '' }}>Boleta de Venta Electrónica</option>
                                <option value="01" {{ old('tipo_documento') == '01' ? 'selected' : '' }}>Factura Electrónica</option>
                                <option value="07" {{ old('tipo_documento') == '07' ? 'selected' : '' }}>Nota de Crédito Electrónica</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Serie</label>
                                <input type="text" name="serie" class="form-control text-uppercase" placeholder="B001" value="{{ old('serie') }}" required maxlength="4">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-semibold">Número</label>
                                <input type="number" name="numero" class="form-control" placeholder="1245" value="{{ old('numero') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Fecha de Emisión</label>
                                <input type="date" name="fecha" class="form-control" value="{{ old('fecha') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Monto Total (S/)</label>
                                <input type="number" step="0.01" name="total" class="form-control" placeholder="0.00" value="{{ old('total') }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Consultar Documento</button>
                    </form>
                </div>
            </div>

            @if(isset($comprobante))
                <div class="card shadow-sm border-success mb-5">
                    <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">✓ Comprobante Encontrado</h5>
                        <span class="badge bg-light text-success fs-6">{{ strtoupper($sunatStatus) }}</span>
                    </div>
                    
                    <div class="card-body p-4">
                        
                        <div class="row mb-4 bg-light p-3 rounded">
                            <div class="col-md-7">
                                <p class="mb-1 text-muted small">Cliente</p>
                                <strong class="d-block mb-2">{{ $comprobante->ccanom }}</strong>
                                
                                <p class="mb-1 text-muted small">RUC / DNI</p>
                                <strong>{{ $comprobante->ccandi }}</strong>
                            </div>
                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <p class="mb-1 text-muted small">N° Documento</p>
                                <h4 class="text-primary mb-2 fw-bold">{{ $comprobante->serdoc }}-{{ str_pad($comprobante->numdoc, 8, "0", STR_PAD_LEFT) }}</h4>
                                
                                <p class="mb-1 text-muted small">Fecha de Emisión</p>
                                <strong>{{ date('d/m/Y', strtotime($comprobante->ccafem)) }}</strong>
                            </div>
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-detalle">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="10%">CANT</th>
                                        <th width="50%">DESCRIPCIÓN</th>
                                        <th class="text-end" width="20%">P. UNIT</th>
                                        <th class="text-end" width="20%">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($detalles))
                                        @foreach($detalles as $det)
                                        <tr>
                                            <td class="text-center">{{ (float)$det->cdecan }}</td>
                                            <td>{{ $det->cdedes }}</td>
                                            <td class="text-end">{{ number_format($det->cdepuni, 2) }}</td>
                                            <td class="text-end">{{ number_format($det->cdevve, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end text-muted small fw-bold">OP. GRAVADA</td>
                                        <td class="text-end fw-bold">{{ $comprobante->moncod == 'USD' ? '$' : 'S/' }} {{ number_format($comprobante->ccatvg, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end text-muted small fw-bold">IGV (18%)</td>
                                        <td class="text-end fw-bold">{{ $comprobante->moncod == 'USD' ? '$' : 'S/' }} {{ number_format($comprobante->ccaigv, 2) }}</td>
                                    </tr>
                                    <tr class="table-active">
                                        <td colspan="3" class="text-end fw-bold fs-5">TOTAL</td>
                                        <td class="text-end fw-bold fs-5 text-success">{{ $comprobante->moncod == 'USD' ? '$' : 'S/' }} {{ number_format($comprobante->ccaitv, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <hr>

                        <h6 class="fw-bold text-secondary mt-3 mb-3">Descargar archivos del comprobante:</h6>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            
                            <a href="{{ $links['pdf'] }}" class="btn btn-danger btn-md fw-bold">📄 PDF</a>

                            @if($links['xml'])
                                <a href="{{ $links['xml'] }}" class="btn btn-outline-dark btn-md" download>📁 XML</a>
                            @else
                                <button class="btn btn-light btn-md" disabled>⚠️ XML no disponible</button>
                            @endif

                            @if($links['cdr'])
                                <a href="{{ $links['cdr'] }}" class="btn btn-outline-warning btn-md text-dark" download>📦 CDR (ZIP)</a>
                            @else
                                <button class="btn btn-light btn-md" disabled>⚠️ CDR no disponible</button>
                            @endif
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

</body>
</html>