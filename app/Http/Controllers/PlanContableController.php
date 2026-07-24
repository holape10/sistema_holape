<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\PlanContable;

class PlanContableController extends Controller
{
    public function index()
    {
        $cuentas = PlanContable::orderBy('codigo', 'asc')->get();
        return view('empresas.contabilidad.plan_contable.index', compact('cuentas'));
    }

    public function create()
    {
        return view('empresas.contabilidad.plan_contable.create');
    }

    public function store(Request $request)
    {
        PlanContable::create($request->all());
        return redirect()->route('plan-contable.index')->with('success', 'Cuenta guardada correctamente.');
    }

    // ==============================================================================
    // EXPORTAR PLAN CONTABLE A EXCEL NATIVO (.XLSX)
    // ==============================================================================
    public function exportarExcel()
    {
        $filename = "plan_contable_" . date('Ymd_His') . ".xls"; // Usamos estructura Spreadsheet XML compatible
        $cuentas = PlanContable::orderBy('codigo', 'asc')->get();

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Cache-Control: max-age=0");

        // Construimos el archivo Excel usando XML para asegurar celdas y columnas nativas perfectas
        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
        echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
        echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ';
        echo 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Estilos básicos (Encabezado con color corporativo azul)
        echo ' <Styles>';
        echo '  <Style ss:ID="Header">';
        echo '   <Font ss:Bold="1" ss:Color="#FFFFFF"/>';
        echo '   <Interior ss:Color="#1F497D" ss:Pattern="Solid"/>';
        echo '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
        echo '  </Style>';
        echo '  <Style ss:ID="Texto">';
        echo '   <NumberFormat ss:Format="@"/>'; // Formato de texto para que no borre ceros a la izquierda
        echo '  </Style>';
        echo ' </Styles>';

        echo ' <Worksheet ss:Name="Plan Contable">' . "\n";
        echo '  <Table>' . "\n";
        
        // Ancho de columnas sugerido
        echo '   <Column ss:Width="100"/>' . "\n";
        echo '   <Column ss:Width="300"/>' . "\n";
        echo '   <Column ss:Width="100"/>' . "\n";
        echo '   <Column ss:Width="80"/>' . "\n";
        echo '   <Column ss:Width="120"/>' . "\n";

        // Cabeceras
        echo '   <Row ss:Height="22">' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">codigo</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">nombre</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">tipo</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">nivel</Data></Cell>' . "\n";
        echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">acepta_movimiento</Data></Cell>' . "\n";
        echo '   </Row>' . "\n";

        // Filas de Datos
        foreach ($cuentas as $cuenta) {
            echo '   <Row>' . "\n";
            echo '    <Cell ss:StyleID="Texto"><Data ss:Type="String">' . htmlspecialchars($cuenta->codigo) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($cuenta->nombre) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($cuenta->tipo) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . intval($cuenta->nivel) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . intval($cuenta->acepta_movimiento) . '</Data></Cell>' . "\n";
            echo '   </Row>' . "\n";
        }

        echo '  </Table>' . "\n";
        echo ' </Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
        exit;
    }

    // ==============================================================================
    // IMPORTAR PLAN CONTABLE DESDE EXCEL
    // ==============================================================================
    public function importarExcel(Request $request)
    {
        if (!$request->hasFile('archivo_excel')) {
            return back()->with('error', 'Por favor, seleccione un archivo de Excel.');
        }

        $file = $request->file('archivo_excel');
        $path = $file->getRealPath();

        // Leemos el archivo. Si es un XML de Excel o un CSV limpio, lo procesamos de forma segura.
        $contenido = file_get_contents($path);
        
        $insertados = 0;
        $omitidos = 0;

        // Comprobamos si viene de nuestro formato estructurado XML
        if (strpos($contenido, 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"') !== false) {
            $xml = simplexml_load_string($contenido);
            if ($xml === false) {
                return back()->with('error', 'Error al leer la estructura del archivo Excel.');
            }

            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
            
            $rows = $xml->xpath('//ss:Worksheet/ss:Table/ss:Row');
            
            // Saltamos la primera fila de cabecera
            for ($i = 1; $i < count($rows); $i++) {
                $cells = $rows[$i]->xpath('ss:Cell/ss:Data');
                
                if (count($cells) < 5) continue;

                $codigo            = trim((string)$cells[0]);
                $nombre            = trim((string)$cells[1]);
                $tipo              = strtolower(trim((string)$cells[2]));
                $nivel             = intval((string)$cells[3]);
                $acepta_movimiento = intval((string)$cells[4]);

                if (empty($codigo)) continue;

                $existe = PlanContable::where('codigo', $codigo)->exists();
                if (!$existe) {
                    PlanContable::create([
                        'codigo'            => $codigo,
                        'nombre'            => $nombre,
                        'tipo'              => $tipo,
                        'nivel'             => $nivel,
                        'acepta_movimiento' => $acepta_movimiento
                    ]);
                    $insertados++;
                } else {
                    $omitidos++;
                }
            }
        } else {
            // Fallback automático por si suben un CSV plano copiado por el usuario
            $handle = fopen($path, 'r');
            // Detectar separador dinámico
            $headers = fgetcsv($handle, 1000, ";");
            $separador = (count($headers) == 1) ? "," : ";";
            if (count($headers) == 1) { rewind($handle); }

            while (($data = fgetcsv($handle, 1000, $separador)) !== FALSE) {
                if (empty($data[0]) || $data[0] == 'codigo') continue;

                $codigo            = trim($data[0]);
                $nombre            = trim($data[1]);
                $tipo              = strtolower(trim($data[2]));
                $nivel             = intval($data[3]);
                $acepta_movimiento = intval($data[4]);

                $existe = PlanContable::where('codigo', $codigo)->exists();
                if (!$existe) {
                    PlanContable::create([
                        'codigo'            => $codigo,
                        'nombre'            => $nombre,
                        'tipo'              => $tipo,
                        'nivel'             => $nivel,
                        'acepta_movimiento' => $acepta_movimiento
                    ]);
                    $insertados++;
                } else {
                    $omitidos++;
                }
            }
            fclose($handle);
        }

        return redirect()->route('plan-contable.index')
            ->with('success', "Proceso terminado. Cuentas importadas: {$insertados}. Omitidas por duplicadas: {$omitidos}.");
    }
}