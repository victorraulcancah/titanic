<?php

class ViewController extends Controller
{
    public function index(){
        return $this->view('index');
    }
    public function indexAdmin(){
        return $this->view('admin/index');
    }
    public function login(){
        return $this->view('login');
    }
    public function escanearBarra(){
        return $this->view('escanear-barra');
    }
}