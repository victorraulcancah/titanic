<?php

class SendURL
{
    public static function SendGuiaRemision($data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,URL_GEN_XML_SUNAT.'/guiaremision.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        return $server_output;
    }
    public static function SendComprobante($data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,URL_GEN_XML_SUNAT.'/factura.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        return $server_output;
        /*if ($server_output == "OK"){

        }else{

        }*/
    }
}