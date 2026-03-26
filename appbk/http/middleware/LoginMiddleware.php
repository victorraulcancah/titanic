<?php

class LoginMiddleware extends Middleware{
    public function valid()
    {
        if (isset($_SESSION['usuario_fac'])){
            return true;
        }
        return false;
    }
    public function is_false()
    {
        header('Location: '.URL::to('login'));
        die();
    }

}