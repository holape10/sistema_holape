<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Asiento;
use MasterSoft\PlanContable;
use Illuminate\Support\Facades\DB;

class AsientoController extends Controller
{
    // ==============================================================================
    // VISTA PRINCIPAL CON FILTROS (PAGINADA)
    // ==============================================================================
    public function index(Request $request)
    {
        $mes = $request->get('mes', date('m'));
        $anio = $request->get('anio', date('Y'));

        // Usamos paginate en lugar de get() para que no colapse si hay 5,000 registros
        $asientos = Asiento::with('detalles.cuenta')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50); // Muestra de 50 en 50

        // Agregamos los parámetros a la paginación para no perder el filtro al cambiar de página
        $asientos->appends(['mes' => $mes, 'anio' => $anio]);

        return view('empresas.contabilidad.asientos.index', compact('asientos', 'mes', 'anio'));
    }

    // ==============================================================================
    // EXPORTAR REPORTE A EXCEL (Formato Oficial)
    // ==============================================================================
    public function reporteExcel(Request $request)
    {
        $mes = $request->get('mes', date('m'));
        $anio = $request->get('anio', date('Y'));

        $asientos = Asiento::with('detalles.cuenta')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha', 'asc')
            ->get();

        $filename = "Libro_Diario_{$anio}_{$mes}.xls";

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Cache-Control: max-age=0");

        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
        echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ';
        echo 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Estilos
        echo ' <Styles>';
        echo '  <Style ss:ID="Header"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#4F81BD" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
        echo '  <Style ss:ID="Texto"><NumberFormat ss:Format="@"/></Style>';
        echo '  <Style ss:ID="Numero"><NumberFormat ss:Format="0.00"/></Style>';
        echo '  <Style ss:ID="Negrita"><Font ss:Bold="1"/></Style>';
        echo ' </Styles>';

        echo ' <Worksheet ss:Name="Libro Diario">' . "\n";
        echo '  <Table>' . "\n";
        
        // Ancho de columnas
        echo '   <Column ss:Width="80"/>' . "\n";
        echo '   <Column ss:Width="80"/>' . "\n";
        echo '   <Column ss:Width="300"/>' . "\n";
        echo '   <Column ss:Width="80"/>' . "\n";
        echo '   <Column ss:Width="80"/>' . "\n";

        // Título del Reporte
        echo '   <Row ss:Height="30">' . "\n";
        echo '    <Cell ss:MergeAcross="4" ss:StyleID="Negrita"><Data ss:Type="String">LIBRO DIARIO - PERIODO ' . $mes . '/' . $anio . '</Data></Cell>' . "\n";
        echo '   </Row>' . "\n";

        // Cabeceras
        echo '   <Row ss:Height="22">' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Fecha</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Código Cuenta</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Glosa / Denominación</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Debe</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Haber</Data></Cell>' . "\n";
        echo '   </Row>' . "\n";

        $totalDebeGeneral = 0;
        $totalHaberGeneral = 0;

        foreach ($asientos as $asiento) {
            $fecha = date('d/m/Y', strtotime($asiento->fecha));
            
            // Fila de Glosa Principal
            echo '   <Row>' . "\n";
            echo '    <Cell ss:StyleID="Texto"><Data ss:Type="String">' . $fecha . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Negrita"><Data ss:Type="String">' . htmlspecialchars($asiento->glosa) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
            echo '   </Row>' . "\n";

            // Detalles del Asiento
            foreach ($asiento->detalles as $det) {
                $totalDebeGeneral += $det->debe;
                $totalHaberGeneral += $det->haber;

                echo '   <Row>' . "\n";
                echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="Texto"><Data ss:Type="String">' . htmlspecialchars($det->cuenta->codigo) . '</Data></Cell>' . "\n";
                echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($det->cuenta->nombre) . '</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="Numero"><Data ss:Type="Number">' . $det->debe . '</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="Numero"><Data ss:Type="Number">' . $det->haber . '</Data></Cell>' . "\n";
                echo '   </Row>' . "\n";
            }
            
            // Espacio entre asientos
            echo '   <Row><Cell><Data ss:Type="String"></Data></Cell></Row>' . "\n";
        }

        // Totales del Mes
        echo '   <Row>' . "\n";
        echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
        echo '    <Cell><Data ss:Type="String"></Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">TOTALES DEL MES:</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Numero"><Data ss:Type="Number">' . $totalDebeGeneral . '</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Numero"><Data ss:Type="Number">' . $totalHaberGeneral . '</Data></Cell>' . "\n";
        echo '   </Row>' . "\n";

        echo '  </Table>' . "\n";
        echo ' </Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
        exit;
    }

    // ==============================================================================
    // EXPORTAR REPORTE A PDF (Vista de Impresión)
    // ==============================================================================
    public function reportePdf(Request $request)
    {
        $mes = $request->get('mes', date('m'));
        $anio = $request->get('anio', date('Y'));

        $asientos = Asiento::with('detalles.cuenta')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->orderBy('fecha', 'asc')
            ->get();

        return view('empresas.contabilidad.asientos.reporte_pdf', compact('asientos', 'mes', 'anio'));
    }
}