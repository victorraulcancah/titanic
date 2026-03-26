<?php

class ValidarTokenMiddleware extends Middleware
{
    public function valid()
    {
        $headers = apache_request_headers();

        if (isset($headers['token-app'])){
            $token = json_decode(Tools::decryptText($headers['token-app']),true);
            if ($token){
                foreach ($token as $key => $value) {
                    $_SESSION[$key] = $value;
                }
            }
        }

    }
}