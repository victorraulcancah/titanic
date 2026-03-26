<!doctype html>
<html lang="es">
<head>

    <meta charset="utf-8">
    <title>404 | Veltrix - Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description">
    <meta content="Themesbrand" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?=URL::to('public/assets/images/favicon.ico')?>">

    <!-- Bootstrap Css -->
    <link href="<?=URL::to('public/assets/css/bootstrap.min.css')?>" id="bootstrap-style" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="<?=URL::to('public/assets/css/icons.min.css')?>" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link href="<?=URL::to('public/assets/css/app.min.css')?>" id="app-style" rel="stylesheet" type="text/css">

</head>

<body>

<div class="authentication-bg d-flex align-items-center pb-0 vh-100">
    <div class="content-center w-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-4 ms-auto">
                                    <div class="ex-page-content">
                                        <h1 class="text-dark display-1 mt-4">404!</h1>
                                        <h4 class="mb-4">Lo siento, página no encontradaLo siento, página no encontrada</h4>
                                        <p class="mb-5"></p>
                                        <a class="btn btn-primary mb-5 waves-effect waves-light" href="<?=URL::to("/")?>">
                                            <i class="mdi mdi-home"></i> Ir al Inicio</a>
                                    </div>

                                </div>
                                <div class="col-lg-5 mx-auto">
                                    <img src="<?=URL::to("public/assets/images/error.png")?>" alt="" class="img-fluid mx-auto d-block">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!--end row-->
        </div>
        <!-- end container -->
    </div>

</div>

<!-- JAVASCRIPT -->
<script src="<?=URL::to('public/assets/libs/jquery/jquery.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/metismenu/metisMenu.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/simplebar/simplebar.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/node-waves/waves.min.js')?>"></script>
<script src="<?=URL::to('public/assets/js/app.js')?>"></script>

</body>
</html>
