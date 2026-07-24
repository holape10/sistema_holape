<!--<style>
    /* ========== MODAL STYLES ========== */
    .modal-slide-in-right {
        animation: slideInRight 0.4s ease-out;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
        padding: 20px 25px;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header .modal-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        flex: 1;
        text-align: center;
    }

    .modal-header .close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: white;
        opacity: 0.8;
        border: none;
        background: none;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0;
        width: auto;
        height: auto;
    }

    .modal-header .close:hover {
        opacity: 1;
        transform: translateY(-50%) scale(1.2);
    }

    .modal-body {
        padding: 30px 25px;
        background-color: #f9fafb;
    }

    .modal-footer {
        background: linear-gradient(135deg, #ECF0F1 0%, #D5DBDB 100%);
        padding: 15px 25px;
        border: none;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    /* ========== FORM STYLES ========== */
    .form-group-sm {
        margin-bottom: 20px;
    }

    .form-group-sm label {
        font-weight: 700;
        font-size: 12px;
        color: #2c3e50;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }

    .form-control {
        border-radius: 6px;
        border: 2px solid #ecf0f1;
        padding: 10px 15px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
        background-color: white;
    }

    .form-control:focus {
        border-color: #3498DB;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }

    .form-control option {
        padding: 10px;
        background-color: white;
        color: #2c3e50;
    }

    /* ========== BUTTON STYLES ========== */
    .btn {
        border-radius: 6px;
        font-weight: 700;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        padding: 10px 20px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn-primary {
        background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2980B9 0%, #1f618d 100%);
    }

    .btn-danger {
        background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #C0392B 0%, #a93226 100%);
    }

    .btn-sm {
        padding: 8px 12px;
        font-size: 11px;
    }

    /* ========== ANIMACIONES ========== */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .modal-header .modal-title {
            font-size: 13px;
        }

        .modal-body {
            padding: 20px 15px;
        }

        .modal-footer {
            padding: 12px 15px;
        }

        .form-group-sm {
            margin-bottom: 15px;
        }

        .btn {
            padding: 8px 15px;
            font-size: 11px;
        }
    }

    @media (max-width: 576px) {
        .modal-body {
            padding: 15px 12px;
        }

        .form-group-sm label {
            font-size: 11px;
        }

        .form-control {
            padding: 8px 12px;
            font-size: 12px;
        }

        .modal-footer {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
            padding: 10px 15px;
        }
    }
</style>-->

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-aperturar">
    {!!Form::open(array('url'=>'/aperturarturno','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'formturno','name'=>'formturno'))!!}
    {{Form::token()}}
    
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- HEADER MEJORADO -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-unlock-alt"></i> Aperturar Turno Caja
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- BODY MEJORADO -->
            <div class="modal-body">
                <div class="row">
                    <!-- MONTO APERTURA -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="txtMonto">
                                <i class="fa fa-dollar"></i> Monto Apertura
                            </label>
                            <input type="number" 
                                   name="txtMonto" 
                                   id="txtMonto"
                                   step="0.01" 
                                   class="form-control" 
                                   value="0" 
                                   min="0"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>

                    <!-- SUCURSALES -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="form-group form-group-sm">
                            <label for="cmbSucursal">
                                <i class="fa fa-building"></i> Sucursal
                            </label>
                            <select class="form-control" 
                                    name="cmbSucursal" 
                                    id="cmbSucursal"
                                    required>
                                
                                @foreach($sucursales as $sucursal)
                                    <option value="{{$sucursal->id_empresa_negocio}}">
                                        {{$sucursal->tipo_negocio}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER MEJORADO -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i> Aperturar
                </button>
            </div>

        </div>
    </div>
    
    {{Form::Close()}}
</div>