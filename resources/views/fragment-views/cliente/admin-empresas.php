<?php
$conexion = (new Conexion())->getConexion();

$listaEmpresas = $conexion->query("select * from empresas");

?>
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
            <div class="card-body">

                <h4 class="card-title"></h4>

                <div class="card-title-desc">
                </div>

                <div class="table-responsive">
                    <table id="tabla-empresas" class="table table-bordered table-sm text-center">
                        <thead>
                        <tr>
                            <th></th>
                            <th>RUC</th>
                            <th>Empresa</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Modo</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($listaEmpresas as $empr){ ?>
                            <tr>
                                <td><?= $empr['id_empresa']?></td>
                                <td><?= $empr['ruc']?></td>
                                <td><?= $empr['razon_social']?></td>
                                <td><?= $empr['email']?></td>
                                <td><?= $empr['telefono']?></td>
                                <td><?= $empr['modo']?></td>
                                <td><a href="<?='/administrarempresas/ventas/'.$empr['id_empresa']?>" class="btn btn-info btn-sm button-link"><i class="fa fa-eye"></i></a></td>
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

<script>
    $(document).ready(function(){
        $("#tabla-empresas").DataTable({

        })
    })
</script>