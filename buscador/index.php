<?php
 $bid="20603319274";
 ?>

<!--?xml version='1.0' encoding='UTF-8' ?-->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"><head id="j_idt2">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!--SI  -->
    <link type="text/css" rel="stylesheet" href="assets/css/layout.css">
    <link type="text/css" rel="stylesheet" href="assets/css/components.css">
    <link type="text/css" rel="stylesheet" href="assets/css/core.css"> 
    <link type="text/css" rel="stylesheet" href="assets/css/theme.css">
    <link href="assets/img/favicon.ico" rel="icon" type="image/x-icon">
    <link href="assets/img/favicon.ico" rel="shortcut icon" type="image/x-icon">         
    <!--[if lt IE 9]><script src="/ConsultaComprobanteE/faces/javax.faces.resource/js/html5shiv.js?ln=bsf"></script><script src="/ConsultaComprobanteE/faces/javax.faces.resource/js/respond.js?ln=bsf"></script><![endif]-->

    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Consultar Comprobante Electrónico</title>
    </head>
      <body class="login-body"> 
        <br />       
        <div id="login-wrapper">   
                                  
            <div id="login-container">
                <span class="title">Consulta Comprobante Electrónico</span><span id="grow1"></span>
                    <form id="formLogin" name="formLogin">
                        <div class="ui-g ui-fluid">
                        <div class="ui-g-12"><input id="rucemisor" name="rucemisor" value="<?=$bid;?>" type="text" maxlength="11" placeholder="RUC Emisor" tabindex="1" aria-required="true" class="ui-inputfield ui-inputtext ui-widget ui-state-default ui-corner-all input-text" role="textbox" aria-disabled="false" aria-readonly="true" style="background-color: #d2d6de;"></div>                    
                        <div class="ui-g-12 ui-md-4"><input id="serie" name="serie" type="text" maxlength="4" placeholder="Serie" tabindex="2" onkeyup="javascript:this.value = this.value.toUpperCase();" aria-required="true" class="ui-inputfield ui-inputtext ui-widget ui-state-default ui-corner-all input-text" role="textbox" aria-disabled="false" aria-readonly="false"></div>  
                        <div class="ui-g-12 ui-md-8"><input id="correlativo" name="correlativo" type="text" maxlength="8" placeholder="Correlativo" tabindex="3" aria-required="true" class="ui-inputfield ui-inputtext ui-widget ui-state-default ui-corner-all input-text" role="textbox" aria-disabled="false" aria-readonly="false">
                        </div>                         
                        <div class="ui-g-12">
                            <div class="form-group " id="console">
                            <select id="consoleInner" name="consoleInner" class="form-control select-one-menu  bf-required" style="margin-bottom: -12px" tabindex="4">
                                <option data-label="[- Seleccione Tipo Documento -]" value="" selected="selected">[- Seleccione Tipo Documento -]</option>
                                <option data-label="FACTURA" value="2">FACTURA</option>
                                <option data-label="BOLETA DE VENTA" value="1">BOLETA DE VENTA</option>
                                <option data-label="NOTA DE CREDITO" value="3">NOTA DE CREDITO</option>
                                <option data-label="NOTA DE DEBITO" value="4">NOTA DE DEBITO</option>
                                <option data-label="GUIA DE REMISIÓN REMITENTE" value="11">GUIA DE REMISIÓN REMITENTE</option>
                            </select>
                            </div>                        
                        </div>            
                        <!--<div class="ui-g-12 ">
                            <input id="captchaCode" name="captchaCode" type="text" placeholder="Valor Captcha" tabindex="7" aria-required="true" class="ui-inputfield ui-inputtext ui-widget ui-state-default ui-corner-all input-text" role="textbox" aria-disabled="false" aria-readonly="false"> 
                        </div>-->               
                     
                        <div class="ui-g-12" align="center">
                        <br />
                            <button id="loginButton" name="loginButton" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-right btn btn-primary" style="width:80%;" tabindex="8" type="submit" role="button" aria-disabled="false">
                            <span class="ui-button-icon-right ui-icon ui-c fa fa-search"></span>
                            <span class="ui-button-text ui-c">Consultar Comprobante</span>
                         </button>
                        </div>
                        <div align="center">
                            <p style="font-size: 14px;  line-height: 1.5;  color: #e2e2e2;">Desarrollado por:</p>    
                            <img id="login-image" src="assets/img/magus.png" style="filter: grayscale(100%); opacity: 40%; transition: 0.4s;" height="38%" alt="" style="margin-bottom: 5px">                            
                        </div> 
                       
                        
                    </div>
                    </form>  
                                 
            </div>     
    
        </div>
        
</body>
<script type="text/javascript" src="assets/js/jquery.js"></script>
<script type="text/javascript" src="assets/js/core.js"></script>
<script type="text/javascript" src="assets/js/components.js"></script>
<script type="text/javascript">if(window.PrimeFaces){PrimeFaces.settings.locale='en_US';}</script>
<script>
$(document).ready(function() {
  var opcion , buscar, ruta;

   $("#formLogin").trigger("reset");
   
  //ruta ='../PDF/documentos/R_Tablapago.php?&id='+guid+'&emp='+empid;
  //
  //ruta = "https://magustechnologies.com/kanako/venta/comprobante/pdf/6446/20611556960-01-F001-142";

  
  $('#formLogin').submit(function(e){
  opcion =1;
  serie = $.trim($('#serie').val());
  correlativo = $.trim($('#correlativo').val());
  rucemisor = $.trim($('#rucemisor').val());
  consoleInner = $.trim($('#consoleInner').val());

  e.preventDefault();
    $.ajax({    
         url:  "assets/functions/Buscar.php",
         type: "POST",
         datatype:"json",
         data:  {opcion:opcion,serie:serie,correlativo:correlativo,consoleInner:consoleInner},
         success: function(data)
         {
          var cadena = data;
          let ObjetoJS = JSON.parse(cadena);
          //RECORRER OBJETO
          for (let item of ObjetoJS){
              var vruta = item.ruta;
              var vfiltro = item.buscar;
          }
      	  if (vfiltro>0) {
		ruta = vruta;
		window.open(ruta, '_blank');
          } else {
		alert('Datos no encontrados !!');	
	   }
         }
       });
  

  });  


});
</script>



</html>
