<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Registrar Producto</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Almacen</a></li>
                <li class="breadcrumb-item"><a href="/almacen/productos" class="button-link">Productos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registrar</li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Venta de Producto</h4>

                <div class="card-title-desc">
                    <form class="">

                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Codigo</label>
                            <div class="col-lg-2">
                                <input type="text" placeholder="Codigo" class="form-control text-center" id="input_codigo_producto" name="input_codigo_producto" disabled="true" value="">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Descripcion</label>
                            <div class="col-lg-9">
                                <input type="text" placeholder="Descripcion del Producto" class="form-control" id="input_descripcion_producto" name="input_descripcion_producto" required="true" value="">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Cod SUNAT</label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control text-right" id="input_codsunat" name="input_codsunat" value="" />
                            </div>

                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Precio Venta</label>
                            <div class="col-lg-2">
                                <input type="text" class="form-control text-right" id="input_precio_producto" name="input_precio_producto" value="" required/>
                            </div>

                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Costo</label>
                            <div class="col-lg-2">
                                <input type="text" class="form-control text-right" id="input_costo_producto" name="input_costo_producto" value="" required/>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-lg-2 control-label">Afecto ISCBP</label>
                            <div class="col-lg-2">
                                <input type="checkbox" class="i-checks"  id="input_afecto" name="input_afecto" value="1"/>
                                <input type="hidden" id="action" name="action" value="" />
                                <input type="hidden" id="hidden_idproducto" name="hidden_idproducto" value="" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-footer">
                <button type="button" id="btn_finalizar_pedido" class="btn btn-lg btn-primary"><i class="fa fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>