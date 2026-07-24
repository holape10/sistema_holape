<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tipoDocDescripcion }} - {{ $cabpdf->serdoc }}-{{ $cabpdf->numdoc }}</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 11px; 
            color: #2c3e50; 
            margin: 0; 
            padding: 0; 
            background: #ffffff; 
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; }
        .no-border, .no-border td, .no-border th { border: none; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Estilos del original modernizado */
        .panel-numeracion {
            text-align: center;
            background: white;
            border: 2px solid #007bff;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        .ruc-text { font-size: 13px; font-weight: bold; color: #495057; margin-bottom: 5px; }
        .comprobante-badge {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 8px;
            margin: 8px 0;
            font-weight: bold;
            font-size: 13px;
            border-radius: 5px;
        }
        .numero-doc { font-size: 15px; font-weight: bold; color: #007bff; margin-top: 5px; }

        .panel-cliente {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 0 8px 8px 0;
            padding: 12px;
            margin-bottom: 20px;
        }
        .cliente-label { font-weight: 600; color: #2c3e50; font-size: 10px; }
        .cliente-value { color: #495057; font-size: 10px; }

        .table-detalle { 
            border: 2px solid #dee2e6; 
            border-radius: 8px; 
            border-spacing: 0; 
            border-collapse: separate; 
            overflow: hidden; 
        }
        .table-detalle thead tr { background: linear-gradient(135deg, #495057 0%, #343a40 100%); }
        .table-detalle th { 
            color: white; 
            font-weight: bold; 
            font-size: 10px; 
            text-align: center; 
            border-right: 1px solid rgba(255,255,255,0.2); 
        }
        .table-detalle td { font-size: 10px; border-right: 1px solid #dee2e6; border-top: 1px solid #dee2e6; }
        
        .caja-totales {
            background: #f8f9fa; 
            border: 2px solid #dee2e6; 
            border-radius: 8px; 
            padding: 10px;
        }
        .table-total { width: 100%; font-size: 10px; }
        .table-total td { padding: 6px; border-bottom: 1px solid #dee2e6; }
        .table-total .label-total { font-weight: bold; color: #2c3e50; }
        .table-total .monto-total { font-weight: bold; color: #007bff; font-size: 13px; }
        
        .hash-footer {
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px;
            border-radius: 8px;
            font-size: 9px;
            color: #6c757d;
            margin-top: 25px;
        }
    </style>
</head>
<body>

    <table class="no-border" style="margin-bottom: 20px;">
        <tr>
            <td width="60%" style="vertical-align: top;">
                @if(isset($logoFinal) && !empty($logoFinal))
                    <img src="{{ public_path($logoFinal) }}" style="max-height: 85px; max-width: 250px; margin-bottom: 12px;">
                @endif
                <h2 style="margin: 0; font-size: 18px; color: #2c3e50;">{{ isset($empresa) ? $empresa->NomEmpresa : 'EMPRESA' }}</h2>
                <p style="margin: 5px 0 0 0; font-size: 11px; color: #6c757d;"><strong>Dirección:</strong> {{ isset($empresa) ? $empresa->DirEmpresa : '' }}</p>
            </td>
            <td width="40%" style="vertical-align: top;">
                <div class="panel-numeracion">
                    <div class="ruc-text">R.U.C. {{ isset($empresa) ? $empresa->IdEmpresa : '' }}</div>
                    <div class="ruc-text">{{ $tipoDocDescripcion }}</div>
                    <div class="numero-doc">{{ $cabpdf->serdoc }} - {{ str_pad($cabpdf->numdoc, 8, "0", STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="panel-cliente">
        <table class="no-border" style="margin: 0;">
            <tr>
                <td width="15%" class="cliente-label">Cliente:</td>
                <td width="55%" class="cliente-value">{{ $cabpdf->ccanom }}</td>
                <td width="15%" class="cliente-label">Fecha:</td>
                <td width="15%" class="cliente-value">{{ date('d/m/Y', strtotime($cabpdf->ccafem)) }}</td>
            </tr>
            <tr>
                <td class="cliente-label">RUC/DNI:</td>
                <td class="cliente-value">{{ $cabpdf->ccandi }}</td>
                <td class="cliente-label">Moneda:</td>
                <td class="cliente-value">{{ $cabpdf->moncod == 'USD' ? 'DÓLARES' : 'SOLES' }}</td>
            </tr>
            <tr>
                <td class="cliente-label">Dirección:</td>
                <td colspan="3" class="cliente-value">{{ !empty($cabpdf->direccion) ? $cabpdf->direccion : '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="table-detalle">
        <thead>
            <tr>
                <th width="10%">CANT.</th>
                <th width="50%">DESCRIPCIÓN</th>
                <th width="20%">P. UNIT.</th>
                <th width="20%">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detpdf as $det)
                <tr>
                    <td class="text-center">{{ (float)$det->cdecan }}</td>
                    <td>{{ $det->cdedes }}</td>
                    <td class="text-right">{{ number_format($det->cdepuni, 2) }}</td>
                    <td class="text-right">{{ number_format($det->cdevve, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="no-border" style="margin-top: 20px;">
        <tr>
            <td width="65%" style="vertical-align: bottom; padding-right: 20px;">
                <div style="background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; border-radius: 4px; margin-bottom: 10px;">
                    <span style="font-weight: bold; color: #2c3e50;">SON:</span> <span style="color: #495057; font-size: 10px;">{{ $totalletras }}</span>
                </div>
            </td>
            <td width="35%" style="vertical-align: bottom;">
                <div class="caja-totales">
                    <table class="table-total no-border" style="margin: 0;">
                        <tr>
                            <td class="label-total">Op. Exonerada:</td>
                            <td class="text-right">{{ number_format($cabpdf->ccatexo, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label-total">Op. Gravada:</td>
                            <td class="text-right">{{ number_format($cabpdf->ccatvg, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label-total">IGV (18%):</td>
                            <td class="text-right">{{ number_format($cabpdf->ccaigv, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label-total monto-total" style="border-bottom: none;">Total:</td>
                            <td class="text-right monto-total" style="border-bottom: none;">{{ number_format($cabpdf->ccaitv, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="hash-footer">
        <strong>BIENES TRANSFERIDOS O SERVICIOS PRESTADOS EN LA AMAZONÍA PARA SER CONSUMIDOS EN LA MISMA.</strong><br>
        Representación impresa generada por sistema Hola P.
    </div>

</body>
</html>