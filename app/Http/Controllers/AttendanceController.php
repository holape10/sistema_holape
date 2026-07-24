<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Attendance;
use MasterSoft\empleado;
use MasterSoft\TurnoAsistencia;
use MasterSoft\AsistenciaHorario;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $negocio = \DB::table('empresa_negocios')->where('id_empresa_negocio', 1)->first();
        $ipPermitida = $negocio ? $negocio->ip_asistencia : null; 
        $ipCliente = $request->ip(); 

        if (!empty($ipPermitida)) {
            $ipsLocales = ['::1', '127.0.0.1', 'localhost'];
            if (!in_array($ipCliente, $ipsLocales) && $ipCliente !== $ipPermitida) {
                return response()->view('empresas.asistencia.mensaje_celular', [
                    'nombre_completo' => 'Acceso Restringido',
                    'mensaje' => 'El sistema de asistencia solo está disponible dentro de las instalaciones de la empresa. (Tu IP: ' . $ipCliente . ')',
                    'tipo' => 'danger'
                ]);
            }
        }

        $empleados = empleado::where('asistencia', '1')->orderBy('emp_nom', 'asc')->get(); 
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        
        $asistenciasHoy = Attendance::where('date', $today)->orderBy('id', 'desc')->get()->keyBy('user_id');
        
        // --- NUEVA LÓGICA: EVALUAR FALTAS Y DESCANSOS ---
        $estadosVisuales = [];
        foreach ($empleados as $emp) {
            $horario = AsistenciaHorario::with('turno')->where('emp_id', $emp->emp_id)->where('fecha', $today)->first();
            $asistencia = $asistenciasHoy->get($emp->emp_id);
            
            $estado = 'normal'; // Por defecto, estado normal (esperando marcar)
            
            if (!$horario) {
                // Si no tiene horario asignado en la matriz, asumimos Descanso
                $estado = 'descanso';
            } elseif ($horario && $horario->turno) {
                $turno = $horario->turno;
                // Si es un turno especial configurado como descanso (ejemplo: si tuvieras un turno "D")
                if (strtoupper($turno->codigo) === 'D' || strtoupper($turno->codigo) === 'DESCANSO') {
                     $estado = 'descanso';
                }
                // Evaluar Falta: Si no hay asistencia y la hora actual supera la tolerancia de entrada
                elseif (!$asistencia && $turno->hora_entrada_1) {
                    $esperada1 = Carbon::parse($today . ' ' . $turno->hora_entrada_1);
                    $limite1 = $esperada1->copy()->addMinutes($turno->tolerancia_minutos ?? 15);
                    
                    if ($now->gt($limite1)) {
                        $estado = 'falta';
                    }
                }
            }
            $estadosVisuales[$emp->emp_id] = $estado;
        }

        $motivos = \DB::table('motivos_tardanza')->where('estado', 'Activo')->get();

        return view('empresas.asistencia.index', compact('empleados', 'asistenciasHoy', 'estadosVisuales', 'motivos'));
    }

    public function verificarEstado($dni)
    {
        $empleado = empleado::where('emp_num_doc', $dni)->first();
        if (!$empleado) return response()->json(['status' => 'none']);

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $empleado->emp_id)->where('date', $today)->first();

        if (!$attendance) return response()->json(['status' => 'none']); // No ha marcado nada
        if ($attendance->check_in_1 && !$attendance->check_out_1) return response()->json(['status' => 'entrada']); // Trabajando B1
        if ($attendance->check_out_1 && !$attendance->check_in_2) return response()->json(['status' => 'none']); // Salió B1, debe entrar a B2
        if ($attendance->check_in_2 && !$attendance->check_out_2) return response()->json(['status' => 'entrada']); // Trabajando B2
        
        return response()->json(['status' => 'none']); // Turno completo
    }

    public function generarUrlSegura($dni)
    {
        $empleado = empleado::where('emp_num_doc', $dni)->first();
        if (!$empleado) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado']);
        }

        $emp_id = $empleado->emp_id;
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $horario = AsistenciaHorario::with('turno')->where('emp_id', $emp_id)->where('fecha', $today)->first();
        
        // Redirigir a Administrador en vez de bloquear por completo si es día de descanso
        if (!$horario || ($horario->turno && (strtoupper($horario->turno->codigo) == 'D' || strtoupper($horario->turno->codigo) == 'DESCANSO'))) {
            return response()->json([
                'success' => false, 
                'require_auth' => true,
                'message' => 'Hoy es tu día de descanso. Llama al administrador para autorizar tu ingreso especial.'
            ]);
        }
        
        $attendance = Attendance::where('user_id', $emp_id)->where('date', $today)->first();

        // --- LA MAGIA: Determinar qué acción EXACTA le toca a este QR ---
        $accion_esperada = 'check_in_1'; // Por defecto es la primera entrada
        if ($attendance) {
            if ($attendance->check_in_1 && is_null($attendance->check_out_1)) $accion_esperada = 'check_out_1';
            elseif ($attendance->check_out_1 && is_null($attendance->check_in_2)) $accion_esperada = 'check_in_2';
            elseif ($attendance->check_in_2 && is_null($attendance->check_out_2)) $accion_esperada = 'check_out_2';
            else $accion_esperada = 'completado';
        }

        if ($horario && $horario->turno) {
            $turno = $horario->turno;
            $tolerancia = $turno->tolerancia_minutos;

            // CASO A: Primera entrada
            if ($accion_esperada == 'check_in_1' && $turno->hora_entrada_1) {
                $esperada1 = Carbon::parse($today . ' ' . $turno->hora_entrada_1);
                $limite1 = $esperada1->copy()->addMinutes($tolerancia);

                if ($now->gt($limite1)) {
                    return response()->json([
                        'success' => false, 
                        'require_auth' => true,
                        'message' => 'Llegaste fuera de la tolerancia. Límite: ' . $limite1->format('H:i') . '. Llama al administrador para autorizar el ingreso.'
                    ]);
                }
            }

            // CASO B: Segunda entrada (Retorno refri)
            if ($accion_esperada == 'check_in_2' && $turno->hora_entrada_2) {
                $esperada2 = Carbon::parse($today . ' ' . $turno->hora_entrada_2);
                $limite2 = $esperada2->copy()->addMinutes($tolerancia);

                if ($now->gt($limite2)) {
                    return response()->json([
                        'success' => false, 
                        'require_auth' => true,
                        'message' => 'Retorno de refrigerio fuera de tolerancia. Límite: ' . $limite2->format('H:i') . '. Llama al administrador.'
                    ]);
                }
            }
        }

        // Incrustamos la "accion" directamente en el enlace firmado
        $urlSegura = URL::temporarySignedRoute('asistencia.registrar', Carbon::now()->addSeconds(60), [
            'dni' => $dni,
            'accion' => $accion_esperada
        ]);
        
        return response()->json(['success' => true, 'url' => $urlSegura]);
    }

    // --- NUEVO MÉTODO PARA AUTORIZAR CON CLAVE DE ADMIN ---
    public function autorizarTardanza(Request $request)
    {
        $dni = $request->input('dni');
        $usuario = $request->input('admin_user');
        $password = $request->input('admin_password');
        $motivo = $request->input('motivo');
        $hora_ingreso = $request->input('hora_ingreso'); // Se recibe la hora del modal

        // 1. Validar al administrador por correo o usuario (name)
        $admin = DB::table('users')->where('email', $usuario)->orWhere('name', $usuario)->first();
        
        // Laravel usa Hash para las contraseñas
        if (!$admin || !\Hash::check($password, $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Usuario o contraseña de administrador incorrectos.']);
        }

        // 2. Validar que tenga el Rol 2 (Administrador) según tu tabla role_user
        $isAdmin = DB::table('role_user')->where('user_IdUsuario', $admin->IdUsuario)->where('role_id', 2)->exists();
        if (!$isAdmin) {
            return response()->json(['success' => false, 'message' => 'Este usuario no tiene permisos de Administrador.']);
        }

        // 3. Evaluar y Registrar Asistencia Forzada (Control de Hora Modificada)
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $horaRegistro = $now;
        
        if (!empty($hora_ingreso)) {
            $horaRegistroInput = Carbon::parse($today . ' ' . $hora_ingreso . ':' . $now->format('s'));
            // Si el admin cambió la hora respecto a la real del sistema, guardamos la incidencia.
            if ($horaRegistroInput->format('H:i') !== $now->format('H:i')) {
                $motivo .= ' | [INCIDENCIA: Hora de ingreso modificada por sistema de ' . $now->format('H:i') . ' a ' . $hora_ingreso . ']';
            }
            $horaRegistro = $horaRegistroInput;
        }

        $empleado = empleado::where('emp_num_doc', $dni)->first();
        $emp_id = $empleado->emp_id;

        $attendance = Attendance::where('user_id', $emp_id)->where('date', $today)->first();
        $horario = AsistenciaHorario::with('turno')->where('emp_id', $emp_id)->where('fecha', $today)->first();
        $turno = $horario ? $horario->turno : null;
        $turno_id = $horario ? $horario->turno_id : null;
        
        if (!$attendance) {
            // Guardar Bloque 1
            $tardanza = 0;
            // Evaluamos solo si existe un turno válido (es decir, no es día de descanso)
            if ($turno && $turno->hora_entrada_1 && strtoupper($turno->codigo) !== 'D' && strtoupper($turno->codigo) !== 'DESCANSO') {
                $esperada = Carbon::parse($today . ' ' . $turno->hora_entrada_1);
                if ($horaRegistro->gt($esperada)) {
                    $tardanza = $esperada->diffInMinutes($horaRegistro);
                }
            }

            Attendance::create([
                'user_id' => $emp_id,
                'turno_id' => $turno_id,
                'check_in_1' => $horaRegistro,
                'date' => $today,
                'tardanza_minutos' => $tardanza,
                'autorizado_por' => $admin->name,
                'motivo_tardanza' => $motivo
            ]);
        } else {
            // Guardar Bloque 2
            $tardanza = 0;
            if ($turno && $turno->hora_entrada_2 && strtoupper($turno->codigo) !== 'D' && strtoupper($turno->codigo) !== 'DESCANSO') {
                $esperada = Carbon::parse($today . ' ' . $turno->hora_entrada_2);
                if ($horaRegistro->gt($esperada)) {
                    $tardanza = $esperada->diffInMinutes($horaRegistro);
                }
            }
            
            $motivoActualizado = $attendance->motivo_tardanza ? $attendance->motivo_tardanza . ' | B2: ' . $motivo : $motivo;

            $attendance->update([
                'check_in_2' => $horaRegistro,
                'tardanza_minutos' => $attendance->tardanza_minutos + $tardanza,
                'autorizado_por' => $admin->name,
                'motivo_tardanza' => $motivoActualizado
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Ingreso autorizado correctamente por ' . $admin->name
        ]);
    }

    // --- NUEVO MOTOR DE MARCACIÓN POR BLOQUES ---
    public function registrarCelular(Request $request, $dni)
    {
        // 1. Validar Firma del QR
        if (! $request->hasValidSignature()) {
            return view('empresas.asistencia.mensaje_celular', [
                'nombre_completo' => 'Control de Seguridad',
                'mensaje' => 'El código QR ha expirado o es inválido. Refresca la pantalla.',
                'tipo' => 'danger'
            ]);
        }

        // --- BLOQUEO ANTIFRAUDE: VERIFICAR SI EL CELULAR YA ESTÁ VINCULADO ---
        $dispositivo_vinculado = $request->cookie('dispositivo_empleado');
        
        if ($dispositivo_vinculado && $dispositivo_vinculado !== $dni) {
            return view('empresas.asistencia.mensaje_celular', [
                'nombre_completo' => '🚫 ALERTA ANTIFRAUDE',
                'mensaje' => 'Este celular ya fue utilizado para registrar la asistencia de otro trabajador. El sistema no permite marcar por compañeros.',
                'tipo' => 'danger'
            ]);
        }
        // ----------------------------------------------------------------------

        // 2. Extraer la acción que el QR pretendía hacer
        $accion_qr = $request->query('accion');

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 3. Buscar Empleado
        $empleado = empleado::where('emp_num_doc', $dni)->first();
        if (!$empleado) return response("Empleado no encontrado.", 404);

        try {
            // 4. Buscar Asistencia de hoy
            $attendance = Attendance::where('user_id', $empleado->emp_id)->where('date', $today)->first();
            $horario = AsistenciaHorario::with('turno')->where('emp_id', $empleado->emp_id)->where('fecha', $today)->first();
            $turno = $horario ? $horario->turno : null;

            // --- ESCUDO ANTI RECARGAS DE PESTAÑA ---
            $estado_real_bd = 'check_in_1';
            if ($attendance) {
                if ($attendance->check_in_1 && is_null($attendance->check_out_1)) $estado_real_bd = 'check_out_1';
                elseif ($attendance->check_out_1 && is_null($attendance->check_in_2)) $estado_real_bd = 'check_in_2';
                elseif ($attendance->check_in_2 && is_null($attendance->check_out_2)) $estado_real_bd = 'check_out_2';
                else $estado_real_bd = 'completado';
            }

            if ($accion_qr !== $estado_real_bd) {
                return view('empresas.asistencia.mensaje_celular', [
                    'nombre_completo' => $empleado->emp_nom,
                    'mensaje' => 'Este registro ya fue procesado correctamente. Puedes cerrar esta pestaña de forma segura.',
                    'tipo' => 'warning'
                ]);
            }
            // ---------------------------------------

            $mensaje = "";
            $tipo = "success";

            // ESTADO 1: ENTRADA 1
            if ($accion_qr === 'check_in_1') {
                $tardanza = 0;
                if ($turno && $turno->hora_entrada_1) {
                    $esperada = Carbon::parse($today . ' ' . $turno->hora_entrada_1);
                    if ($now->gt($esperada->copy()->addMinutes($turno->tolerancia_minutos ?? 15))) {
                        $tardanza = $esperada->diffInMinutes($now);
                    }
                }
                
                Attendance::create([
                    'user_id' => $empleado->emp_id,
                    'turno_id' => $horario ? $horario->turno_id : null,
                    'check_in_1' => $now,
                    'date' => $today,
                    'tardanza_minutos' => $tardanza
                ]);
                $mensaje = '¡Bienvenido ' . $empleado->emp_nom . '! Entrada registrada a las ' . $now->format('H:i:s');
            }

            // ESTADO 2: SALIDA 1 (Refrigerio)
            elseif ($accion_qr === 'check_out_1') {
                $attendance->update(['check_out_1' => $now]);
                $mensaje = '¡Hasta luego! Salida de refrigerio registrada a las ' . $now->format('H:i:s');
                $tipo = 'info';
            }

            // ESTADO 3: ENTRADA 2 (Retorno)
            elseif ($accion_qr === 'check_in_2') {
                $tardanza_adicional = 0;
                if ($turno && $turno->hora_entrada_2) {
                    $esperada = Carbon::parse($today . ' ' . $turno->hora_entrada_2);
                    if ($now->gt($esperada->copy()->addMinutes($turno->tolerancia_minutos ?? 15))) {
                        $tardanza_adicional = $esperada->diffInMinutes($now);
                    }
                }
                $attendance->update([
                    'check_in_2' => $now,
                    'tardanza_minutos' => $attendance->tardanza_minutos + $tardanza_adicional
                ]);
                $mensaje = '¡Bienvenido de nuevo! Retorno registrado a las ' . $now->format('H:i:s');
            }

            // ESTADO 4: SALIDA 2 (Fin de día)
            elseif ($accion_qr === 'check_out_2') {
                $attendance->update(['check_out_2' => $now]);
                $mensaje = '¡Buen trabajo! Jornada finalizada a las ' . $now->format('H:i:s');
                $tipo = 'info';
            }

            // ESTADO 5: Ya marcó todo
            else {
                return view('empresas.asistencia.mensaje_celular', [
                    'nombre_completo' => $empleado->emp_nom,
                    'mensaje' => 'Ya completaste todos tus registros de hoy.',
                    'tipo' => 'warning'
                ]);
            }

            // --- CASAR EL CELULAR CON ESTE TRABAJADOR POR 1 AÑO ---
            cookie()->queue('dispositivo_empleado', $dni, 525600);
            // ------------------------------------------------------

            return view('empresas.asistencia.mensaje_celular', [
                'nombre_completo' => $empleado->emp_nom,
                'mensaje' => $mensaje,
                'tipo' => $tipo
            ]);

        } catch (\Exception $e) {
            return view('empresas.asistencia.mensaje_celular', [
                'nombre_completo' => 'Error del Sistema',
                'mensaje' => 'No se pudo guardar el registro: ' . $e->getMessage(),
                'tipo' => 'danger'
            ]);
        }
    }

    public function registrarLectorFisico($dni)
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $now = \Carbon\Carbon::now();

        $empleado = empleado::where('emp_num_doc', $dni)->first();
        if (!$empleado) {
            return response()->json(['success' => false, 'message' => 'El DNI escaneado no pertenece a ningún colaborador.']);
        }

        try {
            $attendance = Attendance::where('user_id', $empleado->emp_id)->where('date', $today)->first();
            $horario = AsistenciaHorario::with('turno')->where('emp_id', $empleado->emp_id)->where('fecha', $today)->first();
            $turno = $horario ? $horario->turno : null;

            // Redirigir a Administrador en vez de bloquear por completo si es día de descanso
            if (!$horario || ($turno && (strtoupper($turno->codigo) == 'D' || strtoupper($turno->codigo) == 'DESCANSO'))) {
                return response()->json([
                    'success' => false, 
                    'require_auth' => true, 
                    'message' => 'Hoy es tu día de descanso. Llama al administrador para autorizar tu ingreso.',
                    'dni' => $dni
                ]);
            }

            // Determinar qué bloque le toca marcar
            $estado_real_bd = 'check_in_1';
            if ($attendance) {
                if ($attendance->check_in_1 && is_null($attendance->check_out_1)) $estado_real_bd = 'check_out_1';
                elseif ($attendance->check_out_1 && is_null($attendance->check_in_2)) $estado_real_bd = 'check_in_2';
                elseif ($attendance->check_in_2 && is_null($attendance->check_out_2)) $estado_real_bd = 'check_out_2';
                else $estado_real_bd = 'completado';
            }

            if ($estado_real_bd === 'completado') {
                return response()->json(['success' => false, 'message' => 'Ya completaste todos tus registros de hoy.']);
            }

            $mensaje = "";
            $tolerancia = $turno ? $turno->tolerancia_minutos : 15;

            // ESTADO 1: ENTRADA 1
            if ($estado_real_bd === 'check_in_1') {
                if ($turno && $turno->hora_entrada_1) {
                    $esperada = \Carbon\Carbon::parse($today . ' ' . $turno->hora_entrada_1);
                    if ($now->gt($esperada->copy()->addMinutes($tolerancia))) {
                        return response()->json([
                            'success' => false,
                            'require_auth' => true,
                            'message' => 'Llegaste fuera de tolerancia. Llama al administrador.',
                            'dni' => $dni
                        ]);
                    }
                }
                Attendance::create([
                    'user_id' => $empleado->emp_id,
                    'turno_id' => $horario ? $horario->turno_id : null,
                    'check_in_1' => $now,
                    'date' => $today,
                    'tardanza_minutos' => 0
                ]);
                $mensaje = '¡Entrada registrada a las ' . $now->format('H:i:s') . '!';
            }
            // ESTADO 2: SALIDA 1
            elseif ($estado_real_bd === 'check_out_1') {
                $attendance->update(['check_out_1' => $now]);
                $mensaje = '¡Salida de refrigerio a las ' . $now->format('H:i:s') . '!';
            }
            // ESTADO 3: ENTRADA 2
            elseif ($estado_real_bd === 'check_in_2') {
                 if ($turno && $turno->hora_entrada_2) {
                    $esperada = \Carbon\Carbon::parse($today . ' ' . $turno->hora_entrada_2);
                    if ($now->gt($esperada->copy()->addMinutes($tolerancia))) {
                        return response()->json([
                            'success' => false,
                            'require_auth' => true,
                            'message' => 'Retorno fuera de tolerancia. Llama al administrador.',
                            'dni' => $dni
                        ]);
                    }
                }
                $attendance->update(['check_in_2' => $now]);
                $mensaje = '¡Retorno registrado a las ' . $now->format('H:i:s') . '!';
            }
            // ESTADO 4: SALIDA 2
            elseif ($estado_real_bd === 'check_out_2') {
                $attendance->update(['check_out_2' => $now]);
                $mensaje = '¡Jornada finalizada a las ' . $now->format('H:i:s') . '!';
            }

            return response()->json(['success' => true, 'message' => $mensaje, 'empleado' => $empleado->emp_nom]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error de sistema: ' . $e->getMessage()]);
        }
    }

    // El método register para APIs internas o pruebas
    public function register(Request $request)
    {
        $dni_escaneado = $request->input('qr_code'); 
        return $this->registrarCelular($request, $dni_escaneado);
    }

    public function turnosEdit($id)
    {
        $turno = TurnoAsistencia::findOrFail($id);
        return view('empresas.asistencia.turnos_edit', compact('turno'));
    }

    public function turnosUpdate(Request $request, $id)
    {
        if (!$this->validarDuracion($request)) {
            return back()->with('error', '¡Error! El turno editado debe sumar al menos 8 horas de trabajo.');
        }

        $turno = TurnoAsistencia::findOrFail($id);
        $turno->update([
            'codigo' => strtoupper($request->codigo),
            'descripcion' => $request->descripcion,
            'hora_entrada_1' => $request->hora_entrada_1,
            'hora_salida_1' => $request->hora_salida_1,
            'hora_entrada_2' => $request->hora_entrada_2,
            'hora_salida_2' => $request->hora_salida_2,
            'tolerancia_minutos' => $request->tolerancia_minutos
        ]);

        return redirect()->route('asistencia.turnos')->with('success', '¡Turno actualizado correctamente!');
    }

    public function reporte(Request $request)
    {
        $empleados = empleado::where('est_cod', '1')->get();
        $fecha_inicio = $request->input('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $emp_id = $request->input('emp_id', 'todos');
        $action = $request->input('action', 'ver'); 

        $query = DB::table('attendances')
                    ->join('empleado', 'attendances.user_id', '=', 'empleado.emp_id')
                    ->select('attendances.*', 'empleado.emp_nom', 'empleado.emp_ape_pat', 'empleado.emp_num_doc');

        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('attendances.date', [$fecha_inicio, $fecha_fin]);
        }

        if ($emp_id !== 'todos') {
            $query->where('attendances.user_id', $emp_id);
        }

        // CORREGIDO: Ahora ordena por el nuevo campo check_in_1
        $asistencias = $query->orderBy('attendances.date', 'desc')
                             ->orderBy('attendances.check_in_1', 'desc')
                             ->get();

        if ($action == 'excel') return view('empresas.asistencia.excel', compact('asistencias', 'fecha_inicio', 'fecha_fin'));
        if ($action == 'ticket') return view('empresas.asistencia.ticket', compact('asistencias', 'fecha_inicio', 'fecha_fin'));

        return view('empresas.asistencia.reporte', compact('asistencias', 'empleados', 'fecha_inicio', 'fecha_fin', 'emp_id'));
    }

    public function reporteTareo(Request $request)
    {
        $action = $request->input('action', 'ver');

        $hoy = \Carbon\Carbon::now();
        $def_inicio = $hoy->copy()->startOfMonth()->format('Y-m-d');
        $def_fin = $hoy->copy()->endOfMonth()->format('Y-m-d');

        $fecha_inicio = $request->input('fecha_inicio', $def_inicio);
        $fecha_fin = $request->input('fecha_fin', $def_fin);
        $emp_id = $request->input('emp_id', 'todos');

        $empresa = \Illuminate\Support\Facades\DB::table('empresa')->first();
        $leyendas = \MasterSoft\Attendance::getLeyendas();

        $fechas = [];
        $current = \Carbon\Carbon::parse($fecha_inicio);
        $end = \Carbon\Carbon::parse($fecha_fin);
        $dias_espanol = ['Sunday'=>'D', 'Monday'=>'L', 'Tuesday'=>'M', 'Wednesday'=>'M', 'Thursday'=>'J', 'Friday'=>'V', 'Saturday'=>'S'];
        
        while ($current->lte($end)) {
            $fechas[] = [
                'fecha_sql' => $current->format('Y-m-d'),
                'dia_numero' => $current->format('d'),
                'dia_letra' => $dias_espanol[$current->format('l')]
            ];
            $current->addDay();
        }

        $empleadosQuery = empleado::where('asistencia', '1')->orderBy('emp_nom', 'asc');
        if ($emp_id !== 'todos') $empleadosQuery->where('emp_id', $emp_id);
        $empleados = $empleadosQuery->get();
        
        $todos_empleados = empleado::where('asistencia', '1')->orderBy('emp_nom', 'asc')->get();

        $asistenciasRaw = Attendance::whereBetween('date', [$fecha_inicio, $fecha_fin])->get();
        $horariosRaw = AsistenciaHorario::with('turno')->whereBetween('fecha', [$fecha_inicio, $fecha_fin])->get();

        // ---> LA MAGIA DE LOS FERIADOS (MOTOR AUTOMÁTICO HÍBRIDO) <---
        $añoActual = \Carbon\Carbon::parse($fecha_inicio)->year;

        $feriadosFijos = [
            "$añoActual-01-01", 
            "$añoActual-05-01", 
            "$añoActual-06-29", 
            "$añoActual-07-28", 
            "$añoActual-07-29", 
            "$añoActual-08-06", 
            "$añoActual-08-30", 
            "$añoActual-10-08", 
            "$añoActual-11-01", 
            "$añoActual-12-08", 
            "$añoActual-12-09", 
            "$añoActual-12-25", 
        ];

        // Feriados Móviles (Semana Santa calculada por PHP)
        $diasPascua = easter_days($añoActual);
        $fechaPascua = \Carbon\Carbon::createFromDate($añoActual, 3, 21)->addDays($diasPascua);
        $juevesSanto = $fechaPascua->copy()->subDays(3)->format('Y-m-d');
        $viernesSanto = $fechaPascua->copy()->subDays(2)->format('Y-m-d');
        $feriadosMoviles = [$juevesSanto, $viernesSanto];

        // Feriados Extras/Regionales en Base de Datos
        $feriadosBD = \DB::table('feriados')->whereBetween('fecha', [$fecha_inicio, $fecha_fin])->pluck('fecha')->toArray();

        // Unificar todo
        $feriadosArray = array_unique(array_merge($feriadosFijos, $feriadosMoviles, $feriadosBD));

        $asistencias = [];
        foreach ($asistenciasRaw as $a) $asistencias[$a->user_id][$a->date] = $a;

        $horarios = [];
        foreach ($horariosRaw as $h) $horarios[$h->emp_id][$h->fecha] = $h;

        $hoy_limite = \Carbon\Carbon::today();

        $matriz = [];
        foreach ($empleados as $emp) {
            $totales = array_fill_keys(array_keys($leyendas), 0);
            $fila_dias = [];

            foreach ($fechas as $f) {
                $fechaStr = $f['fecha_sql'];
                $asist = $asistencias[$emp->emp_id][$fechaStr] ?? null;
                $horario = $horarios[$emp->emp_id][$fechaStr] ?? null;
                
                $esFeriado = in_array($fechaStr, $feriadosArray);

                $letra = '';
                
                $esDescanso = false;
                if (!$horario || ($horario->turno && (strtoupper($horario->turno->codigo) == 'D' || strtoupper($horario->turno->codigo) == 'DESCANSO'))) {
                    $esDescanso = true;
                }

                if ($asist) {
                    if ($asist->tardanza_minutos > 0) {
                        $letra = 'T'; 
                    } else {
                        $letra = '1';
                    }
                } else {
                    if (\Carbon\Carbon::parse($fechaStr)->gt($hoy_limite)) {
                        $letra = '';
                    } else {
                        // ORDEN DE PRIORIDAD DE AUSENCIA
                        if ($esFeriado) {
                            $letra = 'F'; 
                        } elseif ($esDescanso) {
                            $letra = 'D'; 
                        } else {
                            $letra = '0'; 
                        }
                    }
                }

                $fila_dias[$fechaStr] = $letra;
                if ($letra !== '' && isset($totales[$letra])) {
                    $totales[$letra]++;
                }
            }

            $matriz[] = [
                'empleado' => $emp,
                'dias' => $fila_dias,
                'totales' => $totales
            ];
        }

        if ($action == 'excel') {
            header("Content-Type: application/xls");
            header("Content-Disposition: attachment; filename=Tareo_" . $fecha_inicio . "_al_" . $fecha_fin . ".xls");
            return view('empresas.asistencia.tareo_excel', compact('matriz', 'fechas', 'fecha_inicio', 'fecha_fin', 'todos_empleados', 'empresa'));
        }
        
        if ($action == 'pdf') {
            return view('empresas.asistencia.tareo_pdf', compact('matriz', 'fechas', 'fecha_inicio', 'fecha_fin', 'todos_empleados', 'empresa'));
        }

        return view('empresas.asistencia.tareo', compact('matriz', 'fechas', 'fecha_inicio', 'fecha_fin', 'emp_id', 'todos_empleados', 'empresa'));
    }

    public function asignarHorarios(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfWeek()->toDateString());
        $semanaAnterior = Carbon::parse($fechaInicio)->subWeek()->toDateString();
        $semanaSiguiente = Carbon::parse($fechaInicio)->addWeek()->toDateString();
        $action = $request->input('action', 'ver'); 

        $empleados = empleado::where('asistencia', 1)
                     ->orderBy('emp_nom', 'asc')
                     ->get();
        $turnos = TurnoAsistencia::all();

        $diasES = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
        ];

        $fechas = [];
        $currentDate = Carbon::parse($fechaInicio);
        for ($i = 0; $i < 7; $i++) {
            $diaIngles = $currentDate->copy()->addDays($i)->format('l');
            $fechas[] = [
                'sql' => $currentDate->copy()->addDays($i)->toDateString(),
                'vista' => $currentDate->copy()->addDays($i)->format('d/m'),
                'dia_nombre' => $diasES[$diaIngles]
            ];
        }

        $fechaFin = $fechas[6]['sql'];
        $horariosAsignados = AsistenciaHorario::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        
        $matriz = [];
        foreach ($horariosAsignados as $ha) {
            $matriz[$ha->emp_id][$ha->fecha] = $ha->turno_id;
        }

        if ($action == 'excel') return view('empresas.asistencia.horarios_excel', compact('empleados', 'turnos', 'fechas', 'fechaInicio', 'matriz'));
        if ($action == 'pdf') return view('empresas.asistencia.horarios_pdf', compact('empleados', 'turnos', 'fechas', 'fechaInicio', 'matriz'));

        return view('empresas.asistencia.horarios', compact('empleados', 'turnos', 'fechas', 'fechaInicio', 'semanaAnterior', 'semanaSiguiente', 'matriz'));
    }

    public function turnosIndex()
    {
        $turnos = TurnoAsistencia::all();
        return view('empresas.asistencia.turnos', compact('turnos'));
    }

    private function validarDuracion(Request $request)
    {
        // Limpiamos el código: quitamos espacios y convertimos a mayúsculas
        $codigo = strtoupper(trim($request->codigo));
        $leyendas_especiales = ['V', 'DM', 'J', 'S', 'F', 'MD', 'R', 'T']; 
        
        // Si el código es una leyenda (ej: V, DM), NO exigimos horas
        if (in_array($codigo, $leyendas_especiales)) {
            return true; 
        }

        // Si NO es leyenda, validamos que las horas existan
        if (empty($request->hora_entrada_1) || empty($request->hora_salida_1)) {
            return false;
        }

        // Validación normal de 8 horas (480 minutos)
        $start1 = \Carbon\Carbon::parse($request->hora_entrada_1);
        $end1 = \Carbon\Carbon::parse($request->hora_salida_1);
        
        if ($end1->lt($start1)) $end1->addDay();
        $totalMinutos = $start1->diffInMinutes($end1);

        if ($request->hora_entrada_2 && $request->hora_salida_2) {
            $start2 = \Carbon\Carbon::parse($request->hora_entrada_2);
            $end2 = \Carbon\Carbon::parse($request->hora_salida_2);
            if ($end2->lt($start2)) $end2->addDay();
            $totalMinutos += $start2->diffInMinutes($end2);
        }

        return $totalMinutos >= 480;
    }

    public function turnosStore(Request $request)
    {
        // 1. Validamos usando la función mejorada
        if (!$this->validarDuracion($request)) {
            return back()->with('error', '¡Error! El turno debe sumar al menos 8 horas de trabajo (a menos que sea una leyenda especial).');
        }

        // 2. Guardamos el turno
        TurnoAsistencia::create([
            'codigo' => strtoupper(trim($request->codigo)),
            'descripcion' => $request->descripcion,
            'hora_entrada_1' => $request->hora_entrada_1,
            'hora_salida_1' => $request->hora_salida_1,
            'hora_entrada_2' => $request->hora_entrada_2,
            'hora_salida_2' => $request->hora_salida_2,
            'tolerancia_minutos' => $request->tolerancia_minutos ?? 15
        ]);

        return back()->with('success', '¡Turno creado correctamente!');
    }

    public function turnosDestroy($id)
    {
        TurnoAsistencia::findOrFail($id)->delete();
        return back()->with('success', 'Turno eliminado del sistema.');
    }

    public function guardarHorarios(Request $request)
    {
        $horarios = $request->input('horario'); 
        $fecha_inicio = $request->input('fecha_inicio');

        if ($horarios) {
            foreach ($horarios as $emp_id => $fechas) {
                foreach ($fechas as $fecha => $turno_id) {
                    if (!empty($turno_id)) {
                        AsistenciaHorario::updateOrCreate(
                            ['emp_id' => $emp_id, 'fecha' => $fecha],
                            ['turno_id' => $turno_id]
                        );
                    } else {
                        AsistenciaHorario::where('emp_id', $emp_id)->where('fecha', $fecha)->delete();
                    }
                }
            }
        }

        return redirect()->route('asistencia.horarios', ['fecha_inicio' => $fecha_inicio])
                         ->with('success', '¡Horarios asignados correctamente!');
    }

    public function configurarIpIndex()
    {
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', 1)->first();
        return view('empresas.asistencia.configurar_ip', compact('negocio'));
    }

    public function configurarIpUpdate(Request $request)
    {
        DB::table('empresa_negocios')
            ->where('id_empresa_negocio', 1)
            ->update(['ip_asistencia' => trim($request->input('ip_asistencia'))]);

        return redirect()->route('asistencia.configurar_ip')->with('success', '¡IP de seguridad actualizada con éxito!');
    }

    public function reporteDetallado(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', Carbon::today()->toDateString());
        $fecha_fin = $request->input('fecha_fin', Carbon::today()->toDateString());
        $emp_id = $request->input('emp_id', 'todos');
        $action = $request->input('action', 'ver'); // Detectamos qué botón presionó

        $query = \DB::table('attendances')
            ->join('empleado', 'attendances.user_id', '=', 'empleado.emp_id')
            ->leftJoin('turnos_asistencia', 'attendances.turno_id', '=', 'turnos_asistencia.id')
            ->select('attendances.*', 'empleado.emp_nom', 'empleado.emp_ape_pat', 'empleado.emp_num_doc',
                     'turnos_asistencia.codigo',
                     'turnos_asistencia.hora_entrada_1 as te1', 'turnos_asistencia.hora_salida_1 as ts1',
                     'turnos_asistencia.hora_entrada_2 as te2', 'turnos_asistencia.hora_salida_2 as ts2',
                     'turnos_asistencia.tolerancia_minutos');

        if ($emp_id !== 'todos') {
            $query->where('attendances.user_id', $emp_id);
        }
        
        $asistenciasRaw = $query->whereBetween('attendances.date', [$fecha_inicio, $fecha_fin])
                                ->orderBy('attendances.date', 'desc')
                                ->get();
                                
        $asistencias = [];

        foreach ($asistenciasRaw as $asist) {
            $minutosLaborados = 0;
            $minutosTardanza = 0;
            $tolerancia = $asist->tolerancia_minutos ?? 0;

            if ($asist->check_in_1 && $asist->te1) {
                $entrada_real = Carbon::parse($asist->check_in_1);
                $entrada_teorica = Carbon::parse($asist->date . ' ' . $asist->te1);
                $salida_teorica = Carbon::parse($asist->date . ' ' . $asist->ts1);
                
                if ($entrada_real->gt($entrada_teorica->copy()->addMinutes($tolerancia))) {
                    $minutosTardanza += $entrada_teorica->diffInMinutes($entrada_real);
                    $inicio_computado = $entrada_real;
                } else {
                    $inicio_computado = $entrada_teorica;
                }

                if ($asist->check_out_1) {
                    $salida_real = Carbon::parse($asist->check_out_1);
                    $fin_computado = $salida_real->lt($salida_teorica) ? $salida_real : $salida_teorica;
                    
                    if ($fin_computado->gt($inicio_computado)) {
                        $minutosLaborados += $inicio_computado->diffInMinutes($fin_computado);
                    }
                }
            }

            if ($asist->check_in_2 && $asist->te2) {
                $entrada_real_2 = Carbon::parse($asist->check_in_2);
                $entrada_teorica_2 = Carbon::parse($asist->date . ' ' . $asist->te2);
                $salida_teorica_2 = Carbon::parse($asist->date . ' ' . $asist->ts2);

                if ($entrada_real_2->gt($entrada_teorica_2->copy()->addMinutes($tolerancia))) {
                    $minutosTardanza += $entrada_teorica_2->diffInMinutes($entrada_real_2);
                    $inicio_computado_2 = $entrada_real_2;
                } else {
                    $inicio_computado_2 = $entrada_teorica_2;
                }

                if ($asist->check_out_2) {
                    $salida_real_2 = Carbon::parse($asist->check_out_2);
                    $fin_computado_2 = $salida_real_2->lt($salida_teorica_2) ? $salida_real_2 : $salida_teorica_2;
                    
                    if ($fin_computado_2->gt($inicio_computado_2)) {
                        $minutosLaborados += $inicio_computado_2->diffInMinutes($fin_computado_2);
                    }
                }
            }

            $minutosPresencialesTotales = 0;
            if ($asist->check_in_1 && $asist->check_out_1) {
                $minutosPresencialesTotales += Carbon::parse($asist->check_in_1)->diffInMinutes(Carbon::parse($asist->check_out_1));
            }
            if ($asist->check_in_2 && $asist->check_out_2) {
                $minutosPresencialesTotales += Carbon::parse($asist->check_in_2)->diffInMinutes(Carbon::parse($asist->check_out_2));
            }

            $minutosExtraLocal = 0;
            if ($minutosPresencialesTotales > 480) {
                $minutosExtraLocal = $minutosPresencialesTotales - 480;
            }

            $asist->tiempo_laborado = floor($minutosLaborados / 60) . 'h ' . ($minutosLaborados % 60) . 'min';
            $asist->tardanza_texto = $minutosTardanza > 0 ? floor($minutosTardanza / 60) . 'h ' . ($minutosTardanza % 60) . 'min' : '0 min';
            $asist->extra_local = $minutosExtraLocal > 0 ? floor($minutosExtraLocal / 60) . 'h ' . ($minutosExtraLocal % 60) . 'min' : '--';
            $asist->estado_jornada = ($minutosLaborados + $minutosTardanza >= 480) ? 'Conforme' : 'Incompleto';

            $asistencias[] = $asist;
        }

        $empleados = \DB::table('empleado')->where('est_cod', '1')->orderBy('emp_nom', 'asc')->get();

        // LÓGICA PARA EXPORTAR
        if ($action == 'excel') {
            return view('empresas.asistencia.excel_detallado', compact('asistencias', 'fecha_inicio', 'fecha_fin'));
        }
        if ($action == 'pdf') {
            return view('empresas.asistencia.pdf_detallado', compact('asistencias', 'fecha_inicio', 'fecha_fin'));
        }

        return view('empresas.asistencia.reporte_detallado', compact('asistencias', 'empleados', 'fecha_inicio', 'fecha_fin', 'emp_id'));
    }

    public function motivosIndex()
    {
        $motivos = \DB::table('motivos_tardanza')->orderBy('descripcion', 'asc')->get();
        return view('empresas.asistencia.motivos', compact('motivos'));
    }

    public function motivosStore(Request $request)
    {
        \DB::table('motivos_tardanza')->insert([
            'descripcion' => strtoupper($request->input('descripcion')),
            'estado' => 'Activo',
            'created_at' => \Carbon\Carbon::now()
        ]);
        return back()->with('success', 'Motivo agregado correctamente.');
    }

    public function motivosDestroy($id)
    {
        \DB::table('motivos_tardanza')->where('id', $id)->delete();
        return back()->with('success', 'Motivo eliminado.');
    }

    public function motivosUpdate(Request $request, $id)
    {
        \DB::table('motivos_tardanza')->where('id', $id)->update([
            'descripcion' => strtoupper($request->input('descripcion')),
            'updated_at' => \Carbon\Carbon::now()
        ]);
        
        return back()->with('success', 'Motivo actualizado correctamente.');
    }
}