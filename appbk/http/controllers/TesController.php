<?php

class TesController extends Controller
{
    public function tes(){
        return "tessssss";
    }
    public function prueba(){
        var_dump($_POST["nombre"]);
    }
}