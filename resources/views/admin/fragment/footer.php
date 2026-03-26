<script src="<?=URL::to('public/assets/libs/jquery/jquery.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/bootstrap/js/bootstrap.bundle.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/metismenu/metisMenu.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/simplebar/simplebar.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/node-waves/waves.min.js')?>"></script>
<script src="<?=URL::to('public/assets/js/app.js')?>"></script>
<script src="<?=URL::to('public/js/tools.js')?>"></script>
<script src="<?=URL::to('public/js/main.js')?>"></script>
<script src="<?=URL::to('public/js/clientes.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/datatables.net/js/jquery.dataTables.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js')?>"></script>
<script src="<?=URL::to('public/plugin/jquery-ui/jquery-ui.min.js')?>" type="text/javascript"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>
<script src="<?=URL::to('public/assets/libs/moment/min/moment.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/core/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/bootstrap/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/daygrid/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/timegrid/main.min.js')?>"></script>
<script src="<?=URL::to('public/assets/libs/%40fullcalendar/interaction/main.min.js')?>"></script>
<script>
    if (window.history && window.history.pushState) {
        window.onpopstate = function() {
            $("#loader-menor").show();
            _ajaxDOM(getPathURL(),'contenedor-app')
            reselc_estadop();
        }
    }

    $(document).ready(function (){
        _ajaxDOM(getPathURL(),'contenedor-app')
        $('body').on('click','#toggle-sidebar',function(){
            $(".page-wrapper").toggleClass("toggled");
        })
    })


</script>
