<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    header {
        background-color:#445084;
    }

</style>
<header>
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box" style="background-color: #445084;" >
                <a href="index.html" class="logo logo-white">
                                <span class="logo-sm">
                                    <img src="<?=URL::to('public/assets/images/lencika-pink.png')?>" alt="" height="60"> 
                                </span>
                    <span class="logo-lg">
                                    <img src="<?=URL::to('public/assets/images/lencika-pink.png')?>" alt="" width="200" class="center">
                                </span>
                </a>

               
            </div>

            <!-- <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                <i class="mdi mdi-menu"></i>
            </button> -->


        </div>
                  
        <style>
    .flex{
    display: block;
    align-items: center;
    justify-content: flex-start;
    flex-wrap: wrap;
}
.mr{
    margin: 5px;
    display: flex;
    align-items: center;
    justify-content: flex-start; 
}
.pad{
    padding: 4px;
    margin-right: 10px;
}
.mr:hover{
    background: #0e7490;
    
    border: none;
}
@media only screen and (max-width: 768px) {
    .flex {
        flex-direction: column; /* Los elementos se apilan en vertical en pantallas pequeñas */
        align-items: center; /* Centramos los elementos en pantallas pequeñas */
    }

    .mr {
        width: 100%; /* Cada elemento ocupará el ancho completo de la pantalla */
        justify-content: center; /* Centramos el contenido dentro del contenedor en pantallas pequeñas */
    }

    .pad {
        margin-right: 0;
        padding: 8px; /* Aumenta el padding en pantallas pequeñas para mayor accesibilidad */
    }

    /* Alineación de iconos */
    .icon {
        font-size: 24px; 
        margin-right: 10px ;
    }

    .mr .icon {
        display: inline-block;
        vertical-align: middle;
    }
}



 </style>
        <div class="d-flex">
            <!-- App Search-->
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="<?=URL::to('public/assets/images/confi.png')?>"
                         alt="Header Avatar">
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->

                    <div >
                        <div class="flex">
                            <a class=" btn btn-primary mr" id="light-mode-switch">
                            <i class="fa fa-sun pad"></i>    
                            Light</a>
                            <a class="btn btn-primary mr" id="dark-mode-switch">
                            <i class="fa fa-moon pad"></i>    
                            Dark</a>
                            <div class="dropdown-divider"></div>
                    <a id="logout" class="btn btn-primary mr" href="<?=URL::to('/logout')?>"><i class="bi bi-box-arrow-left pad"></i> Logout</a>
                        </div>
                     
                 
                        
                        
                            
                    </div> 


            </div>



        </div>
    </div>
</header>