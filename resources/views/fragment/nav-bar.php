<style>
/* Add your custom styles here */
.nav-item a i{
    color: #ec7964;
}
.nav-item a {
    color: white;
    /* font-weight: bold; */
}
.nav-item ul li a {
    color: black;
    /* font-weight: bold; */
}
.panel-palpitante {
    background-color: white;
    animation-name: colorpalpitante;
    animation-duration: 1.5s;
    animation-iteration-count: infinite;
}
@keyframes colorpalpitante {
    from {
        background-color: #ffad9e;
        color: white
    }
    to {
        background-color:#626ed4;
        color: white
    }
}
.texto-palpitante {
    color: #000000;
    animation-name: colorpalpitante2;
    animation-duration: 1.5s;
    animation-iteration-count: infinite;
}
@keyframes colorpalpitante2 {
    from {
        color: white;
    }
    to {
        color: #ffffff;
    }
}
</style>
<?php 
$id_role = isset($_SESSION['rol'])?$_SESSION['rol']:1;
?>
<nav class="navbar navbar-expand-lg" style="background-color: #28719B;">
<div class="container" style="max-width: 1640px;">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="color: #4e58aa;">
        <i class="fa fa-align-justify" style="color: #445084;"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- inicio del nav del administrador -->
        <?php if($id_role == "1"):?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100">
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>">
                    <i class="ti-home"></i>DASHBOARD
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti-package"></i>FACTURACIÓN
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>ventas">Ventas</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>guias/remision">Guías Remisión</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>nota/electronica/lista">Notas Electrónicas</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cotizaciones">
                    <i class="fa fa-align-justify"></i>PEDIDOS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cobranzas">
                    <i class="fa fa-money-bill"></i>CUENTAS POR COBRAR
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>pagos">
                    <i class="fa fa-money-bill"></i>CUENTAS POR PAGAR
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>deudas">
                    <i class="fa fa-money-bill"></i>Reporte C.
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>devoluciones">
                    <i class="fa fa-money-bill"></i>DEVOLUCIÓNES
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti-package"></i>CAJAS
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>cajaRegistros">Registro</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>caja/flujo">Caja Chica</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>arqueo-diario">Arqueo Diario</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>compras">
                    <i class="ti-calendar"></i>COMPRAS
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti-view-grid"></i>ALMACÉN
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>almacen/productos">Kardex</a></li>
                </ul>
            </li>
            <li class="nav-item panel-palpitante">
                <a class="nav-link texto-palpitante" href="<?= DOMINIO ?>almacen/intercambio/productos">
                    <i class="ti-calendar"></i>Intercambio Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>clientes">
                    <i class="ti-calendar"></i>CLIENTES
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>usuarios" style="cursor: pointer;">
                    <i class="ti-user"></i>USUARIOS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>proveedores" style="cursor: pointer;">
                    <i class="ti-calendar"></i><span>PROVEEDORES</span>
                </a>
            </li>
        </ul>
        <?php endif;?>
        <!-- fin del nav de administrador -->

        <!-- inicio de nav del vendedor -->
        <?php if($id_role == "3"):?> 
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100">
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cotizaciones">
                    <i class="fa fa-align-justify"></i>PEDIDOS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>clientes">
                    <i class="ti-calendar"></i>CLIENTES
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cobranzas">
                    <i class="fa fa-money-bill"></i>CUENTAS POR COBRAR
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>mis-cobros">
                    <i class="fa fa-money-bill"></i>MIS COBROS
                </a>
            </li>
        </ul>
        <?php endif;?> 
        <!-- fin del nav del vendedor -->

        <!-- inicio de nav de cajera -->
        <?php if($id_role == "4"): ?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100">
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cobranzas">
                    <i class="fa fa-money-bill"></i>CUENTAS POR COBRAR
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>cotizaciones">
                    <i class="fa fa-align-justify"></i>PEDIDOS
                </a>
            </li>
            <li hidden class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti-package"></i>FACTURACIÓN
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>ventas">Ventas</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>guias/remision">Guías Remisión</a></li>
                    <li><a class="dropdown-item" href="<?= DOMINIO ?>nota/electronica/lista">Notas Electrónicas</a></li>
                </ul>
            </li>
            <li hidden class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>clientes">
                    <i class="ti-calendar"></i>CLIENTES
                </a>
            </li>
        </ul>
        <?php endif;?>
        <!-- fin de nav de cajera -->

        <!-- inicio de nav de contador -->
        <?php if($id_role == "5"): ?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100 text-center">
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>ventas">
                    <i class="ti-calendar"></i>Ventas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>guias/remision">
                    <i class="ti-calendar"></i>Guías Remisión
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>clientes">
                    <i class="ti-calendar"></i>Notas Electrónicas
                </a>
            </li>
        </ul>
        <?php endif; ?>
        <!-- fin de nav de contador -->

        <!-- inicio de nav de almacen -->
        <?php if($id_role == "6"):?>
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100">
            <li class="nav-item">
                <a class="nav-link" href="<?= DOMINIO ?>almacen/productos">
                    <i class="ti-calendar"></i>Kardex
                </a>
            </li>
            <li class="nav-item panel-palpitante">
                <a class="nav-link texto-palpitante" href="<?= DOMINIO ?>almacen/intercambio/productos">
                    <i class="ti-calendar"></i>Intercambio Productos
                </a>
            </li>
        </ul>
        <?php endif;?>
        <!-- fin de nav de almacen -->
    </div>
</div>
</nav>
