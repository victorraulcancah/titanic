<!doctype html>
<html lang="es">
<head>

    <meta charset="utf-8">
    <title>Starter Page | Veltrix - Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description">
    <meta content="Themesbrand" name="author">
    <link rel="shortcut icon" href="<?=URL::to('public/assets/images/favicon.ico')?>">
    <link href="<?=URL::to('public/assets/css/bootstrap.min.css')?>" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('public/assets/css/icons.min.css')?>" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('public/assets/css/app.min.css?v=2')?>" id="app-style" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('public/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')?>" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('public/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')?>" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="<?=URL::to('public/plugin/sweetalert2/sweetalert2.min.css')?>">
    <link href="<?=URL::to('public/plugin/jquery-ui/jquery-ui.css')?>" rel="stylesheet" type="text/css">
    <link href="<?=URL::to('public/assets/libs/%40fullcalendar/core/main.min.css')?>" rel="stylesheet" type="text/css" />
    <link href="<?=URL::to('public/assets/libs/%40fullcalendar/daygrid/main.min.css')?>" rel="stylesheet" type="text/css" />
    <link href="<?=URL::to('public/assets/libs/%40fullcalendar/bootstrap/main.min.css')?>" rel="stylesheet" type="text/css" />
    <link href="<?=URL::to('public/assets/libs/%40fullcalendar/timegrid/main.min.css')?>" rel="stylesheet" type="text/css" />
    <script>
        const _URL='<?=URL::base()?>';
    </script>

    <style>
        @keyframes ldio-407auvblvok {
            0% { transform: rotate(0) }
            100% { transform: rotate(360deg) }
        }
        .ldio-407auvblvok div { box-sizing: border-box!important }
        .ldio-407auvblvok > div {
            position: absolute;
            width: 79.92px;
            height: 79.92px;
            top: 15.540000000000001px;
            left: 15.540000000000001px;
            border-radius: 50%;
            border: 8.88px solid #000;
            border-color: #626ed4 transparent #626ed4 transparent;
            animation: ldio-407auvblvok 1s linear infinite;
        }

        .ldio-407auvblvok > div:nth-child(2), .ldio-407auvblvok > div:nth-child(4) {
            width: 59.940000000000005px;
            height: 59.940000000000005px;
            top: 25.53px;
            left: 25.53px;
            animation: ldio-407auvblvok 1s linear infinite reverse;
        }
        .ldio-407auvblvok > div:nth-child(2) {
            border-color: transparent #02a499 transparent #02a499
        }
        .ldio-407auvblvok > div:nth-child(3) { border-color: transparent }
        .ldio-407auvblvok > div:nth-child(3) div {
            position: absolute;
            width: 100%;
            height: 100%;
            transform: rotate(45deg);
        }
        .ldio-407auvblvok > div:nth-child(3) div:before, .ldio-407auvblvok > div:nth-child(3) div:after {
            content: "";
            display: block;
            position: absolute;
            width: 8.88px;
            height: 8.88px;
            top: -8.88px;
            left: 26.64px;
            background: #626ed4;
            border-radius: 50%;
            box-shadow: 0 71.04px 0 0 #626ed4;
        }
        .ldio-407auvblvok > div:nth-child(3) div:after {
            left: -8.88px;
            top: 26.64px;
            box-shadow: 71.04px 0 0 0 #626ed4;
        }

        .ldio-407auvblvok > div:nth-child(4) { border-color: transparent; }
        .ldio-407auvblvok > div:nth-child(4) div {
            position: absolute;
            width: 100%;
            height: 100%;
            transform: rotate(45deg);
        }
        .ldio-407auvblvok > div:nth-child(4) div:before, .ldio-407auvblvok > div:nth-child(4) div:after {
            content: "";
            display: block;
            position: absolute;
            width: 8.88px;
            height: 8.88px;
            top: -8.88px;
            left: 16.650000000000002px;
            background: #02a499;
            border-radius: 50%;
            box-shadow: 0 51.06px 0 0 #02a499;
        }
        .ldio-407auvblvok > div:nth-child(4) div:after {
            left: -8.88px;
            top: 16.650000000000002px;
            box-shadow: 51.06px 0 0 0 #02a499;
        }
        .loadingio-spinner-double-ring-8kmkrab6ncg {
            width: 111px;
            height: 111px;
            display: inline-block;
            overflow: hidden;
            background: rgba(255, 255, 255, 0);
        }
        .ldio-407auvblvok {
            width: 100%;
            height: 100%;
            position: relative;
            transform: translateZ(0) scale(1);
            backface-visibility: hidden;
            transform-origin: 0 0; /* see note above */
        }
        .ldio-407auvblvok div { box-sizing: content-box; }
        /* generated by https://loading.io/ */
    </style>
    <style>

        #loader-menor{
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            width: 100%;
            height: 100%;
            display: none;
            background-color: #ffffff96;
            line-height: 100vh;
            text-align: center;
        }
    </style>