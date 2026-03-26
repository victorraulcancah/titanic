<?php
$conexion = (new Conexion())->getConexion();

$sql = "SELECT u.usuario_id,CONCAT(u.nombres) nomb,u.rubro,u.email,u.telefono,e.razon_social,u.estado FROM  usuarios u JOIN empresas e ON e.id_empresa = u.id_empresa WHERE u.id_rol=2
";

$listaUsd= $conexion->query($sql);

?>
<style>
    .rojo{
        color:red;
    }
</style>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Ventas</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
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
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="card-title">Mis Clientes</h4>
                    </div>
                    <div class="col-md-6 text-end">
                        <button data-bs-toggle="modal" data-bs-target="#modal-add-usuario" class="btn btn-primary">Agregar</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="table-miclientes" class="table table-bordered table-sm text-center">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Nombres</th>
                                <th>Rubro</th>
                                <th>Empresa</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th>Estado</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            foreach ($listaUsd as $item){  ?>
                                <tr>
                                    <td><?= $item['usuario_id']?></td>
                                    <td><?= $item['nomb']?></td>
                                    <td><?= $item['rubro']?></td>
                                    <td><?= $item['razon_social']?></td>
                                    <td><?= $item['email']?></td>
                                    <td><?= $item['telefono']?></td>
                                    <td><?= $item['estado']?></td>
                                    <td><button data-item="<?= $item['usuario_id']?>" class="btn-edt btn btn-primary btn-sm"><i class="fa fa-edit"></i></button></td>
                                    <td><button data-item="<?= $item['usuario_id']?>-<?= $item['estado']?>" class="btn-efd btn btn-success btn-sm"><i class="fa fa-align-justify"></i></button></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="conte-model-vue">

    <div class="modal fade" id="modal-add-usuario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="guardarEmpresa">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>DNI:<span class="rojo">(*)</span> </label>
                                <div class="input-group">
                                    <input @change="ChangeconsultarDoc" v-model="reg.documento" required @keypress="onlyNumber" type="text" class="form-control" >
                                    <div class="input-group-prepend">
                                        <button type="button" @click="consultarDoc" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nombres:<span class="rojo">(*)</span> </label>
                                <input v-model="reg.nombres" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Email:<span class="rojo">(*)</span> </label>
                                <input v-model="reg.email" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Telefono: </label>
                                <input v-model="reg.telefono" @keypress="onlyNumber" type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Rubro: </label>
                                <input v-model="reg.rubro" type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Usuario:<span class="rojo">(*)</span> </label>
                                <input v-model="reg.usuario" type="text" class="form-control" >
                            </div><div class="form-group col-md-3">
                                <label>Contraseña:<span class="rojo">(*)</span> </label>
                                <input v-model="reg.clave" type="password" class="form-control" >
                            </div>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <div   style="width: 100%; height: 20px; border-bottom: 2px solid #869fba; text-align: left">
                                                  <span style="font-size: 16px; font-weight: bold ; background-color: #ffffff; padding: 0 5px;">
                                                    Datos de la Empresa<!--Padding is optional-->
                                                  </span>

                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label><span class="rojo"></span>RUC: </label>
                                <div class="input-group">
                                    <input @change="ChangeconsultarDocRUC" v-model="reg.ruc" required @keypress="onlyNumber" type="text" class="form-control" >
                                    <div class="input-group-prepend">
                                        <button type="button" @click="consultarDocRUC" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Razon Social: </label>
                                <input v-model="reg.razon" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-5">
                                <label>Direccion: </label>
                                <input v-model="reg.direccion" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Distrito: </label>
                                <input v-model="reg.distrito" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Provincia: </label>
                                <input v-model="reg.provincia" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Departamento: </label>
                                <input v-model="reg.departamento" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Ubigeo: </label>
                                <input v-model="reg.ubigeo" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Logo: </label>
                                <input id="fil-logo-emp" accept="image/png, image/jpeg"    type="file" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Usuario Sol: </label>
                                <input v-model="reg.usuario_sol" required  type="text" class="form-control" >
                            </div>

                            <div class="form-group col-md-3">
                                <label>Clave Sol: </label>
                                <input v-model="reg.clave_sol" required  type="text" class="form-control" >
                            </div>
                            <div style="display: none" class="form-group col-md-3">
                                <label>Certificado Digital: </label>
                                <input id="certificado-pem" accept=".pem"    type="file" class="form-control" >
                                <small>Certificado Digital en .pem</small>
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
    <div class="modal fade" id="modal-edt-usuario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="actualizarEmpresa">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label>DNI:<span class="rojo">(*)</span> </label>
                                <div class="input-group">
                                    <input @change="ChangeconsultarDoc" v-model="edt.documento" required @keypress="onlyNumber" type="text" class="form-control" >
                                    <div class="input-group-prepend">
                                        <button type="button" @click="consultarDoc" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nombres:<span class="rojo">(*)</span> </label>
                                <input v-model="edt.nombres" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Email:<span class="rojo">(*)</span> </label>
                                <input v-model="edt.email" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Telefono: </label>
                                <input v-model="edt.telefono" @keypress="onlyNumber" type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Rubro: </label>
                                <input v-model="edt.rubro" type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Usuario:<span class="rojo">(*)</span> </label>
                                <input v-model="edt.usuario" type="text" class="form-control" >
                            </div><div class="form-group col-md-3">
                                <label>Contraseña:<span class="rojo">(*)</span> </label>
                                <input v-model="edt.clave" type="password" class="form-control" >
                            </div>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <div   style="width: 100%; height: 20px; border-bottom: 2px solid #869fba; text-align: left">
                                                  <span style="font-size: 16px; font-weight: bold ; background-color: #ffffff; padding: 0 5px;">
                                                    Datos de la Empresa<!--Padding is optional-->
                                                  </span>

                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label><span class="rojo"></span>RUC: </label>
                                <div class="input-group">
                                    <input @change="ChangeconsultarDocRUC" v-model="edt.ruc" required @keypress="onlyNumber" type="text" class="form-control" >
                                    <div class="input-group-prepend">
                                        <button type="button" @click="consultarDocRUC" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Razon Social: </label>
                                <input v-model="edt.razon" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-5">
                                <label>Direccion: </label>
                                <input v-model="edt.direccion" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Distrito: </label>
                                <input v-model="edt.distrito" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Provincia: </label>
                                <input v-model="edt.provincia" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Departamento: </label>
                                <input v-model="edt.departamento" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Ubigeo: </label>
                                <input v-model="edt.ubigeo" required  type="text" class="form-control" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Logo: </label>
                                <input id="fil-logo-emp-edt" accept="image/png, image/jpeg"    type="file" class="form-control" >
                                <small v-if="edt.logo.length>0" >Clik para ver logo actual <strong><a target="_blank" :href="linkURl('/files/logos/'+edt.logo)">Ver Imagen</a></strong></small>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Usuario Sol: </label>
                                <input v-model="edt.usuario_sol" required  type="text" class="form-control" >
                            </div>

                            <div class="form-group col-md-3">
                                <label>Clave Sol: </label>
                                <input v-model="edt.clave_sol" required  type="text" class="form-control" >
                            </div>
                            <div style="display: none" class="form-group col-md-3">
                                <label>Certificado Digital: </label>
                                <input id="certificado-pem-edt" accept=".pem"    type="file" class="form-control" >
                                <small>Certificado Digital en .pem, <strong><a target="_blank" :href="linkURl('/files/facturacion/certificados/'+edt.ruc+'-cert.pem')">Ver certificado actual</a></strong> </small>
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


    <div class="modal fade" id="modal-conf-usd" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Configuracion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-1">
                        <label>Estado</label>
                        <select v-model="confg.estado" class="form-control" >
                            <option value="1">Activo</option>
                            <option value="2">Suspender</option>
                        </select>
                    </div>
                    <div v-if="confg.estado==2" class="form-group mt-1">
                        <label>Mensaje</label>
                        <textarea v-model="confg.mensaje" maxlength="240" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="guardarEstado" type="button" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function estados(std){
        switch (std) {
            case '1':
                return '<span class="badge bg-success">Activo</span>';
            case '2':
                return '<span class="badge bg-warning">Suspendido</span>';
            default:
                return ''
        }
    }
    $(document).ready(function (){
        $("#table-miclientes").DataTable({
            order: [
                [ 0, "desc" ],
            ],
            columnDefs:[
                {
                    targets:6,
                    render(data){
                        return estados(data)
                    }
                }
            ]
        })
        $("#table-miclientes").on("click",".btn-efd",function (evt) {
            const cod =($(evt.currentTarget).attr("data-item")).split("-")
            $("#modal-conf-usd").modal("show");
            //app._data.confg.estado
            app._data.confg.cod=cod[0]
            app._data.confg.estado=cod[1]
            app._data.confg.mensaje=''

        })
        $("#table-miclientes").on("click",".btn-edt",function (evt) {
            //console.log($(evt.currentTarget).attr("data-item"))
            _ajax("/ajs/admin/cliente/info","POST",
                {usr:$(evt.currentTarget).attr("data-item")},
                function (resp) {
                    console.log(resp);
                    if (resp.res){
                        app.setDatoEdt(resp.data)
                    }else {
                        alertAdvertencia("Usuario no encontrado")
                    }
                }
            )
        })
        const app = new Vue({
            el:"#conte-model-vue",
            data:{
                confg:{
                    cod:'',
                    estado:'',
                    mensaje:'',
                },
                reg:{
                    documento:'',
                    nombres:'',
                    email:'',
                    telefono:'',
                    rubro:'',
                    usuario:'',
                    clave:'',

                    ruc:'',
                    razon:'',
                    direccion:'',
                    distrito:'',
                    provincia:'',
                    departamento:'',
                    ubigeo:'',

                    usuario_sol:'',
                    clave_sol:''
                },
                edt:{
                    codus:'',
                    documento:'',
                    nombres:'',
                    email:'',
                    telefono:'',
                    rubro:'',
                    usuario:'',
                    clave:'',

                    codemp:'',
                    ruc:'',
                    razon:'',
                    direccion:'',
                    distrito:'',
                    provincia:'',
                    departamento:'',
                    ubigeo:'',

                    usuario_sol:'',
                    clave_sol:'',
                    logo:'',
                }
            },
            methods:{
                guardarEstado(){
                    const tem={...this.confg}
                    _ajax("/ajs/admin/cliente/estado/edt","POST",
                        tem,
                        function (resp) {
                            console.log(resp);
                            alertExito("Guardado")
                                .then(function (){
                                    location.reload();
                                })
                        }
                    )
                },
                linkURl(ruta){
                    return _URL+ruta;
                },
                setDatoEdt(data){
                    $("#modal-edt-usuario").modal("show");
                    this.edt.codus=data.usuario_id;
                    this.edt.codemp=data.id_empresa;
                    this.edt.logo=data.logo;

                    this.edt.documento=data.num_doc;
                    this.edt.nombres=data.nombres;
                    this.edt.email=data.email;
                    this.edt.telefono=data.telefono;
                    this.edt.rubro=data.rubro;
                    this.edt.usuario=data.usuario;
                    //this.edt.clave=data.;
                    this.edt.ruc=data.ruc;
                    this.edt.razon=data.razon_social;
                    this.edt.direccion=data.direccion;
                    this.edt.distrito=data.distrito;
                    this.edt.provincia=data.provincia;
                    this.edt.departamento=data.departamento;
                    this.edt.ubigeo=data.ubigeo;
                    this.edt.usuario_sol=data.user_sol;
                    this.edt.clave_sol=data.clave_sol;
                },

                actualizarEmpresa(){
                    $("#loader-menor").show();
                    const temp={...this.edt}
                    var fd = new FormData();
                    if ($("#fil-logo-emp-edt").val().length>0){
                        fd.append('file1',$("#fil-logo-emp-edt")[0].files[0]);
                    }
                    if ($("#certificado-pem-edt").val().length>0){
                        fd.append('file2',$("#certificado-pem-edt")[0].files[0]);
                    }
                    fd.append("data",JSON.stringify(temp))

                    $.ajax({
                        type: 'POST',
                        url: _URL+'/ajs/admin/cliente/edt',
                        data: fd,
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function(){
                            console.log('inicio');
                        },
                        error:function(err){
                            $("#loader-menor").hide();
                            console.log(err);
                        },
                        success: function(resp){
                            $("#loader-menor").hide();
                            console.log(resp);
                            if (isJson(resp)){
                                resp=JSON.parse(resp);
                                if (resp.res){
                                    alertExito("Guardado")
                                        .then(function () {
                                            location.reload()
                                        })
                                }else {
                                    alertAdvertencia("No se pudo agregar")
                                }
                            }else{
                                alertError("Error","error en el servidor")
                            }
                        }
                    })

                },
                guardarEmpresa(){
                    $("#loader-menor").show();
                    const temp={...this.reg}
                    var fd = new FormData();
                    if ($("#fil-logo-emp").val().length>0){
                        fd.append('file1',$("#fil-logo-emp")[0].files[0]);
                    }
                    if ($("#certificado-pem").val().length>0){
                        fd.append('file2',$("#certificado-pem")[0].files[0]);
                    }
                    fd.append("data",JSON.stringify(temp))

                    $.ajax({
                        type: 'POST',
                        url: _URL+'/ajs/admin/cliente/add',
                        data: fd,
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function(){
                            console.log('inicio');
                        },
                        error:function(err){
                            $("#loader-menor").hide();
                            console.log(err);
                        },
                        success: function(resp){
                            $("#loader-menor").hide();
                            console.log(resp);
                            if (isJson(resp)){
                                resp=JSON.parse(resp);
                                if (resp.res){
                                    alertExito("Guardado")
                                        .then(function () {
                                            location.reload()
                                        })
                                }else {
                                    alertAdvertencia("No se pudo agregar")
                                }
                            }else{
                                alertError("Error","error en el servidor")
                            }
                            /*resp=JSON.parse(resp);
                            if (resp.res){
                                $("#file-inpor-exel").val("");
                                $("#importar-cliente").modal("hide");

                                APP._data.temp_cli= resp.data_exel
                                listar_cliente_impor(resp.data_exel)
                                setTimeout(function (){
                                    $("#listar-cliente-uper").modal("show");
                                },500)
                            }else{
                                alertAdvertencia("Alerta", "No se pudo subir el archivo");
                            }
                            setTimeout(function (){
                                $('#progreso-exel').css('width', 0+'%').attr('aria-valuenow', 0);

                            },1000)*/
                        }
                    })

                },
                ChangeconsultarDoc(){
                    if (this.reg.documento.length==8){
                        this.getInfoDoc();
                    }else{
                        this.reg.documento=''
                    }
                },
                consultarDoc(){
                    if (this.reg.documento.length==8){

                        this.getInfoDoc();
                    }else{
                        alertAdvertencia("El DNI es de 8 dígitos")
                    }
                },
                ChangeconsultarDocRUC(){
                    if (this.reg.ruc.length==11){
                        this.getInfoDoc2();
                    }else{
                        this.reg.ruc=''
                    }
                },
                consultarDocRUC(){
                    if (this.reg.ruc.length==11){

                        this.getInfoDoc2();
                    }else{
                        alertAdvertencia("El RUC es de 11 dígitos")
                    }
                },
                getInfoDoc2() {
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/doc/cliente", "POST",
                        {doc:this.reg.ruc},
                        function (resp) {
                            console.log(resp);
                            if (resp.res){
                                app._data.reg.razon=resp.data.razon_social;
                                app._data.reg.direccion=resp.data.direccion;
                                app._data.reg.distrito=resp.data.distrito;
                                app._data.reg.provincia=resp.data.provincia;
                                app._data.reg.departamento=resp.data.departamento;
                                app._data.reg.ubigeo=resp.data.ubigeo;
                            }else{
                                alertAdvertencia("Documento no encontrado")
                            }
                        }
                    )
                },
                getInfoDoc() {
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/doc/cliente", "POST",
                        {doc:this.reg.documento},
                        function (resp) {
                            console.log(resp);
                            if (resp.res){
                                app._data.reg.nombres=resp.data.nombre
                            }else{
                                alertAdvertencia("Documento no encontrado")
                            }
                        }
                    )
                },

                onlyNumber ($event) {
                    //console.log($event.keyCode); //keyCodes value
                    let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                        $event.preventDefault();
                    }
                }
            }
        })
    })
</script>