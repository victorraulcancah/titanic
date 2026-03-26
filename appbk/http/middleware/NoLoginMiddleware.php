<?php

class NoLoginMiddleware extends Middleware{
    public function valid()
    {
        if (isset($_SESSION['usuario_fac'])){
            return true;
        }
        return false;
    }
    public function is_true()
    {
        header('Location: '.URL::base());
        die();
    }

}