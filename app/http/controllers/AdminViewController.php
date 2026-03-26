<?php

class AdminViewController extends Controller
{
    public function index(){
        return $this->view("admin/frafment-views/inicio");
    }

    public function clientes(){
        return $this->view("fragment-views/admin/mis-clientes");
    }

}