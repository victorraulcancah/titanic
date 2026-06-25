<?php
// define("DOMINIO","http://facturacion_hatuna.test");
define("DOMINIO","http://well-known.test/");
/**
 * DATABASE_CONFIG
 */
define("HOST_SS","localhost");
define("DATABASE_SS","titanic");
define("USER_SS","root");
define("PASSWORD_SS","");


/**
 * EMAILS_CONFIG
*/
define("HOST_SMTP","matrixsistem.com");
define("USER_SMTP","informes@matrixsistem.com");
define("CLAVE_SMTP","s(^&2_b5$2lp");
define("PUERTO_SMTP","465");

/*define("HOST_SMTP","mail.cooplafabril.com");
define("USER_SMTP","mail_send@cooplafabril.com");
define("CLAVE_SMTP","J!nUsx)iM_OS");
define("PUERTO_SMTP","465");*/
/**
 * SERVER GEN XML SUNAT
 */
define("ENDPOINT","beta");//production
define("URL_GEN_XML_SUNAT","http://genxml.des");

define("KEY_ENCRYPT","matrixsistem_key");

//php.exe -f F:/programacion/phpProjects/facturacion3/jobs/enviar_sunat.php