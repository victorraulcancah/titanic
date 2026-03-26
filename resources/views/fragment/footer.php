<script src="<?=URL::to('public/assets/libs/jquery/jquery.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/metismenu/metisMenu.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/simplebar/simplebar.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/node-waves/waves.min.js')?>"></script>
<script src="<?=URL::to('public/assets/js/app.js')?>"></script>
<script src="<?=URL::to('public/js/tools.js?v=0.0.3')?>"></script>
<script src="<?=URL::to('public/js/main.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/datatables.net/js/jquery.dataTables.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js')?>"></script>
<script src="<?=URL::to('public/plugin/jquery-ui/jquery-ui.min.js')?>" type="text/javascript"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>
<!-- <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script> -->
<script src="<?=URL::to('public/assets/libs/moment/min/moment.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/core/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/bootstrap/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/daygrid/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/timegrid/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/interaction/main.min.js')?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/decimal.js/10.4.3/decimal.min.js" integrity="sha512-WWzCZDQZ23GuPVKPowBGCF6MhoA1az8iJk/Gjh2a5S3jeeNEvKJHgGPMyDofeUtcOeHeI3AbsPFUILWHfoRP8w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- (Optional) Latest compiled and minified JavaScript translation files -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-*.min.js"></script>
<?php
if (isset($_SESSION['usuario_fac'])){  ?>

    <script>
        if (window.history && window.history.pushState) {
            window.onpopstate = function() {
                $("#loader-menor").show();
                _ajaxDOM(getPathURL(),'contenedor-app')
                reselc_estadop();
            }
        }


    </script>

    <?php
}
?>

