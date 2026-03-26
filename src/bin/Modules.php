<?php

class Modules
{

    public static function requires($ubicacionFolders, $modulos=[]){
        $requieresMod = new RequiresMod($ubicacionFolders,$modulos);
        return $requieresMod->rasterRequite();
    }


}