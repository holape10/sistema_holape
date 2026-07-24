@extends('layouts.empresas')

@section('contenido')
<div class="container mt-4">
    <h2>Módulo Contable: Centralizar Compras</h2>
    <p class="text-muted">Lista de adquisiciones y facturas de proveedores registradas en el sistema.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Serie / Doc</th>
                        <th>Proveedor RUC</th>
                        <th>Fecha Compra</th>
                        <th>Total</th>
                        <th class="text-center">Acción Contable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compras as $c)
                    <tr>
                        <td>{{ $c->com_cab_id }}</td>
                        <td>{{ $c->com_doc_ser }}</td>
                        <td>{{ $c->prov_num }}</td>
                        <td>{{ date('d/m/Y', strtotime($c->com_fec)) }}</td>
                        <td><strong>{{ $c->mon_id ?? 'PEN' }} {{ number_format($c->total_com, 2) }}</strong></td>
                        <td class="text-center">
                            <form action="{{ route('compras.centralizar', $c->com_cab_id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa fa-cog"></i> Centralizar Compra
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection