<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportarDatosController extends Controller
{
    public function index()
    {
        return view('empresas.mantenimiento.importar');
    }

    public function importarTabla(Request $request)
    {
        $tabla = $request->input('tabla');
        
        // Quitamos los límites de PHP por si acaso
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            if (!Schema::connection('antigua')->hasTable($tabla) || !Schema::hasTable($tabla)) {
                return response()->json(['res' => 'warning', 'msg' => "La tabla '{$tabla}' no existe en alguno de los lados. Omitida."]);
            }

            // 1. Obtener columnas
            $columnasViejas = Schema::connection('antigua')->getColumnListing($tabla);
            $columnasNuevas = Schema::getColumnListing($tabla);
            $columnasComunes = array_intersect($columnasViejas, $columnasNuevas);
            
            if (empty($columnasComunes)) {
                return response()->json(['res' => 'warning', 'msg' => "No hay columnas compatibles en '{$tabla}'."]);
            }

            // 2. Proteger los nombres de las columnas con comillas invertidas (backticks)
            $columnasConComillas = array_map(function($col) {
                return "`{$col}`";
            }, $columnasComunes);

            $columnasInsert = implode(', ', $columnasConComillas);
            $columnasSelect = implode(', ', $columnasConComillas);

            // 3. Revisar si la nueva necesita empresa y la vieja no la tiene
            $requiereEmpresa = in_array('id_empresa_negocio', $columnasNuevas);
            $tieneEmpresaVieja = in_array('id_empresa_negocio', $columnasViejas);

            if ($requiereEmpresa && !$tieneEmpresaVieja) {
                $columnasInsert .= ', `id_empresa_negocio`';
                $columnasSelect .= ', 1'; // Inyectamos el ID 1 estático temporalmente
            }

            // 4. Obtener los nombres reales de tus bases de datos desde la configuración
            $bdVieja = config('database.connections.antigua.database');
            $bdNueva = config('database.connections.mysql.database');

            // Preparar la base de datos (desactivar llaves foráneas y limpiar)
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($tabla)->truncate(); 
            
            // 5. LA MAGIA: Copia directa de BD a BD en puro SQL
            $sql = "INSERT INTO `{$bdNueva}`.`{$tabla}` ({$columnasInsert}) SELECT {$columnasSelect} FROM `{$bdVieja}`.`{$tabla}`";
            
            DB::statement($sql);

            // Reactivar llaves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json(['res' => 'success', 'msg' => "Tabla '{$tabla}' importada al instante."]);

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            \Log::error("Error importando {$tabla}: " . $e->getMessage());
            return response()->json(['res' => 'error', 'msg' => $e->getMessage()], 500);
        }
    }

    public function vistaBackup()
    {
        return view('empresas.mantenimiento.backup');
    }

    // Método para generar y descargar el SQL
    public function descargarBackup()
    {
        try {
            // 1. Obtener credenciales de la base de datos actual
            $database = config('database.connections.mysql.database');
            $user     = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host     = config('database.connections.mysql.host');

            // 2. Definir el nombre del archivo y la ruta donde se guardará temporalmente
            $fecha = date('Y-m-d_H-i-s');
            $fileName = "backup_{$database}_{$fecha}.sql";
            $storagePath = storage_path('app/' . $fileName);

            // 3. Construir el comando mysqldump
            // Si hay contraseña, se concatena sin espacio después de -p
            $passwordParam = $password ? "-p{$password}" : "";
            
            // OJO: En Windows puede que necesites poner la ruta completa a mysqldump.exe
            // En Linux normalmente funciona directo.
            $command = "mysqldump --user={$user} {$passwordParam} --host={$host} {$database} > {$storagePath}";

            // 4. Ejecutar el comando
            $output = null;
            $returnVar = null;
            exec($command, $output, $returnVar);

            // returnVar === 0 significa que el comando se ejecutó sin errores en la consola
            if ($returnVar === 0 && file_exists($storagePath)) {
                // 5. Descargar el archivo y eliminarlo del servidor automáticamente después
                return response()->download($storagePath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Error al generar el backup. Verifica que mysqldump esté accesible en el servidor.');
            }

        } catch (Exception $e) {
            return back()->with('error', 'Ocurrió un error inesperado: ' . $e->getMessage());
        }
    }
 
}