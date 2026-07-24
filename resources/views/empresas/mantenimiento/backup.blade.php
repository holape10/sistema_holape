@extends('layouts.empresas')
@section('contenido')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Mantenimiento: Respaldo de Base de Datos</h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <p>Al hacer clic en el botón a continuación, se generará y descargará un archivo <strong>.sql</strong> con la estructura y todos los registros actuales de tu base de datos.</p>
            
            <form action="{{ route('backup.descargar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-download"></i> Generar y Descargar Backup
                </button>
            </form>
        </div>
    </div>
</div>
@endsection