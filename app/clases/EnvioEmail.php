<?php

require "utils/lib/mailer/vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EnvioEmail
{
    private $mail;


    public function __construct()
    {
        $this->mail =  new PHPMailer(true);
        //Server settings
        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $this->mail->isSMTP();                                            //Send using SMTP
        $this->mail->Host       = HOST_SMTP;                     //Set the SMTP server to send through
        $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $this->mail->Username   = USER_SMTP;                     //SMTP username
        $this->mail->Password   = CLAVE_SMTP;                               //SMTP password
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $this->mail->Port       = PUERTO_SMTP;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    public function esHTML($estado){
        $this->mail->isHTML($estado);
        return $this;
    }
    public function de($email,$nombre){
        $this->mail->setFrom($email,$nombre);
        return $this;
    }
    public function addReplicarA($email,$nombre){
        $this->mail->addReplyTo($email,$nombre);
        return $this;
    }

    public function addEmail($email,$nombre=''){
        $this->mail->addAddress($email,$nombre);
        return $this;
    }
    public function addCC($email,$nombre){
        $this->mail->addCC($email,$nombre);
        return $this;
    }
    public function addBCC($email,$nombre){
        $this->mail->AddBCC($email,$nombre);
        return $this;
    }
    public function addArchivoString($contenFile,$name,$type = 'application/pdf', $encoding = 'base64'){
        $this->mail->AddStringAttachment($contenFile, $name, $encoding ,$type);
        return $this;
    }
    public function addArchivo($ruta,$nombre=null){
        if (is_null($nombre)){
            $this->mail->addAttachment($ruta);
        }else{
            $this->mail->addAttachment($ruta, $nombre);
        }
        return $this;
    }
    public function setasunto($text){
        $this->mail->Subject = $text;
        return $this;
    }
    public function cuerpo($text){
        $this->mail->Body = $text;
        return $this;
    }
    public function cuerpoAlternativo($text){
        $this->mail->AltBody = $text;
        return $this;
    }
    public function enviar(){
        return $this->mail->send();
    }
    public function error(){
        return $this->mail->ErrorInfo;
    }
}