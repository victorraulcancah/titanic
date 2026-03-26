<?php

class RequiresMod
{
    private $rutafol;
    private $modules;

    public function __construct($rutafol, $modules)
    {
        $this->rutafol = $rutafol;
        $this->modules = $modules;
    }
    public function subModule($rutafol, $modules){
        $this->rutafol = $rutafol;
        $this->modules = $modules;
        return $this->rasterRequite();
    }
    public function rasterRequite(){
        $ruta = PATH_APP.$this->rutafol."/";
        if (file_exists($ruta)) {
            if (count($this->modules)>0){
                foreach($this->modules as $md){
                    if(is_file("$ruta$md")){
                        require_once  "$ruta$md";
                    }
                }
            }else{
                $listaR=$this->listar_directorios_ruta($ruta);
                Tools::prettyPrint($listaR);;
            }
        }else{
            throw new Exception('Ubicación de módulos no encontrada "'.$ruta.'"');
        }
        return $this;
    }
    private function listar_directorios_ruta($ruta){

            if ($dh = opendir($ruta)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($ruta . $file) && $file!="." && $file!=".."){
                        $this->listar_directorios_ruta($ruta . $file . "/");
                    }elseif(is_file("$ruta$file")){
                        require_once  "$ruta$file";
                        //echo "<br>Directorio: $ruta$file";
                    }
                }
                closedir($dh);
            }
          return "";
    }
}