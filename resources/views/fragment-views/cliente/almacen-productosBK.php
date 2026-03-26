<?php

require_once "app/models/Producto.php";

$c_producto = new Producto();
$c_producto->setIdEmpresa($_SESSION['id_empresa']);
$almacenProducto = 1;

?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Productos</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Almacen</a></li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">
                <div hidden class="dropdown">
                    <button class="btn btn-primary  dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="mdi mdi-cog me-2"></i> Settings
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Separated link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <strong>ALERTA DE ACTUALIZACION!</strong> a partir del año 2021, sunat exige el codigo SUNAT (Código de productos y servicios estándar de las Naciones Unidas - UNSPSC v14_0801, a que hace referencia el catálogo N° 25 del Anexo V de la Resolución de Superintendencia N° 340-2017/SUNAT y modificatorias.). Modifique el valor en Productos
                </div>
            </div>
        </div>
    </div>
    <!--col-md-6-->
</div>
<div id="conte-vue-modals">
    <input type="hidden" name="almacenId" id="almacenId" value="<?php echo $almacenProducto ?>">

    <div class="row">
        <div class="col-12">
            <div class="card"  style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="card-title">Lista de Productos</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <button onclick="descarFunccc()" class="btn btn-success"><i class="fa fa-file-excel"></i> Descargar Exel por busqueda</button>
                            <button data-bs-toggle="modal" data-bs-target="#importarModal" class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button>
                            <button data-bs-toggle="modal" data-bs-target="#modal-add-prod" class="btn btn-primary"><i class="fa fa-plus"></i> Agregar Producto</button>
                            <button class="btn btn-danger btnBorrar"><i class="fa fa-times"></i> Borrar</button>
                            <button hidden class="btn btn-danger" @click="agregarIds"><i class="fa fa-times"></i> Seleccionar Todos</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div hidden class="row">

                        <div class="form-group col-md-2" style="margin:  1rem 0;">
                            <label for="">Almacen</label>
                            <select name="almacenSelect" id="almacenSelect" class="form-control" @change="changeAlmacen($event)" v-model="almacen">
                                <option value="1">Almacen 1</option>
                                <option value="2">Tienda 1</option>
                            </select>
                        </div>
                    </div>


                   <div class="table-responsive">
                        <table id="datatable" class="table table-sm table-bordered text-center" cellspacing="0" width="100%">

                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Codigo</th>
                                    <th>Descripcion</th>
                                    <th>Cod SUNAT</th>
                                    <th>stock</th>
                                    <th>Costo</th>
                                    <th>Precios </th>
                                    <th>Ultima Venta</th>
                                    <th>Proveedor</th>
                                    <th>Aumentar Stock</th>
                                    <th>Editar</th>
                                    <th>Eliminar <input type="checkbox" class='btnSeleccionarTodos'></th>
                                    <th>Desactivado</th>
                                    <th>Detalles</th>
                                    <th>R.V.</th>
                                </tr>
                            </thead>
                            <tbody id='tbodyProductos'>
                                <?php
                                    /*$a_productos = $c_producto->verFilas($almacenProducto);

                                    foreach ($a_productos as $fila) {
                                        if ($fila['ultima_salida'] == '1000-01-01' || $fila['ultima_salida'] == '0000-00-00') {
                                            $label_estado = '<span class="label label-warning">Sin Movimiento</span>';
                                        } else {
                                            $label_estado = '<span class="label label-success">' . $fila['ultima_salida'] . '</span>';
                                        }
                                        ?>
                                    <tr>
                                        <td class="text-center"><?php echo $fila['id_producto'] ?></td>
                                        <td class="text-center"><?php echo $fila['codigo'] ?></td>
                                        <td><a href="javascript:abrirModalBarras(<?php echo $fila['id_producto'] ?>)"><?php echo $fila['descripcion'] ?></a></td>
                                        <td><?php echo $fila['codsunat'] ?></td>
                                        <td class="text-right"><?php echo $fila['cantidad'] ?></td>

                                        <td class="text-right"><?php echo number_format($fila['costo'], 2, ".", "") ?></td>
                                        <td class="text-right"> <button data-item="<?=$fila['id_producto']?>" class="btn-ver-precios btn btn-sm btn-info"> <i class="fas fa-eye"></i></button></td>
                                        <td class="text-center"><?php echo $label_estado ?></td>
                                        <td class="text-center"><?php echo $fila['razon_social'] ?></td>
                                        <td class="text-center">
                                            <button data-item="<?=$fila['id_producto']?>" class="btn-re-stock btn btn-sm btn-warning"> <i class="fas fa-sync"></i></button>
                                        </td>
                                        <td class="text-center">
                                            <button data-item="<?=$fila['id_producto']?>" class="btn-edt btn btn-sm btn-info"> <i class="fa fa-edit"></i></button>
                                        </td>
                                        <td>
                                            <input type="checkbox" class='btnCheckEliminar' data-id="<?=$fila['id_producto']?>">
                                        </td>
                                        <td class="text-center">
                                            <button data-item="<?=$fila['id_producto']?>" class="btn-reporte btn btn-sm btn-info"> <i class="fa fa-file"></i></button>
                                        </td>
                                    </tr>
                                <?php

                                }*/
                                ?>
                            </tbody>
                        </table>
                   </div>

                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="modal-precios" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Precios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form @submit.prevent="agregarPrecios">

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Precio x Mayor: </label>
                                <input v-model="edt.precio_unidad" id="precio_unidad" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Precio x Saco: </label>
                                <input v-model="edt.precio4" id="precio4" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Precio: </label>
                                <input v-model="edt.precio" id="precio1" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Credito 1: </label>
                                <input v-model="edt.precio2" id="precio2" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Credito 2: </label>
                                <input v-model="edt.precio3" id="precio3" class="form-control">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-add-prod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="agregarProd">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-8 mt-2">
                                <label>Descripción de producto</label>
                                <input v-model="reg.descripcicon" required type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Codigo</label>
                                <input v-model="reg.codigo" required type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Medida  </label>
                                <select  v-model="reg.medida" required class="form-control">
                                    <option>Kilos</option>
                                    <option>Cajas</option>
                                    <option>Sacos</option>
                                    <option>Unidad</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Presetaciones</label>
                                <select id="selcmunAdd" @change="cambioselec22"  class="form-select selectpickerpd" multiple>
                                    <option value="1">Unidad</option>
                                    <option value="2">Cajas</option>
                                    <option value="3">Bolsa</option>
                                    <option value="4">Sacos</option>
                                </select>

                            </div>

                            <div class="form-group col-md-4 mt-2">
                                <label>Medidas por presentacion</label>
                                <input v-model="reg.cnt_medida"  required value="0" type="text" class="form-control">
                                <small>Separe con comas las medidas</small>
                            </div>
                            <div class="form-group col-md-3 mt-2">
                                <label>Precio Venta</label>
                                <input v-model="reg.precio" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Costo</label>
                                <input v-model="reg.costo" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Cantidad</label>
                                <input v-model="reg.cantidad" @keypress="onlyNumber" required type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Cod. Sunat</label>
                                <input v-model="reg.codSunat" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Afecto ICBP</label>
                                <select v-model="reg.afecto" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Precio por Mayor</label>
                                <input v-model="reg.precioMayor" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Precio por Menor</label>
                                <input v-model="reg.precioMenor" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label><span class="rojo"></span>RUC: </label>
                                        <div class="input-group">
                                            <input v-model="reg.ruc" required @keypress="onlyNumber" @keyup="buscarProveedor('insert')" type="text" class="form-control inputSearch" maxlength="11">
                                            <div class="input-group-prepend">
                                                <button type="button" @click="consultarDocRUC" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Razon Social: </label>
                                        <input v-model="reg.razon" required type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-edt-prod" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="actualizarProd">
                    <div class="modal-body">
                        <div class="row">
                            <input v-model="edt.cod_prod" type="hidden" class="form-control">
                            <div class="form-group col-md-8 mt-2">
                                <label>Descripción de producto</label>
                                <input v-model="edt.descripcicon" required type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Codigo</label>
                                <input v-model="edt.codigo" required type="text" class="form-control">
                            </div>

                            <div class="form-group col-md-4 mt-2">
                                <label>Medida  </label>
                                <select  v-model="edt.medida" required class="form-control">
                                    <option>Kilos</option>
                                    <option>Caja</option>
                                    <option>Sacos</option>
                                    <option>Unidad</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Presetaciones</label>
                                <select id="selcmunEdt" @change="cambioselec"  class="form-select selectpickerpd" multiple>
                                    <option value="1">Unidad</option>
                                    <option value="2">Caja</option>
                                    <option value="3">Bolsa</option>
                                    <option value="4">Saco</option>
                                </select>

                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Medidas por presentacion</label>
                                <input v-model="edt.cnt_medida"  required value="0" type="text" class="form-control">
                                <small>Separe con comas las medidas</small>
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Precio Venta</label>
                                <input v-model="edt.precio" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Costo</label>
                                <input v-model="edt.costo" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Cod. Sunat</label>
                                <input v-model="edt.codSunat" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Afecto ICBP</label>
                                <select v-model="edt.afecto" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Si</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Usar Codigo Barra</label>

                                <div class="input-group mb-3">
                                    <select v-model="edt.usar_barra" class="form-control">
                                        <option value="0">No</option>
                                        <option value="1">Si</option>
                                    </select>
                                    <div v-if="edt.usar_barra=='1'" class="input-group-btn">
                                        <button @click="edtGenerarCodeBarra" type="button" class="btn btn-primary">Generar</button>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group col-md-4 mt-2">
                                <label>Precio por Mayor</label>
                                <input v-model="edt.precioMayor" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Precio por Menor</label>
                                <input v-model="edt.precioMenor" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Cantidad</label>
                                <input v-model="edt.cantidad" @keypress="onlyNumber" required value="0" type="text" class="form-control">
                            </div>
                            <div class="form-group col-md-12">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label><span class="rojo"></span>RUC: </label>
                                        <div class="input-group">
                                            <input v-model="edt.ruc" required @keypress="onlyNumber" @keyup="buscarProveedor('update')" type="text" class="form-control inputSearch" maxlength="11">
                                            <div class="input-group-prepend">
                                                <button type="button" @click="consultarDocRUC" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Razon Social: </label>
                                        <input v-model="edt.razon" required type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3 text-center">
                                <img id="barcode" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-restock" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="agregarStock">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input v-model="restock.cantidad" required type="text" class="form-control">
                            <small class="form-text text-muted">La cantidad ingresada se sumara a la cantidad actual</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Importar Productos con EXCEL</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form enctype='multipart/form-data'>
                        <div class="mb-3">
                            <p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar, no
                                modifique los campos en el archivo, <span class="fw-bold">click para
                                    descargar</span> <a href="<?=URL::to('/reporte/producto/guia')?>">plantilla.xlsx</a></p>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label">Importar Excel:</label>

                        </div>
                        <input id="file-import-exel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" type="file">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-lista-productos" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Lista de productos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered text-center">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cnt</th>
                                <th>Costo</th>
                                <th>Precio x Mayor</th>
                                <th>Precio x Saco</th>
                                <th>Precio</th>
                                <th>Credito 1</th>
                                <th>Credito 2</th>

                                <th>Cod.Sunat</th>
                                <th>Almacen</th>
                                <th>Codigo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item,index) in listaProd">
                                <td>{{item.descripcicon}}</td>
                                <td> {{item.cantidad}}</td>
                                <td>{{item.costo}}</td>
                                <td>{{item.precio_unidad}}</td>
                                <td>{{item.precio4}}</td>
                                <td>{{item.precio}}</td>
                                <td>{{item.precio2}}</td>
                                <td>{{item.precio3}}</td>
                                <td>{{item.codSunat}}</td>
                                <td>{{item.almacen}}</td>
                                <td>{{item.codigoProd}}</td>

                                <td><button @click="eliminarItemTablaPro(index)" class="btn-sm btn btn-danger"><i class="fa fa-times"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCodigoBarras" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Codigo de Barras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3 text-center">
                        <img id="idCodigoBarras">
                    </div>
                    <div class="mb-3">
                        <label  class="form-label">Escalar</label>
                        <select id="scalimg" class="form-control" >
                            <option value="1">NO</option>
                            <option value="2">SI</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" id="btnImprimir" onclick="imprimir()">Imprimir</button>
                        <button class="btn btn-primary" id="btnImprimir2" onclick="imprimir2()">Imprimir 2</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modal-prodEreport" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reporte De Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Año</label>
                    <select  id='anioreporEFG' class="form-control">
                        <?php
                        $anio = date("Y");
                        for ($i = 0; $i < 10; $i++) {
                            echo "<option value='$anio'>$anio</option>";
                            $anio--;
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mes</label>
                    <select id='mesreprEFG' class="form-control">
                        <?php
                        $contador = 1;
                        $meses = array('ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE');
                        foreach ($meses as $mes) {
                            echo "<option  " . ($contador == date('m') ? 'selected' : '') . " value='" . ($contador < 10 ? '0' . $contador : $contador) . "'>$mes</option>";
                            $contador++;
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Dia</label>
                    <input id='diareporEfghg' class="form-control">
                </div>

            </div>
            <div class="modal-footer">
                <button id="generarreporteProd" type="button" class="btn btn-primary">Generar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- <style>
    input[type=file].hidden {
        color: transparent;
    }
</style> -->
<script src="
https://cdn.jsdelivr.net/npm/@pokusew/escpos@3.0.8/dist/index.min.js
"></script>
<script>
    function descarFunccc(){
        window.open(_URL +
            `/reporte/producto/excel?texto=${$("#datatable_filter input").val()}`)
    }

    var codProdT=''
    async function printBarcode() {
        try {
            const printer = await EscPosPrinter.requestPrinter();

            // Conectar a la impresora
            await printer.connect();

            // Configurar el tamaño del ticket (50 mm x 25 mm)
            await printer.setPageFormat(50, 25);

            // Imprimir el título
            await printer.printText('Barcode Title\n');

            // Generar el código de barras utilizando JsBarcode
            const svgData = JsBarcode.generateSvg('123456789', {
                format: 'CODE128',
                displayValue: true,
            });

            // Imprimir el código de barras
            await printer.printImage(svgData);

            // Cortar el ticket
            await printer.cut();

            // Desconectar la impresora
            await printer.disconnect();
        } catch (error) {
            console.error(error);
        }
    }
    function imprimir2() {
        window.open(_URL+"/ge/bar/code2?code="+codeBarraTemps+"&nombre="+nombreBarraTemps+"&scal="+$("#scalimg").val(), "_blank");



    }
    function imprimir() {
        window.open(_URL+"/ge/bar/code?code="+codeBarraTemps+"&nombre="+nombreBarraTemps+"&scal="+$("#scalimg").val(), "_blank");

        /*   let printA4 = $(this).attr('href') */
        //printBarcode()
        /*let imgCodigo = $('#idCodigoBarras').attr('src');
        var myWindow = window.open("", "Image", "_blank");
        myWindow.document.write("<html><head><title></title></head><body style='width: 5cm; height: 2.5cm; padding: 0; margin: 0;'>");
        myWindow.document.write("<h3 style='font-size: 12px;text-align: center; margin: 0; padding: 0;'>"+nombreBarraTemps+"</h3>");
        myWindow.document.write("<img src='" + imgCodigo + "' style='width: 100%; height:   display: block; margin: 0 auto;'>");
        myWindow.document.write("</body></html>");
        myWindow.document.close();
        myWindow.focus();
        myWindow.print();
        myWindow.close();*/

       /* let imgCodigo = $('#idCodigoBarras').attr('src');
        let ticketContent = `
        <html>
        <head><title>Ticket de impresión</title></head>
        <body style="width: 5cm; height: 2.5cm; padding: 0; margin: 0;">
          <h3 style="font-size: 12px;text-align: center; margin: 0; padding: 0;">"+nombreBarraTemps+"</h3>
          <img src="${imgCodigo}" style="width: 100%; height: calc(100% - 1em); display: block; margin: 0 auto;">
        </body>
        </html>
      `;

        qz.websocket.connect().then(function() {
            return qz.printers.find("XP-350B"); // Nombre de la impresora XPRINTER XP-350B
        }).then(function(printer) {
            let config = qz.configs.create(printer);
            return qz.print(config, [{ type: 'html', format: 'plain', data: ticketContent }]);
        }).then(function() {
            qz.websocket.disconnect();
        }).catch(function(err) {
            console.error(err);
        });*/

    }

    function abrirModalBarras(e,n='') {
        e=e.trim();
        console.log(e);
        nombreBarraTemps=n
        codeBarraTemps=e
        /*  let  */
        //let codigoCompleto = e.toString().padStart(10, '0');
        JsBarcode("#idCodigoBarras", e);
        $('#modalCodigoBarras').modal('show')

    }
    var nombreBarraTemps=''
    var codeBarraTemps=''
    var datatable
    var almacenCod='<?php echo $_SESSION["sucursal"] ?>'
    $(document).ready(function() {


        const app = new Vue({
            el: "#conte-vue-modals",
            data: {
                almacen: <?php echo $_SESSION["sucursal"] ?>,
                t: 0,
                listaProd: [],
                restock: {
                    cod: '',
                    cantidad: '',
                },
                reg: {
                    medida: '',
                    descripcicon: "",
                    precio: '0',
                    costo: '0',
                    cantidad: '0',
                    codSunat: '',
                    afecto: '0',
                    ruc: '',
                    razon: '',
                    precioMayor: '',
                    precioMenor: '',
                    cnt_medida: '',
                    codigo: '',
                    presentaciones:[]
                },
                edt: {
                    cod_prod: '',
                    cnt_medida: '',
                    cod: '',
                    descripcicon: "",
                    precio: '0',
                    costo: '0',
                    codSunat: '',
                    afecto: '0',
                    usar_barra: '0',
                    ruc: '',
                    medida: '',
                    razon: '',
                    precioMayor: '',
                    precioMenor: '',
                    precio2: '',
                    precio3: '',
                    precio4: '',
                    precio_unidad: '',
                    codigo: '',
                    cantidad:'',
                    presentaciones:[]
                },
                listaIdsss: []
            },
            methods: {
                cambioselec22(evt){
                    this.reg.presentaciones = $(evt.target).val()

                },
                cambioselec(evt){
                    console.log($(evt.target).val())
                    this.edt.presentaciones = $(evt.target).val()

                },
                agregarIds() {
                    /*  console.log('nice'); */
                    this.t = 5
                    console.log(this.listaIdsss);
                    this.listaIdsss.push({
                        id: 20
                    })
                    console.log(this.listaIdsss);
                },
                agregarPrecios() {
                    const data = {
                        ...this.edt
                    }
                    _ajax("/ajs/data/producto/edt/precios", "POST", data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Actualizado")
                                    .then(function() {
                                        location.reload()
                                    })
                            } else {
                                alertAdvertencia("No se pudo actualizar")
                            }
                        }
                    )
                },
                changeAlmacen(event) {
                    /*    datatable.rows().remove().draw(true) */
                    /*     $('.btnSeleccionarTodos').prop('checked', false); */
                    /*   localStorage.removeItem('idChecks'); */

                    $('.btnSeleccionarTodos').prop('checked', false);
                    localStorage.removeItem('idChecks');
                    console.log(event.target.value)
                    /*    var data
                    datatable.rows().remove().draw(true)*/
                    datatable.destroy();
                    /*   return */
                    almacenCod = event.target.value

                    datatable = $("#datatable").DataTable({
                        order: [[0, 'desc']],
                        "processing": true,
                        "serverSide": true,
                        "sAjaxSource": _URL+"/ajs/server/sider/productos?almacenId="+almacenCod,
                        columnDefs:[
                            {
                                "targets": 2,
                                "render": function (data, type, row, meta) {
                                    console.log(row)
                                    return `<div><a href="javascript:abrirModalBarras('${row[1]}','${row[0]}')">${row[2]}</a> </div>`
                                    //return '<a href="javascript:abrirModalBarras(\''+row[1]+'\',\''+row[0]+'\')">'+row[2]+'</a>';
                                }
                            },{
                                "targets":6,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-ver-precios btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
                                }
                            },
                            {
                                "targets":6,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
                                }
                            },
                            {
                                "targets":9,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
                                }
                            },
                            {
                                "targets":10,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-edt btn btn-sm btn-info"><i class="fa fa-edit"></i></button>`;
                                }
                            },
                            {
                                "targets":11,
                                "render": function (data, type, row, meta) {
                                    return `<input type="checkbox" data-id="${row[0]}" class="btnCheckEliminar">`;
                                }
                            },
                            {
                                "targets":12,
                                "render": function (data, type, row, meta) {
                                    if(row[13]==1){
                                        return `<input type="checkbox" data-id="${row[0]}" class="btnCheckActivar">`;
                                    }else{
                                        return `<input type="checkbox" data-id="${row[0]}" class="btnCheckActivar" checked>`;
                                    }
                                }
                            },
                            {
                                "targets":13,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-reporte btn btn-sm btn-info"><i class="fa fa-file"></i></button>`;
                                }
                            },

                            {
                                "targets":14,
                                "render": function (data, type, row, meta) {
                                    return `<button data-item="${row[0]}" class="btn-reporte-prod btn btn-sm btn-success"><i class="fa fa-file-excel"></i></button>`;
                                }
                            },
                        ]
                    })
                },
                edtGenerarCodeBarra() {
                    JsBarcode("#barcode", this.edt.cod_prod);
                },
                agregarListaImport() {

                    if (this.listaProd.length > 0) {
                        _ajax("/ajs/data/producto/add/lista", "POST", {
                                lista: JSON.stringify(this.listaProd)
                            },
                            function(resp) {
                                console.log(resp);
                                if (resp.res) {
                                    alertExito("Agregado")
                                        .then(function() {
                                            location.reload()
                                        })
                                } else {
                                    alertAdvertencia("No se pudo Agregar")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("La lista esta vacia")
                    }
                },
                ChangeconsultarDocRUC() {
                    if (this.reg.ruc.length == 11) {
                        this.getInfoDoc2();
                    } else {
                        this.reg.ruc = ''
                    }
                },
                consultarDocRUC() {
                    if (this.reg.ruc.length == 11) {

                        this.getInfoDoc2();
                    } else if (this.edt.ruc.length == 11) {
                        this.getInfoDoc3();
                    } else {
                        alertAdvertencia("El RUC es de 11 dígitos")
                    }
                },
                getInfoDoc2() {
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/doc/cliente", "POST", {
                            doc: this.reg.ruc
                        },
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                app._data.reg.razon = resp.data.razon_social;
                                app._data.reg.direccion = resp.data.direccion;
                                app._data.reg.distrito = resp.data.distrito;
                                app._data.reg.provincia = resp.data.provincia;
                                app._data.reg.departamento = resp.data.departamento;
                                app._data.reg.ubigeo = resp.data.ubigeo;
                            } else {
                                alertAdvertencia("Documento no encontrado")
                            }
                        }
                    )
                },
                getInfoDoc3() {
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/doc/cliente", "POST", {
                            doc: this.edt.ruc
                        },
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                app._data.edt.razon = resp.data.razon_social;
                                app._data.edt.direccion = resp.data.direccion;
                                app._data.edt.distrito = resp.data.distrito;
                                app._data.edt.provincia = resp.data.provincia;
                                app._data.edt.departamento = resp.data.departamento;
                                app._data.edt.ubigeo = resp.data.ubigeo;
                            } else {
                                alertAdvertencia("Documento no encontrado")
                            }
                        }
                    )
                },
                eliminarItemTablaPro(index) {
                    this.listaProd.splice(index, 1)
                },
                agregarStock() {
                    const data = {
                        ...this.restock
                    }
                    _ajax("/ajs/data/producto/restock", "POST", data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Actualizado")
                                    .then(function() {
                                        location.reload()
                                    })
                            } else {
                                alertAdvertencia("No se pudo actualizar")
                            }
                        }
                    )
                },
                actualizarProd() {
                    const data = {
                        ...this.edt
                    }
                    data.presentaciones = data.presentaciones.join(',')
                    _ajax("/ajs/data/producto/edt", "POST", data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Actualizado")
                                    .then(function() {
                                        location.reload()
                                    })
                            } else {
                                alertAdvertencia("No se pudo actualizar")
                            }
                        }
                    )
                },
                agregarProd() {
                    const data = {
                        ...this.reg
                    }
                    data.presentaciones = data.presentaciones.join(",")

                    data.almacen = this.almacen
                    console.log(data);
                     /* s */
                    _ajax("/ajs/data/producto/add", "POST", data,
                        function(resp) {
                            console.log(resp);
                           /*  return */
                            if (resp.res) {
                                alertExito("Agregado")
                                    .then(function() {
                                        location.reload()
                                    })
                            } else {
                                alertAdvertencia("No se pudo agregar")
                            }
                        }
                    )
                },
                setInfo(data) {
                    $("#modal-edt-prod").modal("show");

                    this.edt.cod_prod = data.cod_barra
                    this.edt.usar_barra = data.usar_barra
                    this.edt.cod = data.id_producto
                    this.edt.descripcicon = data.descripcion
                    this.edt.precio = data.precio
                    this.edt.medida = data.medida
                    this.edt.costo = parseFloat(data.costo + "").toFixed(2)
                    this.edt.codSunat = data.codsunat
                    this.edt.afecto = data.iscbp
                    this.edt.precioMayor = data.precio_mayor
                    this.edt.precioMenor = data.precio_menor
                    this.edt.razon = data.razon_social
                    this.edt.ruc = data.ruc
                    this.edt.cnt_medida = data.cnt_presenta
                    this.edt.codigo = data.codigo
                    this.edt.cantidad = data.cantidad
                    this.edt.presentaciones = data.presentaciones?data.presentaciones.split(","):[];
                    console.log(this.edt.presentaciones)
                    $('#selcmunEdt').selectpicker("val",this.edt.presentaciones);

                },
                onlyNumber($event) {
                    //console.log($event.keyCode); //keyCodes value
                    let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                        $event.preventDefault();
                    }
                },
                buscarProveedor(tipo) {
                    let appendTo = $("#modal-add-prod");
                    if(tipo==='update') appendTo = $("#modal-edt-prod");
					console.log('geanmarco')
					$(".inputSearch").autocomplete({
						// source: _URL + "/proveedores/search",
                        source: _URL + "/ajs/asearch/provedor/data",
						minLength: 1,
						appendTo: appendTo,
						select: function (event, ui) {
							if (tipo === 'insert') {
								app._data.reg.razon = ui.item.datos;
								app._data.reg.ruc = ui.item.documento;

								app._data.edt.razon = '';
								app._data.edt.ruc = '';
							}
							if (tipo === 'update') {
								app._data.edt.razon = ui.item.datos;
								app._data.edt.ruc = ui.item.documento;

								app._data.reg.razon = '';
								app._data.reg.ruc = '';
							}
						},
						response: function (event, ui) {
						}
					});
					console.log('despues');
				}
            },
            mounted(){
                $('.selectpickerpd').selectpicker();
            }

        })
        /*  localStorage.removeItem('idChecks'); */
        datatable = $("#datatable").DataTable({
            order: [[0, 'desc']],
            "processing": true,
            "serverSide": true,
            "sAjaxSource": _URL+"/ajs/server/sider/productos?almacenId="+almacenCod,
            columnDefs:[
                {
                    "targets": 2,
                    "render": function (data, type, row, meta) {
                        return '<a href="javascript:abrirModalBarras(\''+row[1]+'\',\''+row[0]+'\')">'+row[2]+'</a>';
                    }
                },{
                    "targets":6,
                    "render": function (data, type, row, meta) {
                        return `<button data-item="${row[0]}" class="btn-ver-precios btn btn-sm btn-info"><i class="fas fa-eye"></i></button>`;
                    }
                },
                {
                    "targets":6,
                    "render": function (data, type, row, meta) {
                        return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
                    }
                },
                {
                    "targets":9,
                    "render": function (data, type, row, meta) {
                        return `<button data-item="${row[0]}" class="btn-re-stock btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>`;
                    }
                },
                {
                    "targets":10,
                    "render": function (data, type, row, meta) {
                        return `<button data-item="${row[0]}" class="btn-edt btn btn-sm btn-info"><i class="fa fa-edit"></i></button>`;
                    }
                },
                {
                    "targets":11,
                    "render": function (data, type, row, meta) {
                        return `<input type="checkbox" data-id="${row[0]}" class="btnCheckEliminar">`;
                    }
                },
                {
                    "targets":12,
                    "render": function (data, type, row, meta) {
                        if(row[13]==1){
                            return `<input type="checkbox" data-id="${row[0]}" class="btnCheckActivar">`;
                        }else{
                            return `<input type="checkbox" data-id="${row[0]}" class="btnCheckActivar" checked>`;
                        }
                    }
                },
                {
                    "targets":13,
                    "render": function (data, type, row, meta) {
                        return `<button data-item="${row[0]}" class="btn-reporte btn btn-sm btn-info"><i class="fa fa-file"></i></button>`;
                    }
                },

                {
                    "targets":14,
                    "render": function (data, type, row, meta) {
                        return "";
                        return `<button data-item="${row[0]}" class="btn-reporte-prod btn btn-sm btn-success"><i class="fa fa-file-excel"></i></button>`;
                    }
                },
            ]
        })

        $("#file-import-exel").change(function() {
            console.log("aaaaaaaa")
            if ($("#file-import-exel").val().length > 0) {
                var fd = new FormData();
                fd.append('file', $("#file-import-exel")[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: _URL + '/ajs/data/producto/add/exel',
                    data: fd,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function() {
                        console.log('inicio');
                        $("#loader-menor").show();
                    },
                    error: function(err) {
                        $("#loader-menor").hide();
                        console.log(err);
                    },
                    success: function(resp) {
                        $("#loader-menor").hide();
                        console.log(resp);
                        resp = JSON.parse(resp)
                        if (resp.res) {
                            var bloc = true;
                            var listaTemp = [];
                            resp.data.forEach(function(el) {
                                if (!bloc) {
                                    listaTemp.push({
                                        descripcicon: el[0],
                                        cantidad: el[1],
                                        precio: el[5],
                                        precio2: el[6],
                                        precio3: el[7],
                                        precio4: el[4],
                                        costo: el[2],
                                        codSunat: el[8],
                                        almacen: el[9],
                                        afecto: false,
                                        precio_unidad: el[3],
                                        codigoProd: el[10]
                                    })
                                }
                                bloc = false
                            })
                            app._data.listaProd = listaTemp
                            $("#importarModal").modal("hide")
                            $("#modal-lista-productos").modal("show")
                        } else {
                            alertAdvertencia("No se pudo subir el Archivo")
                        }
                        $("#file-import-exel").val("")

                    }
                })
            }
        })



        var arrayIdsOkUsar = []



        $("#datatable").on("click", ".btn-re-stock", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");
            app._data.restock.cod = cod
            app._data.restock.cantidad = ''
            $("#modal-restock").modal("show");

        })
        $("#generarreporteProd").click(()=>{
            console.log("---------------------------------------")
            const anioREd=$("#anioreporEFG").val()
            const messREd=parseInt($("#mesreprEFG").val())
            const diaRed=$("#diareporEfghg").val().length>0?parseInt($("#diareporEfghg").val()):'nn'
            window.open(_URL +
                `/reporte/productos/pdf/${codProdT}?fecha=${anioREd}${messREd}-${diaRed}`)
            $("#modal-prodEreport").modal("hide");
        })

        $("#datatable").on("click", ".btn-reporte", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");

            codProdT=cod
            $("#modal-prodEreport").modal("show");
            //console.log(cod);

            //window.open(_URL + `/reporte/productos/pdf/${cod}`)
            /*  app._data.restock.cod = cod
             app._data.restock.cantidad = ''
             $("#modal-restock").modal("show"); */

        })
        $("#datatable").on("click", ".btn-reporte-prod", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");

            codProdT=cod
            $("#modal-prodEreport").modal("show");
            //console.log(cod);

            //window.open(_URL + `/reporte/productos/pdf/${cod}`)
            /*  app._data.restock.cod = cod
             app._data.restock.cantidad = ''
             $("#modal-restock").modal("show"); */

        })


        $("#datatable").on("click", ".btn-ver-precios", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");
            console.log(cod);
            $("#modal-precios").modal("show");
            _ajax("/ajs/cargar/productos/precios", "POST", {
                    cod
                },
                function(resp) {
                    console.log(resp);
                    $("#modal-precios").modal("show");
                    /*  $('#precio1').val(resp.precio)
                     $('#precio2').val(resp.precio2)
                     $('#precio3').val(resp.precio3)
                     isNaN(resp.precio4) ? $('#precio4').val('') : parseFloat(resp.precio4 + "").toFixed(2) */

                    app._data.edt.precio = resp.precio == null ? parseFloat(0 + "").toFixed(2) : resp.precio
                    app._data.edt.precio2 = resp.precio2 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio2 + "").toFixed(2)
                    /*    .toFixed(2) */
                    app._data.edt.precio3 = resp.precio3 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio3 + "").toFixed(2)
                    app._data.edt.precio4 = resp.precio4 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio4 + "").toFixed(2)
                    app._data.edt.precio_unidad = resp.precio_unidad == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio_unidad + "").toFixed(2)

                    $('#precio1').val(resp.precio == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio + "").toFixed(2))
                    $('#precio2').val(resp.precio2 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio2 + "").toFixed(2))
                    $('#precio3').val(resp.precio3 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio3 + "").toFixed(2))
                    $('#precio3').val(resp.precio4 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio4 + "").toFixed(2))
                    $('#precio_unidad').val(resp.precio_unidad == null ? parseFloat(0 + "").toFixed(2) : parseFloat(resp.precio_unidad + "").toFixed(2))
                    app._data.edt.cod_prod = cod
                    /* if (resp.res) {


                    } */
                }
            )

        })


        $("#datatable").on("click", ".btn-edt", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");
            /*   console.log(cod); */
            _ajax("/ajs/data/producto/info", "POST", {
                    cod
                },
                function(resp) {
                    console.log(resp);
                    if (resp.res) {
                        app.setInfo(resp.data)
                    } else {
                        alertAdvertencia("Informacion no encontrada")
                    }
                }
            )
        })



        $("#datatable").on("click", ".btnCheckEliminar", function(evt) {


            const cod = $(evt.currentTarget).attr("data-id");
            /*   console.log($('.btnCheckEliminar').checked); */
            let idCheckTrue = false
            /* console.log($(evt.currentTarget).is(":checked")); */
            if ($(evt.currentTarget).is(':checked')) {
                if (arrayIdsOkUsar.findIndex(e => e.id == cod) == -1) {
                    arrayIdsOkUsar.push({
                        id: cod
                    });
                }
            }
            /*  let arrayCheck = [] */
            else {
                const indexArray = arrayIdsOkUsar.findIndex(e => e.id == cod)
                if (indexArray > -1) {
                    arrayIdsOkUsar.splice(indexArray, 1)
                }
            }
            /*  $("input:checkbox[class=btnCheckEliminar]:checked").each(function() {

             }); */
            localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
            /*    console.log(arrayCheck); */
        })





        $('.btnBorrar').click(function() {

            console.log(localStorage.getItem('idChecks'));
            let ids = localStorage.getItem('idChecks')
            let arrayId = JSON.parse(ids)
            Swal.fire({
                title: 'Desea borrar estos productos?',
                showDenyButton: true,

                confirmButtonText: 'Si',
                denyButtonText: `No`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    if (localStorage.getItem("idChecks") !== null) {
                        _ajax("/ajs/data/producto/delete", "POST", {
                                arrayId
                            },
                            function(resp) {
                                console.log(resp);
                                if (resp.res) {
                                    localStorage.removeItem('idChecks');
                                    Swal.fire('Buen trabajo',
                                        'Productos borrados exitosamente',
                                        'success', {}).then((result) => {

                                        location.reload();
                                    });
                                } else {
                                    alertAdvertencia("Ocurrio un error")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("Seleccione productos")
                    }
                    /*   */
                }
            })

        })


        $('.btnSeleccionarTodos').click(function() {

            /*   var p = datatable.rows({
                page: 'current'
            }).nodes();
            let array = []
            for (let index = 0; index < 10; index++) {
                array.push(p[index])
            }
            for (let index = 0; index < array.length; index++) {
                if (array[index] != undefined) {
                    let element = array[index];
                    let data = element.children[12].children[0]
                    $(data).prop('checked', true);
                    let ids = $(data).attr("data-id")
                    if (arrayIdsOkUsar.findIndex(e => e.id == ids) == -1) {
                        arrayIdsOkUsar.push({
                            id: ids
                        });
                    }
                    console.log(data);
                    console.log(ids);
                    console.log(arrayIdsOkUsar);
                    app._data.listaIds = arrayIdsOkUsar
                    console.log(app._data.listaIds);
                    localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
                    console.log(localStorage.getItem('idChecks'));
                }
            }
 */
            /*   return */

            var p = datatable.rows({
                page: 'current'
            }).nodes();


            /*    console.log(p);
               return */
            arrayIdsOkUsar = []
            if (this.checked) {
                const listaChek = $('.btnCheckEliminar')
                for (let item of listaChek) {
                    const itemE = $(item)
                    arrayIdsOkUsar.push({
                        id: itemE.attr("data-id")
                    })
                    itemE.prop('checked', true);
                }
            } else {
                const listaChek = $('.btnCheckEliminar')
                for (let item of listaChek) {
                    const itemE = $(item)
                    itemE.prop('checked', false);
                }
            }
            localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));

            return
            if (this.checked) {
                /*   localStorage.removeItem('idChecks'); */
                var p = datatable.rows({
                    page: 'current'
                }).nodes();
                let array = []
                for (let index = 0; index < 10; index++) {
                    array.push(p[index])
                }
                for (let index = 0; index < array.length; index++) {
                    if (array[index] != undefined) {
                        let element = array[index];
                        let data = element.children[12].children[0]
                        $(data).prop('checked', true);
                        let ids = $(data).attr("data-id")
                        if (arrayIdsOkUsar.findIndex(e => e.id == ids) == -1) {
                            arrayIdsOkUsar.push({
                                id: ids
                            });
                        }
                        console.log(data);
                        console.log(ids);
                        app._data.listaIds = arrayIdsOkUsar
                        localStorage.setItem('idChecks', JSON.stringify(arrayIdsOkUsar));
                        console.log(localStorage.getItem('idChecks'));
                    }
                }

            } else {
                localStorage.removeItem('idChecks');
                var p = datatable.rows({
                    page: 'current'
                }).nodes();
                let array = []
                for (let index = 0; index < 10; index++) {
                    array.push(p[index])
                }
                for (let index = 0; index < array.length; index++) {
                    if (array[index] != undefined) {
                        let element = array[index];
                        let data = element.children[12].children[0]
                        $(data).prop('checked', false);
                        /*   let ids = $(data).attr("data-id") */
                    }
                }
                /*   localStorage.removeItem('idChecks'); */
            }


        })
        
        $("#datatable").on("click", ".btnCheckActivar", function(evt) {
            // event.preventDefault();

            const idProducto = $(evt.currentTarget).attr("data-id");
            let valorChecked = $(evt.currentTarget).prop('checked');
            console.log(valorChecked);
            /*   console.log($('.btnCheckEliminar').checked); */
            let idCheckTrue = false
            /* console.log($(evt.currentTarget).is(":checked")); */
            let tituloConfirm = "¿Deseas activar este producto?";
            if ($(evt.currentTarget).is(':checked')) { // SE HA SELECCIONADO
                tituloConfirm = "¿Deseas desactivar este producto?";
                $(evt.currentTarget).prop('checked', false);
            }else{ // SE DESELECCIONA
                $(evt.currentTarget).prop('checked', true);
            }

            Swal.fire({
                title: tituloConfirm,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: _URL+"/ajs/data/producto/activar",
                        type: 'POST',
                        data: {
                            idProducto,
                            valorChecked
                        },
                        success: function(response) {
                            try{
                                response = JSON.parse(response);
                                console.log(response);
                                Swal.fire({
                                        text: (response.res) ? 'Producto editado correctamente' : 'Error al editar Producto',
                                        icon: (response.res) ? "success" :
                                            "error",
                                        confirmButtonColor: "#3085d6",
                                        confirmButtonText: "Aceptar",
                                    }).then((result) => {
                                        // if (response.success) location.reload();
                                        if (response.res){
                                            datatable.ajax.reload(null, false);
                                        }else{
                                            evt.preventDefault();
                                        }
                                    });
                            }catch(e){
                                Swal.fire({
                                        text: "No se pudo procesar la solicitud",
                                        icon: "error",
                                        confirmButtonColor: "#3085d6",
                                        confirmButtonText: "Aceptar",
                                    }).then((result) => {
                                        evt.preventDefault();
                                    });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            $(evt.currentTarget).prop('checked', !$(evt.currentTarget).prop('checked'));
                        }
                    });
                } else {
                    evt.preventDefault();
                    // $(evt.currentTarget).prop('checked', !$(evt.currentTarget).prop('checked'));
                }
            });

        })
    })
</script>
