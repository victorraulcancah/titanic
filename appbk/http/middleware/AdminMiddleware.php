<?php

class AdminMiddleware extends Middleware
{
    public function valid()
    {
        return $_SESSION['rol']==1;
    }

    public function is_false()
    {
        header('Location: '.URL::base());
        exit();
    }

}