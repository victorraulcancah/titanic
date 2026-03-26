<?php


    function formatearClaveValor($obj) {
        if (!is_array($obj))
            $obj = (array)$obj;
        $valores = [];
        foreach ($obj as $clave => $valor):
            $valor = addslashes($valor);
            $valores[] = "$clave = '$valor'";
        endforeach;
        return arrayToString($valores, ',');
    }

    function array_unique_values($ob_json, $id) {
        $codigo = (array)json_decode($ob_json);
        if (is_array(json_decode($id, true))):
            $id = json_decode($id, true);
            $codigo = array_merge($id, $codigo);
        else:
            $codigo[] = $id;
        endif;
        $codigo = array_values($codigo);
        $codigo = array_unique($codigo);
        $codigo = array_values($codigo);
        $codigo = json_encode($codigo);
        return $codigo;
    }

    function array_cambiar_clave($miArray, $clave_antigua, $clave_nueva) {
        $claves = array_keys($miArray);
        $valores = array_values($miArray);

        $posicion = array_search($clave_antigua, $claves);

        if ($posicion !== false) {
            $claves[$posicion] = $clave_nueva;
            $miArrayActualizado = array_combine($claves, $valores);
            return $miArrayActualizado;
        } else {
            return $miArray;
        }
    }

    function array_cambiar_valor($miArray, $valor_antiguo, $valor_nuevo) {
        $claves = array_keys($miArray);
        $valores = array_values($miArray);

        $posicion = array_search($valor_antiguo, $valores);

        if ($posicion !== false) {
            $valores[$posicion] = $valor_nuevo;
            $miArrayActualizado = array_combine($claves, $valores);
            return $miArrayActualizado;
        } else {
            return $miArray;
        }
    }

    function getTiendaCampo($tienda_empresa, $prefijo = '_ids', $cantidad = 3) {
        $campoTienda = strtolower(stringOnlyTrim($tienda_empresa));
        $campoTienda = str_replace('the', '', $campoTienda);
        $campoTienda = substr($campoTienda, 0, $cantidad) . "$prefijo";
        return $campoTienda;
    }

    function mkdir_force($ruta, $permisos = 0777, $recursive = true) {
        if (is_dir($ruta)) :
            rmdir_rf($ruta);
        endif;
        $umaskOriginal = umask(0);
        mkdir($ruta, "$permisos", "$recursive");
        umask($umaskOriginal);
    }

    function boleanString($indice) {
        if ($indice == 1)
            return 'true';
        else
            return 'false';
    }

    function pidSearch($pid) {
        $command = "ps -p $pid -o pid";
        $pid_despues = shell_exec($command);
        $pid_despues = intval(onlyNumber($pid_despues));
        return $pid_despues;
    }

    function commandSearch($unique = false) {
        global $argv;

        $pid_original = getmypid();
        $command = arrayToString($argv, ' ');
        /*console3($command);
        die();*/
        $pidAndCommand = shell_exec("pgrep -af '$command'");

        $pidAndCommand = trim($pidAndCommand);
        $pidAndCommand = explode("\n", trim($pidAndCommand));

        $comandos = [];
        foreach ($pidAndCommand as $key => $m):
            #if (hasText($m, 'pgrep') or hasText($m, '/bin/sh'))
            if (hasText($m, 'pgrep') or hasText($m, '/bin/sh') or hasText($m, 'sh -c'))

                continue;

            if (!empty($m)) {
                @list($pid, $commandName, $thirdPart, $fourthPart) = explode(' ', $m, 4);
                if ($pid_original == $pid):
                    continue;
                endif;

                $commandName = $commandName . ' ' . $thirdPart . ' ' . $fourthPart;
                $comandoObj = new stdClass();
                $comandoObj->pid = $pid;
                $comandoObj->commandName = $commandName;
                $comandos[] = $comandoObj;
            }
        endforeach;


        /*$archivo = RUTA_CONSOLE . 'PID.txt';
        $manejador = @fopen($archivo, 'a');

        if ($manejador !== false) {
            // El archivo existe, escribir al final
            fwrite($manejador, '------------------ gean ---------------------------' . PHP_EOL);
            fwrite($manejador, $command . PHP_EOL);
            fwrite($manejador, '================' . PHP_EOL);
            fwrite($manejador, $pid_original . PHP_EOL);
            fwrite($manejador, '================' . PHP_EOL);
            fwrite($manejador, json_encode($comandos, JSON_PRETTY_PRINT) . PHP_EOL);

            // Cerrar el manejador de archivo
            fclose($manejador);
        } else {
            // El archivo no existe, crearlo y escribir con permisos 777
            $manejador = fopen($archivo, 'w');
            fwrite($manejador, '------------------------------------------------------' . PHP_EOL);
            fclose($manejador);

            // Asignar permisos 777 al archivo reciÃ©n creado
            chmod($archivo, 0777);
        }*/


        if ($comandos)
            die('ESTE PROCESO ESTA EJECUTANDOSE');

        return $comandos;
    }

    function nformat($number, $decimal = 9) {
        if (hasText($number, 'E') or hasText($number, '-'))
            return number_format($number, $decimal, '.', '');
        else
            return round($number, $decimal);
    }

    function extraer_dato($texto, $patron, $anterior = false) {
        $numero = '';
        if (preg_match($patron, $texto, $matches)):
            $numero = $matches[1] ?? 'no existe';
            $numero = ltrim(rtrim($numero));
        endif;

        if ($anterior):
            $numero = onlyReduceToOneSpace($numero);
            $numero = stringToArray($numero, ' ');
            $numero = end($numero);
            $numero = ltrim(rtrim($numero));
            return $numero;
        else:
            return $numero;
        endif;
    }


    function explorarJSON($datos) {
        $output = array();

        foreach ($datos as $clave => $valor) {
            if (hasText($clave, '_available_markets'))
                continue;
            #$valor = str_replace(["None", "'", '"[', ']"'], ['null', '"', '[', ']'], $valor);
            $valor_tmp = str_replace(["None", "'"], ['null', '"'], $valor);
            $valor_tmp = json_decode($valor_tmp);
            if ($valor_tmp != null)
                $valor_tmp = (array)$valor_tmp;

            $clave = str_replace('__', '_', $clave);
            if (is_array($valor_tmp)) {
                $output[$clave] = $valor_tmp;
            } else {
                $output[$clave] = $valor;
            }
        }

        return $output;
    }


    function rm_spaces_line($texto) {
        $patron = '/\((.*?)\)/s';

        $texto = preg_replace_callback($patron, function ($matches) {
            $contenido = trim(str_replace(["\r", "\n"], '', $matches[1]));
            return "($contenido)";
        }, $texto);


        return str_replace(["\r\n(", ";\r\n"], [" (", "; "], $texto);


        /*$patron = '\r\n(';

		return preg_replace_callback($patron, function ($matches) {
			$contenido = trim(str_replace(["\r\n("], ' (', $contenido));
			return "($contenido)";
		}, $texto);*/
    }

    function extraer_dato_all($texto, $patron) {
        preg_match_all($patron, $texto, $matches);
        $resultado = [];
        if (isset($matches[0])):
            $resultado = $matches[0];
            /*else:
				$resultado = []*/
        endif;
        $resultado = array_map("onlyReduceToOneSpace", $resultado);
        $activados_asociados = [];

        foreach ($resultado as $activado) {
            preg_match('/(\d+)\s*\(/', $activado, $matches);
            if (isset($matches[1])):
                $numero = $matches[1];
                $activados_asociados[$numero] = $activado;
            endif;
        }


        return $activados_asociados;

    }

    function array_clean_rn($valor) {
        foreach ($valor as $key => $m):
            #$valor[$key] = str_replace(array("\r", "\n", "<br>", "<br/>", "<br />"), '', strip_tags($m));
            #$valor[$key] = str_replace(array("<br>", "<br/>", "<br />"), '', strip_tags($m));
            #$valor[$key] = str_replace(array("\r", "\n"), '', strip_tags($m));
            #console($valor[$key]);
            #console('-----------------------------------------------------');
        endforeach;

        return $valor;
    }

    function array_replace_key($array, $clave = '', $nueva_clave = '') {
        $valor = $array[$clave];
        $claves = array_keys($array);
        $claves = array_replace($claves, array_fill(array_search($clave, $claves), 1, $nueva_clave));
        $array = array_combine($claves, array_values($array));
        $array[$nueva_clave] = $valor;
        return $array;
    }

    function array_json_combine($array, $nombre) {
        $array = (array)$array;
        if (json_decode($nombre) == null):
            $datosDecodificados = json_decode($array[$nombre]);
            unset($array[$nombre]);
        else:
            $datosDecodificados = json_decode($nombre);
        endif;
        return (object)array_merge((array)$array, (array)$datosDecodificados);
    }

    #function nformat($number) {
    #	if (hasText($number, 'E') or hasText($number, '-'))
    #		return number_format($number, 7, '.', '');
    #	else
    #		return round($number, 7);
    #}

    function getIpCliente() {

        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }

    function isAdmin($idroll) {
        if ($idroll > 2) {
            die(http_response_code(400));
        }
    }

    function encryptRuta($ruta) {
        $ruta = convertStringToArray($ruta, '/');
        $ruta2 = array_pop($ruta);
        $mt = substr($ruta2, 0, 2);
        $ruta = convertArrayToString($ruta, '/') . '/';
        $ruta .= $mt == 'mt' ? encrypt($ruta2) : $ruta2;
        return encrypt($ruta);
    }

    function url_actual() {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url = "https://";
        } else {
            $url = "http://";
        }
        return $url . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    function save_image($inPath, $outPath) { //Download images from remote server
        $in = fopen($inPath, "rb");
        $out = fopen($outPath, "wb");
        while ($chunk = fread($in, 8192)) {
            fwrite($out, $chunk, 8192);
        }
        fclose($in);
        fclose($out);
    }

    function texto_dimensions($texto, $tamano, $rutaFuente) {
        // Calcular el ancho
        $box = imagettfbbox($tamano, 0, $rutaFuente, $texto);
        $width = abs(abs($box[2]) - abs($box[0]));
        // Calcular el alto y la baseline
        $box = imagettfbbox($tamano, 0, $rutaFuente, 'ILyjgq'); // Tiene caracteres que no terminan en la baseline
        $height = abs($box[7] - $box[1]);
        $baseline = abs($box[5]);
        return array($width, $height, $baseline);
    }

    function truncateTxt($txt, $letra = '?') {
        $txt = str_split($txt);
        $texto = '';
        foreach ($txt as $m):
            if ($m == $letra):
                break;
            endif;
            $texto .= $m;
        endforeach;
        return $texto;
    }

    function rutanav($ruta = []) {
        $ruta1 = strtolower($ruta[0]);
        $vars = [];
        $rutaurl = convertArrayToString($ruta, '/');

        if ($ruta1 == 'dashboard' or $ruta1 == 'service') {
            if ($ruta1 == 'service') {
                $ruta[0] = 'dashboard';
            }
            $vars[] = $rutaurl;
            $vars[] = "<i class='icon-home'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'youtube') {
            $vars[] = $rutaurl;
            $vars[] = "<i class='icon-youtube'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'publishing') {
            $vars[] = $rutaurl;
            $vars[] = "<i class='icon-pencil'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'conexos') {
            $vars[] = $rutaurl;
            $vars[] = "<i class='fas fa-copyright'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'marketing') {
            $vars[] = $rutaurl;
            $vars[] = "<i class='fad fa-chart-line'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'comunity') {
            return false;
        }
        if ($ruta1 == 'contabilidad') {
            $vars[] = $rutaurl;
            $vars[] = "<i class='icon-coin-dollar'></i>";
            $vars[] = ucfirst($ruta[0]);
            return $vars;
        }
        if ($ruta1 == 'wallet') {
            return false;
        }

        return false;
    }

    function get_tipo_work_id($id) {
        $tipos = [
            1 => 'Catalogo',
            2 => 'Reporte',
            3 => 'Manual'
        ];
        return $tipos[$id];
    }

    function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function getVarsPost($vars, $slash = false) {
        $object = new stdClass();
        #if (isset($vars['q']))
        #    $object->q2 = stringOnlyTrim(strtolower(eliminartildes($vars['q'])));

        foreach ($vars as $key => $var) {
            $key = stringOnlyTrim($key);
            if (substr("$key", 0, 5) == 'data-'):
                $key = str_replace('data-', '', $key);
            endif;
            $key = str_replace('-', '', $key);

            $vartmp = (is_string($var) and is_array(json_decode($var, true))) ? (array)@json_decode($var) : '';

            $var = is_object($var) ? (array)$var : $var;
            if (is_array($var) or is_array($vartmp)):
                $var = is_array($vartmp) ? $vartmp : $var;
                $object->{$key} = getVarsPost($var, $slash = false);
            else:
                $var2 = $var;
                if (encrypt(decrypt($var), true) == str_replace('=', '', $var))
                    $var = decrypt($var);
                if (encrypt(encrypt(decrypt(decrypt($var2))), true) == str_replace('=', '', $var2))
                    $var = decrypt(decrypt($var2));
                $object->{$key} = $slash ? addslashes($var) : $var;
            endif;
        }
        if (isset($object->q))
            $object->q2 = stringOnlyTrim(strtolower(eliminartildes($object->q)));

        return $object;
    }

    function getSql($vars, $tabla, $insert = true) {
        $sql = [];
        foreach ($vars as $key => $var):
            if (hasText($key, 'amg')):
                $key = str_replace('amg', '', $key);
                $sql[] = "$key='$var'";
            endif;
        endforeach;
        $sql = convertArrayToString($sql, ',');
        if ($insert)
            return $sql = "INSERT INTO $tabla SET " . $sql;
        else
            return $sql = "UPDATE $tabla SET " . $sql;
    }

    function getCondicion($inputs, $operador = 'OR') {
        $data = [];
        foreach ($inputs as $key => $m):
            $key = $operador['clave'] ?? $key;
            $data[] = "$key='$m'";
        endforeach;


        $data = convertArrayToString($data, ' ' . ($operador['operador'] ?? $operador) . ' ');
        if (onlyTrim($data) != '')
            $data = " ($data) ";
        return $data;
    }

    function getVariables($inputs) {
        $data = [];
        foreach ($inputs as $key => $m):
            $m = addslashes(stringOnlyReduceToOneSpace($m));
            $data[] = "$key='$m'";
        endforeach;
        $data = convertArrayToString($data, ',');
        return $data;
    }

    function h_to_seconds($h) {
        $horas = $h[0] ?? 0;
        $minutos = $h[1] ?? 0;
        $segundos = $h[2] ?? 0;

        $horas = $horas * 60 * 60;
        $minutos *= 60;
        return $horas + $minutos + $segundos;
    }

    function ms_to_minute_seconds($milisegundos, $hora = true, $minuto = true, $segundo = true) {
        $input = $milisegundos;

        $uSec = $input % 1000;
        $input = floor($input / 1000);

        #SEGUNDOS
        $seconds = str_pad((string)($input % 60), 2, "0", STR_PAD_RIGHT);
        $input = floor($input / 60);

        #MINUTOS
        $minutes = str_pad((string)($input % 60), 2, "0", STR_PAD_LEFT);
        $input = floor($input / 60);

        #HORAS
        $input = str_pad((string)($input % 60), 2, "0", STR_PAD_LEFT);

        $tiempo = '';
        if ($hora)
            $tiempo .= "$input:";
        if ($minuto)
            $tiempo .= "$minutes:";
        if ($segundo)
            $tiempo .= "$seconds";
        #return "$input:$minutes:$seconds";
        return $tiempo;
    }

    function ms_to_minute($milisegundos) {
        $input = $milisegundos;

        $uSec = $input % 1000;
        $input = floor($input / 1000);

        #SEGUNDOS
        $seconds = str_pad((string)($input % 60), 2, "0", STR_PAD_RIGHT);
        $input = floor($input / 60);

        #MINUTOS
        $minutes = str_pad((string)($input % 60), 2, "0", STR_PAD_LEFT);
        $input = floor($input / 60);

        $minutes = (intval($minutes) * 60) + intval($seconds);
        #HORAS

        return $minutes;
    }

    function ddex_ms_to_minute_seconds($milisegundos) {
        $input = $milisegundos;

        $uSec = $input % 1000;
        $input = floor($input / 1000);

        #SEGUNDOS
        $seconds = ($input % 60);
        $input = floor($input / 60);

        #MINUTOS
        $minutes = ($input % 60);
        $input = floor($input / 60);

        #HORAS
        $input = str_pad((string)($input % 60), 2, "0", STR_PAD_LEFT);
        return "PT$minutes" . "M:$seconds" . "S";
    }


    function getInputs($inputs, $dencrypt_key = false, $dencrypt_value = false, $comprime_espace = true) {
        $data = [];
        $vacios = 0;
        foreach ($inputs as $key => $m):

            $decrypt_value_ = false;
            if ($dencrypt_key):
                $key = decrypt($key);
                $null = strtolower(substr($key, strlen($key) - 5, strlen($key)));


                $decrypt_ = strtolower(substr($key, 0, 8));

                if ($null == '_null'):
                    $key = substr($key, 0, strlen($key) - 5);
                endif;

                if ($decrypt_ == 'decrypt_'):
                    $key = substr($key, 8);
                    $decrypt_value_ = true;
                endif;
            else:
                $null = strtolower(substr($key, strlen($key) - 5, strlen($key)));
                if ($null == '_null'):
                    $key = substr($key, 0, strlen($key) - 5);
                endif;


                $decrypt_ = strtolower(substr($key, 0, 8));
                if ($decrypt_ == 'decrypt_'):
                    $key = substr($key, 8);
                    $decrypt_value_ = true;
                endif;

            endif;
            /*prettyPrint($key);*/
            if ($dencrypt_value):
                if (is_array($m)):
                    $required = array_key_first($m);
                    $m = decrypt(array_shift($m));
                    if ($required == 'required'):
                        if ($m == ''):
                            $vacios++;
                            echo "<script>$('*[name=\'inputs[" . encrypt($key) . "][required]\']').attr('valid','false')</script>";
                            echo "<script>$('*[name=\'inputs[" . encrypt($key) . "][required]\']').attr('required','required')</script>";
                        endif;
                    endif;
                else:
                    $m = decrypt($m);
                endif;
            else:
                if (is_array($m)):
                    $required = array_key_first($m);
                    $m = array_shift($m);
                    if ($required == 'required'):
                        if ($m == ''):
                            $vacios++;
                            echo "<script>$('*[name=\'inputs[" . encrypt($key) . "][required]\']').attr('valid','false')</script>";
                            echo "<script>$('*[name=\'inputs[" . encrypt($key) . "][required]\']').attr('required','required')</script>";
                        endif;
                    endif;
                    /* else:
                             $m = decrypt($m);*/
                endif;
            endif;

            if ($decrypt_value_):
                $m = decrypt($m);
            endif;
            if ($comprime_espace):
                $m = stringOnlyReduceToOneSpace($m);
            endif;
            if (hasText($m, 'IF(')):
                $m = slashesSQLIF($m);
                $data[] = "$key=$m";
            else:
                $m = addslashes($m);
                if ($null == '_null'):
                    $data[] = "$key=NULLIF('$m','')";
                else:
                    $data[] = "$key='$m'";
                endif;
            endif;
        endforeach;
        if ($vacios > 0) {
            die();
        }
        $data = convertArrayToString($data, ',');
        return $data;
    }

    function slashesSQLIF($valor) {
        $posicion_primera_coma = strpos($valor, ",");
        $posicion_ultima_coma = strrpos($valor, ",");
        $texto_entre_comas1 = substr($valor, $posicion_primera_coma + 1, $posicion_ultima_coma - $posicion_primera_coma - 1);
        $texto_entre_comas = trim($texto_entre_comas1, "'");
        $texto_entre_comas = addslashes($texto_entre_comas);
        $valor = str_replace("$texto_entre_comas1", "'$texto_entre_comas'", $valor);
        return $valor;
    }

    $_GET['encode'] = 0;
    function ecd() {
        $num = $_GET['encode'];
        $num++;
        $_GET['encode'] = $num;
        $num = 'ab' . $num . 'cd';
        $num = str_split($num);
        shuffle($num);
        /* prettyPrint($num);*/
        $num = convertArrayToString($num, '');
        $num = encrypt($num);
        $num = str_replace('=', '', $num);
        return $num;
    }


    function getAhorro($oferta = null) {
        if ($oferta == null):
            return 0.00;
        endif;
        $oferta = ((($oferta->creditos - $oferta->precio) * 100) / $oferta->precio);
        return round($oferta, 2);
    }

    function rmdir_rf($carpeta) {
        foreach (glob($carpeta . "/*") as $archivos_carpeta) {
            if (is_dir($archivos_carpeta)) {
                rmdir_rf($archivos_carpeta);
            } else {
                unlink($archivos_carpeta);
            }
        }
        rmdir($carpeta);
    }

    function getMsgTrigger($txt, $arg = '*') {
        $txt = convertStringToArray($txt, $arg);
        return $txt[1];
    }

    function getMinutos($tiempo) {
        $tiempo = round($tiempo, 2);
        $min = round($tiempo / 60);
        return $min;
    }

    function getSegundos($tiempo) {
        $tiempo = round($tiempo, 2);
        $seg = round($tiempo % 60);
        return $seg;
    }

    function botonenlace($contenido, $tipo) {
        $datos = array();
        $enalce = $contenido;
        switch ($tipo) {
            case 1:
                $enalce = $contenido;
                break;
            case 2:
                $enalce = 'https://wa.me/' . $contenido;
                break;
            case 3:
                $enalce = 'mailto: ' . $contenido;
                break;
            case 4:
                $enalce = 'tel: ' . $contenido;
                break;
        }
        return $enalce;
    }

    function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    function callporciento($m, $isrecaudo = false) {
        $de = $m->comision + $m->interes;
        $de = 1 - ($de / 100);

        $impuesto_relativo = ((($m->ingreso_impuesto * 100) / ($m->ingreso * $de)) / 100);
        $impuesto_relativo = is_nan($impuesto_relativo) ? 0 : $impuesto_relativo;

        $descuentos = (1 - (($m->comision + $m->interes) / 100));

        $descuentos = $descuentos * (1 - $impuesto_relativo);

        if ($isrecaudo):
            $descuentos = $descuentos * (1 - ($m->recaudo / 100));
        endif;

        return $descuentos;
    }

    function calMonto($comision, $impuesto, $interes, $recaudo) {
        $descuentos = (1 - ($comision + $interes) / 100);
        $descuentos = ($descuentos) * (1 - ($impuesto / 100));
        /*$descuentos = $descuentos * (1-($recaudo/100));*/
        return $descuentos;
    }

    function calDesPor($comision, $impuesto, $interes, $recaudo) {
        $descuentos = (1 - (($comision + $interes) / 100));
        $descuentos = $descuentos * (1 - ($impuesto / 100));
        $descuentos = $descuentos * (1 - ($recaudo / 100));
        return $descuentos;
    }

    function calPorHijo($porcentaje, $comision, $impuesto, $interes, $recaudo) {
        $porcentaje_user = (($porcentaje * (1 - (($comision + $interes) / 100))) * (1 - ($impuesto / 100))) * (1 - ($recaudo / 100));
        return $porcentaje_user;
    }

    function calDescuentos($ingreso, $comision, $impuesto, $interes, $recaudo) {
        $des_1 = $ingreso * (($comision + $interes) / 100);
        $des_2 = ($ingreso * (1 - (($comision + $interes) / 100))) * ($impuesto / 100);
        $des_3 = (($ingreso * (1 - (($comision + $interes) / 100))) * (1 - ($impuesto / 100)) * ($recaudo / 100));
        return ($des_1 + $des_2 + $des_3);
    }


    function arrayDescuento($ingreso, $comision, $impuesto, $interes, $recaudo) {
        $row = [];
        $row[] = $ingreso * ($comision / 100);
        $row[] = ($ingreso * (1 - (($comision + $interes) / 100))) * ($impuesto / 100);
        $row[] = $ingreso * ($interes / 100);
        $row[] = calRecaudo($ingreso, $comision, $impuesto, $interes, $recaudo);


        return $row;
    }

    function contar($data) {
        return count($data);
    }

    function calRecaudo($ingreso, $comision, $impuesto, $interes, $recaudo) {
        $descuentos = $ingreso * (1 - ($comision + $interes) / 100);
        $descuentos = ($descuentos) * (1 - ($impuesto / 100));
        $descuentos = $descuentos * ($recaudo / 100);

        return $descuentos;
    }

    function getRecaudo_in($ingreso, $comision, $impuesto, $interes, $recaudo, $descuento_regalia) {
        $descuentos = $ingreso * (1 - ($comision + $interes) / 100);
        $descuentos = ($descuentos) * (1 - ($impuesto / 100));
        $descuentos -= $descuento_regalia;
        $descuentos = $descuentos * ($recaudo / 100);

        return $descuentos;
    }

    function money($n, $decimales = 2) {
        if (!is_numeric($n))
            return '0.00';
        $m = number_format($n, $decimales, '.', ',');
        return $m;
    }

    function getRootDirectoryTime() {
        $date = new DateTime();
        $dateString = $date->format('Y-m-d\TH:i:sP');
        #return (new \DateTime())->format('Y-m-d\TH:i:s.u');
        return $dateString;
    }

    function getRootDirectory() {
        $currentDateTime = new DateTime();
        $dateString = $currentDateTime->format('YmdHisu') . substr(microtime(), 2, 6);
        $dateString = onlyNumber($dateString);
        $dateString = substr($dateString, 0, 17);
        return onlyTrim(onlyNumber($dateString));
    }

    function porciento($n, $simbolo = false, $adicional = '', $decimales = 2) {
        if (!is_numeric($n))
            return '';


        $m = number_format($n, $decimales, '.', '');

        if ($adicional != '')
            $m = str_replace(' ', $m, $adicional);

        if ($simbolo == true)
            $m = "$m %";
        return $m;
    }

    function sum_colum($datos, $columna = '', $moneda = false) {
        $m = array_sum(array_column($datos, $columna));
        if ($moneda):
            $m = money($m);
        endif;
        return $m;
    }

    function getCm($px) {
        $cm = $px / 36.7957517575;
        return $cm;
    }

    function formato($fecha, $formato = 'Y-m-d') {
        $fecha = new DateTime("$fecha");
        if ($formato != 'Y-m-d'):
            return $fecha->format('$formato');
        else:
            return $fecha->format('Y-m-d');
        endif;
    }

    function getperiodo() {
        setlocale(LC_ALL, "es_ES");
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");

        return "$anio-$mes-00";
    }

    function getFecha() {
        setlocale(LC_ALL, "es_ES");
        $dia = date("d");
        $mes = date("m");
        $anio = date("Y");

        return "$anio-$mes-$dia";
    }

    function abreviaturaMes($mes) {
        $meses = array("EN", "FEBR", "MZO", "ABR", "MY", "JUN", "JUL", "AGTO", "SPTt", "OCT", "NOV", "DIC");

        return $meses[$mes];
    }

    function nombreMes($mes, $minuscula = false) {
        $meses = array('ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE');
        $mes = $meses[$mes];

        if ($minuscula):
            $mes = ucfirst(strtolower($mes));
        endif;

        return $mes;
    }

    function valFecha($fecha) {
        if (stringOnlyTrim($fecha) == '')
            return 0;
        return intval(onlyNumber($fecha));
    }

    function fechaEspaÃ±ol($mes, $formato = 'd, M Y') {
        if (hasText($mes, '-00') and valFecha($mes) > 0)
            $mes = substr($mes, 0, -1) . '1';
        if (valFecha($mes) > 0):
            $fecha = date("$formato", strtotime($mes));
            $mes = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $mesremplace = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $fecha = str_replace($mes, $mesremplace, $fecha);
            return $fecha;
        else:
            return '';
        endif;
    }


    function getValueFecha($fecha) {
        $meses_ingles = array(
            '01' => "January",
            '02' => "February",
            '03' => "March",
            '04' => "April",
            '05' => "May",
            '06' => "June",
            '07' => "July",
            '08' => "August",
            '09' => "September",
            '10' => "October",
            '11' => "November",
            '12' => "December"
        );
        return buscarPositionInArray($fecha, $meses_ingles);
    }

    function getValuePais($pais) {
        $paises = array(
            "AfganistÃ¡n" => "AF",
            "Islas Gland" => "AX",
            "Albania" => "AL",
            "Alemania" => "DE",
            "Andorra" => "AD",
            "Angola" => "AO",
            "Anguilla" => "AI",
            "AntÃ¡rtida" => "AQ",
            "Antigua y Barbuda" => "AG",
            "Antillas Holandesas" => "AN",
            "Arabia SaudÃ­" => "SA",
            "Argelia" => "DZ",
            "Argentina" => "AR",
            "Armenia" => "AM",
            "Aruba" => "AW",
            "Australia" => "AU",
            "Austria" => "AT",
            "AzerbaiyÃ¡n" => "AZ",
            "Bahamas" => "BS",
            "BahrÃ©in" => "BH",
            "Bangladesh" => "BD",
            "Barbados" => "BB",
            "Bielorrusia" => "BY",
            "BÃ©lgica" => "BE",
            "Belice" => "BZ",
            "Benin" => "BJ",
            "Bermudas" => "BM",
            "BhutÃ¡n" => "BT",
            "Bolivia" => "BO",
            "Bosnia y Herzegovina" => "BA",
            "Botsuana" => "BW",
            "Isla Bouvet" => "BV",
            "Brasil" => "BR",
            "BrunÃ©i" => "BN",
            "Bulgaria" => "BG",
            "Burkina Faso" => "BF",
            "Burundi" => "BI",
            "Cabo Verde" => "CV",
            "Islas CaimÃ¡n" => "KY",
            "Camboya" => "KH",
            "CamerÃºn" => "CM",
            "CanadÃ¡" => "CA",
            "RepÃºblica Centroafricana" => "CF",
            "Chad" => "TD",
            "RepÃºblica Checa" => "CZ",
            "Chile" => "CL",
            "China" => "CN",
            "Chipre" => "CY",
            "Isla de Navidad" => "CX",
            "Ciudad del Vaticano" => "VA",
            "Islas Cocos" => "CC",
            "Colombia" => "CO",
            "Comoras" => "KM",
            "RepÃºblica DemocrÃ¡tica del Congo" => "CD",
            "Congo" => "CG",
            "Islas Cook" => "CK",
            "Corea del Norte" => "KP",
            "Corea del Sur" => "KR",
            "Costa de Marfil" => "CI",
            "Costa Rica" => "CR",
            "Croacia" => "HR",
            "Cuba" => "CU",
            "Dinamarca" => "DK",
            "Dominica" => "DM",
            "RepÃºblica Dominicana" => "DO",
            "Ecuador" => "EC",
            "Egipto" => "EG",
            "El Salvador" => "SV",
            "Emiratos Ãrabes Unidos" => "AE",
            "Eritrea" => "ER",
            "Eslovaquia" => "SK",
            "Eslovenia" => "SI",
            "EspaÃ±a" => "ES",
            "Islas ultramarinas de Estados Unidos" => "UM",
            "Estados Unidos" => "US",
            "Estonia" => "EE",
            "EtiopÃ­a" => "ET",
            "Islas Feroe" => "FO",
            "Filipinas" => "PH",
            "Finlandia" => "FI",
            "Fiyi" => "FJ",
            "Francia" => "FR",
            "GabÃ³n" => "GA",
            "Gambia" => "GM",
            "Georgia" => "GE",
            "Islas Georgias del Sur y Sandwich del Sur" => "GS",
            "Ghana" => "GH",
            "Gibraltar" => "GI",
            "Granada" => "GD",
            "Grecia" => "GR",
            "Groenlandia" => "GL",
            "Guadalupe" => "GP",
            "Guam" => "GU",
            "Guatemala" => "GT",
            "Guayana Francesa" => "GF",
            "Guinea" => "GN",
            "Guinea Ecuatorial" => "GQ",
            "Guinea-Bissau" => "GW",
            "Guyana" => "GY",
            "HaitÃ­" => "HT",
            "Islas Heard y McDonald" => "HM",
            "Honduras" => "HN",
            "Hong Kong" => "HK",
            "HungrÃ­a" => "HU",
            "India" => "IN",
            "Indonesia" => "ID",
            "IrÃ¡n" => "IR",
            "Iraq" => "IQ",
            "Irlanda" => "IE",
            "Islandia" => "IS",
            "Israel" => "IL",
            "Italia" => "IT",
            "Jamaica" => "JM",
            "JapÃ³n" => "JP",
            "Jordania" => "JO",
            "KazajstÃ¡n" => "KZ",
            "Kenia" => "KE",
            "KirguistÃ¡n" => "KG",
            "Kiribati" => "KI",
            "Kuwait" => "KW",
            "Laos" => "LA",
            "Lesotho" => "LS",
            "Letonia" => "LV",
            "LÃ­bano" => "LB",
            "Liberia" => "LR",
            "Libia" => "LY",
            "Liechtenstein" => "LI",
            "Lituania" => "LT",
            "Luxemburgo" => "LU",
            "Macao" => "MO",
            "ARY Macedonia" => "MK",
            "Madagascar" => "MG",
            "Malasia" => "MY",
            "Malawi" => "MW",
            "Maldivas" => "MV",
            "MalÃ­" => "ML",
            "Malta" => "MT",
            "Islas Malvinas" => "FK",
            "Islas Marianas del Norte" => "MP",
            "Marruecos" => "MA",
            "Islas Marshall" => "MH",
            "Martinica" => "MQ",
            "Mauricio" => "MU",
            "Mauritania" => "MR",
            "Mayotte" => "YT",
            "MÃ©xico" => "MX",
            "Micronesia" => "FM",
            "Moldavia" => "MD",
            "MÃ³naco" => "MC",
            "Mongolia" => "MN",
            "Montserrat" => "MS",
            "Mozambique" => "MZ",
            "Myanmar" => "MM",
            "Namibia" => "NA",
            "Nauru" => "NR",
            "Nepal" => "NP",
            "Nicaragua" => "NI",
            "NÃ­ger" => "NE",
            "Nigeria" => "NG",
            "Niue" => "NU",
            "Isla Norfolk" => "NF",
            "Noruega" => "NO",
            "Nueva Caledonia" => "NC",
            "Nueva Zelanda" => "NZ",
            "OmÃ¡n" => "OM",
            "PaÃ­ses Bajos" => "NL",
            "PakistÃ¡n" => "PK",
            "Palau" => "PW",
            "Palestina" => "PS",
            "PanamÃ¡" => "PA",
            "PapÃºa Nueva Guinea" => "PG",
            "Paraguay" => "PY",
            "PerÃº" => "PE",
            "Islas Pitcairn" => "PN",
            "Polinesia Francesa" => "PF",
            "Polonia" => "PL",
            "Portugal" => "PT",
            "Puerto Rico" => "PR",
            "Qatar" => "QA",
            "Reino Unido" => "GB",
            "ReuniÃ³n" => "RE",
            "Ruanda" => "RW",
            "Rumania" => "RO",
            "Rusia" => "RU",
            "Sahara Occidental" => "EH",
            "Islas SalomÃ³n" => "SB",
            "Samoa" => "WS",
            "Samoa Americana" => "AS",
            "San CristÃ³bal y Nevis" => "KN",
            "San Marino" => "SM",
            "San Pedro y MiquelÃ³n" => "PM",
            "San Vicente y las Granadinas" => "VC",
            "Santa Helena" => "SH",
            "Santa LucÃ­a" => "LC",
            "Santo TomÃ© y PrÃ­ncipe" => "ST",
            "Senegal" => "SN",
            "Serbia y Montenegro" => "CS",
            "Seychelles" => "SC",
            "Sierra Leona" => "SL",
            "Singapur" => "SG",
            "Siria" => "SY",
            "Somalia" => "SO",
            "Sri Lanka" => "LK",
            "Suazilandia" => "SZ",
            "SudÃ¡frica" => "ZA",
            "SudÃ¡n" => "SD",
            "Suecia" => "SE",
            "Suiza" => "CH",
            "Surinam" => "SR",
            "Svalbard y Jan Mayen" => "SJ",
            "Tailandia" => "TH",
            "TaiwÃ¡n" => "TW",
            "Tanzania" => "TZ",
            "TayikistÃ¡n" => "TJ",
            "Territorio BritÃ¡nico del OcÃ©ano Ãndico" => "IO",
            "Territorios Australes Franceses" => "TF",
            "Timor Oriental" => "TL",
            "Togo" => "TG",
            "Tokelau" => "TK",
            "Tonga" => "TO",
            "Trinidad y Tobago" => "TT",
            "TÃºnez" => "TN",
            "Islas Turcas y Caicos" => "TC",
            "TurkmenistÃ¡n" => "TM",
            "TurquÃ­a" => "TR",
            "Tuvalu" => "TV",
            "Ucrania" => "UA",
            "Uganda" => "UG",
            "Uruguay" => "UY",
            "UzbekistÃ¡n" => "UZ",
            "Vanuatu" => "VU",
            "Venezuela" => "VE",
            "Vietnam" => "VN",
            "Islas VÃ­rgenes BritÃ¡nicas" => "VG",
            "Islas VÃ­rgenes de los Estados Unidos" => "VI",
            "Wallis y Futuna" => "WF",
            "Yemen" => "YE",
            "Yibuti" => "DJ",
            "Zambia" => "ZM",
            "Zimbabue" => "ZW",
        );
        return (buscarPositionInArray($pais, $paises) ? buscarPositionInArray($pais, $paises) : $pais);
    }

    function div_array_limit($array, $div) {
        return array_chunk($array, $div);
    }

    function desformat($n) {
        $num = '';
        for ($i = 0; $i < strlen($n); $i++) {
            if (is_numeric($n[$i]) or $n[$i] == '.')
                $num .= $n[$i];
        }

        return $num;
    }

    function formarCUPON($n) {
        $num = preg_replace('/\P{L}/', '', $n);
        return $num;
    }


    function val($d) {
        if ($d == '') {
            return 0.00;
        } else {
            return $d;
        }
    }

    function isempty($dato) {
        if ($dato)
            return $dato;
        else
            return '';
    }

    function r($b) {
        $n = round($b, 4);
        return $n;
    }

    function buscarPositionInArray($valor, $array) {
        if (in_array($valor, $array))
            $position = array_search($valor, $array);
        else
            $position = false;

        return $position;
    }

    function buscarValueInArray($valor, $arreglo) {
        if (in_array($valor, $arreglo)) {
            $position = array_search($valor, $arreglo);
            $value = $arreglo[$position];
        } else {
            $value = false;
        }
        return $value;
    }


    function code($data) {
        return base64_encode(base64_encode($data));
    }

    function decode($data) {
        return base64_decode(base64_decode($data));
    }

    function array_orden($array, $colum = '', $orden = 'DESC') {
        usort($array, object_sorter($colum, $orden));
        return $array;
    }

    function object_sorter($clave, $orden = 'DESC') {
        return function ($a, $b) use ($clave, $orden) {
            $result = ($orden == "DESC") ? strnatcmp($b->$clave, $a->$clave) : strnatcmp($a->$clave, $b->$clave);
            return $result;
        };
    }

    #16

    function quarter($time, $fill = false) {
        if (!is_numeric($time))
            $time = strtotime($time);

        $curMonth = date("m", $time);
        $curMonth = ceil($curMonth / 3);
        if ($fill)
            $curMonth = zerofill($curMonth, 2);
        return $curMonth;
    }

    function removeChart($text = '', $string = '=') {
        return str_replace("$string", '', "$text");
    }

    function clsHash($ids, $single = 0) {
        $ids = str_replace(' ', '+', $ids);
        if ($single == 0):
            $ids = hashMd5($ids, false);
            $ids = str_replace('/', '-', "$ids");
            $ids = convertStringToArray($ids, '-');
        endif;
        return $ids;
    }

    function hashMd5($dato, $encrypt = true) {

        $clave = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

        $method = 'aes-256-cbc';

        $iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
        #$iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

        $encriptar = function ($valor) use ($method, $clave, $iv) {
            return openssl_encrypt($valor, $method, $clave, false, $iv);
        };

        $desencriptar = function ($valor) use ($method, $clave, $iv) {
            $encrypted_data = base64_decode($valor);
            return openssl_decrypt($valor, $method, $clave, false, $iv);
        };

        $getIV = function () use ($method) {
            return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));
        };

        if ($encrypt):
            $datos = $encriptar($dato);
            $datos = str_replace('/', '_', $datos);
            $datos = str_replace('+', '-', $datos);
            $datos = str_replace('=', '', $datos);
            return $datos;
        else:
            $datos = str_replace('_', '/', $dato);
            $datos = str_replace('-', '+', $datos);
            $datos = $desencriptar($datos);
            return $datos;
        endif;
    }

    function toignore() {
        return encrypt('toignore');
    }

    function hashMd5_2($dato, $encrypt = true) {
        return hashMd5($dato, $encrypt);
#
        #$clave = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';
#
        #$method = 'aes-256-cbc';
#
        #$iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
        ##$iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
#
        #$encriptar = function ($valor) use ($method, $clave, $iv) {
        #	return openssl_encrypt($valor, $method, $clave, false, $iv);
        #};
#
        #$desencriptar = function ($valor) use ($method, $clave, $iv) {
        #	$encrypted_data = base64_decode($valor);
        #	return openssl_decrypt($valor, $method, $clave, false, $iv);
        #};
#
        #$getIV = function () use ($method) {
        #	return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));
        #};
#
        #if ($encrypt):
        #	$datos = $encriptar($dato);
        #	$datos = str_replace('/', '_', $datos);
        #	$datos = str_replace('+', '-', $datos);
        #	$datos = str_replace('=', '', $datos);
        #	return $datos;
        #else:
        #	$datos = str_replace('_', '/', $dato);
        #	$datos = str_replace('-', '+', $datos);
        #	$datos = $desencriptar($datos);
        #	return $datos;
        #endif;
    }


    function getMpago($metodo, $objeto) {
        $cuenta = '';
        if ($metodo == 'WireTransfer'):
            $cuenta = $objeto->bank_name . '-' . $objeto->account_number;
        elseif ($metodo == 'PayPal'):
            $cuenta = $objeto->ewallet_account;
        elseif ($metodo == 'ACH'):
            $cuenta = $objeto->bank_name . '-' . $objeto->account_number;
        elseif ($metodo == 'eCheck'):
            $cuenta = $objeto->iban;
        elseif ($metodo == 'NoPM'):
            $cuenta = 'Editar';
        endif;
        return $cuenta;
    }

    function visorExcel($titulo, $archivo) {
        echo "<!DOCTYPE html>
              <html lang='es'>
                  <head>
                      <meta charset='UTF-8'>
                      <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                      <title>$titulo</title>
                      <style>
                        .contenedor-embed {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            height: 100vh; 
                            background-color: black;  
                            margin: 0; /* Eliminar cualquier margen */
                            padding: 0; /* Eliminar cualquier relleno */
                            overflow: hidden; /* Evitar barras de desplazamiento */
                        }
    
                       embed, iframe {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            background: #000 !important;
                            position: absolute;
                            height: 100%;
                            width: 100%;
                            margin: 0; /* Eliminar cualquier margen */
                            padding: 0; /* Eliminar cualquier relleno */
                        }
                         body, html {
                            height: 100%;
                            margin: 0;
                            overflow: hidden; /* Evitar barras de desplazamiento */
                        }
                    </style>
                  </head>
                <body style='padding: 0;margin: 0;'>
                    <div class='contenedor-embed'>
                        <iframe src='https://view.officeapps.live.com/op/view.aspx?src=$archivo'></iframe>
                  </div>
                </body>
              </html>";
        die();
    }

    function visorJson($titulo, $archivo) {
        header('Content-Type: text/html; charset=utf-8');
        $json = file_get_contents($archivo);
        $jsonArray = json_decode($json, true);
        $type = getContentType($archivo);

        echo "<!DOCTYPE html>
          <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>$titulo</title>
                  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/styles/default.min.css'>
                  <script src='https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.3.1/highlight.min.js'></script>
                  <style>
                    .contenedor-embed {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh; 
                        margin: 0; /* Eliminar cualquier margen */
                        padding: 0; /* Eliminar cualquier relleno */
                        overflow: hidden; /* Evitar barras de desplazamiento */
                    }
                    embed, iframe {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        background: #000 !important;
                        position: absolute;
                        height: 100%;
                        width: 100%;
                        margin: 0; /* Eliminar cualquier margen */
                        padding: 0; /* Eliminar cualquier relleno */
                    }
                     body, html {
                        height: 100%;
                        margin: 0;
                        overflow: hidden; /* Evitar barras de desplazamiento */
                    }
                    pre {
                        white-space: pre-wrap; /* Conserva los espacios y los saltos de lÃ­nea */
                    }
                  </style>
              </head>
            <body style='padding: 0;margin: 0;'>
                <div class='contenedor-embed'>
                    <pre style='overflow-y: scroll; height: 100%;' id='json-container'>$json</pre>
                </div>
                <script>
                        //hljs.highlightBlock(document.getElementById('json-container'));
                </script>
            </body>
          </html>";
        die();
    }

    function publishing_porcentaje_servicio($idempresa) {
        #if ($idempresa == 118 or $idempresa == 125):
        if ($idempresa == 118):
            return "ROUND(COALESCE(c.split_porcentaje_propiedad_editora,0), 2) ";
        elseif ($idempresa == 117):
            #return "ROUND(COALESCE(c.split_porcentaje_isrc,0), 2) ";
            return "ROUND(COALESCE(c.split_porcentaje_propiedad_bmi,0), 2) ";
        else:
            return "ROUND(COALESCE(c.split_porcentaje_propiedad,0), 2) ";
        endif;
    }

    function decrypt($string) {
        $string = strrev($string);
        $string = base64_decode(base64_decode($string));
        #$string = str_replace("=", '', "$string");
        return $string;
    }

    function encrypt($string, $remove = false) {
        $string = base64_encode(base64_encode($string));
        $string = str_replace("=", '', "$string");
        $string = strrev($string);
        return $string;
    }

    function mysql_txt($text) {
        $text = addslashes($text);
        $text = str_replace("'", "\'", $text);
        $text = str_replace('â€œ', '\"', $text);
        $text = str_replace('â€', '\"', $text);
        $text = str_replace('â€œ', '\"', $text);
        $text = str_replace('â€™', "\'", $text);
        return $text;
    }

    function getKeys($array) {
        $t = get_object_vars($array);
        $t = json_encode(array_keys($t));
        $t = strval($t);
        $dato = '';
        for ($i = 0; $i < strlen($t); $i++) {
            if ($t[$i] != '"' and $t[$i] != '[' and $t[$i] != ']')
                $dato .= $t[$i];
        }
        $dato = str_replace("'", "[", $dato);
        $dato = str_replace("'", "]", $dato);
        return $dato;
    }

    function getValues($array) {
        $t = get_object_vars($array);
        $t = json_encode(array_values($t));
        $t = strval($t);
        $dato = '';
        for ($i = 0; $i < strlen($t); $i++) {
            if ($t[$i] != '"' and $t[$i] != '[' and $t[$i] != ']')
                $dato .= $t[$i];
        }
        $dato = str_replace("'", "[", $dato);
        $dato = str_replace("'", "]", $dato);
        return $dato;
    }

    function getKeysObject($array, $valor) {
        $row = [];
        foreach ($array as $key1 => $x):
            foreach ($x as $key2 => $y):
                if ($key2 == $valor):
                    $row[] = $y;
                endif;
            endforeach;
        endforeach;

        /*$t = get_object_vars($row);*/
        $t = json_encode(array_keys($row));
        $t = strval($t);
        $dato = '';
        for ($i = 0; $i < strlen($t); $i++) {
            if ($t[$i] != '"' and $t[$i] != '[' and $t[$i] != ']')
                $dato .= $t[$i];
        }
        $dato = str_replace("'", "[", $dato);
        $dato = str_replace("'", "]", $dato);
        return $dato;
    }


    function getValuesObject($array, $valor, $mes = null) {
        $row = [];
        foreach ($array as $key1 => $x):
            foreach ($x as $key2 => $y):
                if ($key2 == $valor):
                    if ($mes == 'mes'):
                        $y[(countTxt($y) - 1)] = '1';
                        $row[] = $periodo = getMes(date('F', strtotime($y)));
                    else:
                        $row[] = $y;
                    endif;
                endif;
            endforeach;
        endforeach;

        /* $t = get_object_vars($row);*/
        $t = json_encode(array_values($row));
        $t = strval($t);
        $dato = '';
        for ($i = 0; $i < strlen($t); $i++) {
            if ($t[$i] != '"' and $t[$i] != '[' and $t[$i] != ']')
                $dato .= $t[$i];
        }
        $dato = str_replace("'", "[", $dato);
        $dato = str_replace("'", "]", $dato);
        return $dato;
    }

    function rmvcoma($txt) {
        $txt = substr($txt, 0, -1);
        return $txt;
    }

    function div_array($array) {
        $num = count($array);
        $num = round(($num / 2));
        $arrays = array_chunk($array, $num);
        return $arrays;
    }

    function rmv_char_last($str) {
        $n = (strlen($str) - 1);
        $str[$n] = ' ';
        return $str;
    }

    function trm($txt) {
        for ($i = 0; $i < strlen($txt); $i++) {
            if ($txt[$i] == '"' or $txt[$i] == 'Â´')
                $txt[$i] = "'";
        }
        $txt = str_replace(' ', '', $txt);
        return $txt;
    }

    function clsPDFline($linea, $tab = '====') {
        $linea = ltrim(rtrim($linea));
        $linea = str_replace(' ', '/*', $linea);
        $linea = str_replace('/*/*', ' ', $linea);
        $linea = stringOnlyReduceToOneSpace($linea);
        $linea = str_replace(' ', $tab, $linea);
        $linea = str_replace('/*', ' ', $linea);
        $linea = ltrim(rtrim($linea));
        return $linea;
    }

    function search_file($dir, $file_to_search) {
        $result = [];
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            if (is_file("$dir/$value")):
                if (hasText("$value", "$file_to_search")):
                    $result[] = $value;
                endif;
            endif;
        }
        return $result;
    }

    /*ARRAY PHP*/
    function stringOnlySqlScape($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = addslashes($txt);
        return $txt;
    }

    function getHash($tipo = '') {
        $hash = hash('sha256', strrev(getSerie()));
        if ($tipo != '')
            $tipo = $tipo . "_";

        return "$tipo$hash";
    }

    function getSerie() {
        $microsegundos = microtime(true);
        $fechaHoraActual = date('YmdHis') . str_replace('.', '', sprintf('%0.6f', $microsegundos));
        $fechaHoraActual = onlyNumber($fechaHoraActual);
        $fechaHoraActual = substr($fechaHoraActual, 0, 17);
        return $fechaHoraActual;
    }

    function getCode() {
        $microsegundos = microtime(true);
        $fechaHoraActual = date('YmdHis') . str_replace('.', '', sprintf('%0.6f', $microsegundos));
        $fechaHoraActual = onlyNumber($fechaHoraActual);
        return $fechaHoraActual;
    }

    function periodoAdd($periodo, $meses) {
        if ($meses > 0)
            $meses = "+$meses";
        else
            $meses = "-$meses";

        $timestamp = strtotime($periodo);
        $nuevaFechaTimestamp = strtotime("$meses months", $timestamp);
        return date("Y-m", $nuevaFechaTimestamp);
    }

    function stringOnlyTrim($txt, $mayuscula = null) {
        return onlyTrim($txt, $mayuscula);
    }

    function ucfirst_text($texto) {
        return ucwords(strtolower($texto));
    }

    function onlyTrim($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = str_replace(' ', '', $txt);
        $txt = ltrim(rtrim($txt));
        return $txt;
    }

    function formateSql($sql) {
        $sql = onlyReduceToOneSpace($sql);
        $sql = preg_replace('/\b' . preg_quote('and and') . '\b/i', 'AND', $sql);
        $sql = preg_replace('/\b' . preg_quote('and order') . '\b/i', 'ORDER', $sql);
        $sql = preg_replace('/\b' . preg_quote('where where') . '\b/i', 'WHERE', $sql);
        $sql = preg_replace('/\b' . preg_quote('and limit') . '\b/i', 'LIMIT', $sql);
        $sql = preg_replace('/\b' . preg_quote('where and') . '\b/i', 'WHERE', $sql);

        $pattern = '/ONLY_TRIM\(([^)]+),1\)/';
        $replacement = 'LOWER(REPLACE($1, \' \', \'\'))';
        $newSql = preg_replace($pattern, $replacement, $sql);

        if ($newSql !== $sql) {
            $sql = $newSql;
        }

        $pattern = '/ONLY_TRIM\(([^)]+),0\)/';
        $replacement = 'UPPER(REPLACE($1, \' \', \'\'))';
        $newSql = preg_replace($pattern, $replacement, $sql);

        if ($newSql !== $sql) {
            $sql = $newSql;
        }

        return $sql;
    }

    function onlyReduceToOneSpace($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        return preg_replace("/\s+/", " ", trim($txt));
    }

    function stringOnlyReduceToOneSpace($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        return preg_replace("/\s+/", " ", trim($txt));
    }

    function stringOnlyTextOneSpace($txt, $mayuscula = null) {
        return onlyTextOneSpace($txt, $mayuscula);
    }

    function onlyTextOneSpace($txt, $mayuscula = null) {
        $txt = ltrim(rtrim($txt));
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = stringOnlyReduceToOneSpace($txt);
        return preg_replace('([^A-Za-z ])', '', $txt);
    }

    function stringOnlyTextComprime($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = str_replace('-', '', $txt);
        return preg_replace('([^A-Za-z])', '', $txt);
    }

    function urlsmartlink($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        #$txt = str_replace('-', '', $txt);
        return preg_replace('([^A-Za-z-0-9--])', '', $txt);
    }

    function stringOnlyAlfaNumeric($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = str_replace('-', '', $txt);
        return preg_replace('([^A-Za-z-0-9])', '', $txt);
    }

    function stringOnlyAlfaNumericTxt($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $asuntos = '';
        $array = imap_mime_header_decode($txt);
        foreach ($array as $m) {
            $asuntos .= utf8_encode(rtrim($m->text, "t"));
        }

        return preg_replace('([^A-Za-z-0-9 ])', '', $asuntos);
    }

    function array_alternoado($array1 = [], $array2 = []) {
        if (count($array1) == 0):
            $array1 = array_fill(0, count($array2), '');
        endif;

        $resultado = [];
        foreach ($array2 as $index => $m):
            $resultado[] = $array1[$index];
            $resultado[] = $m;
        endforeach;

        /*for ($i = 0; $i<$longitud; $i++) {
			if (isset($array1[$i/2])) {
				$resultado[] = $array1[$i/2];
			}
			if (isset($array2[$i/2])) {
				$resultado[] = $array2[$i/2];
			}
		}*/
        return $resultado;
    }

    function EBR_name($tiendacode = 0) {
        $year = date('y');
        $sequenceNumber = "0001";
        $senderCode = zerofill($tiendacode, 3);
        $recipientId = "707";
        $fileExtension = ".xlsx";
        $fileName = "EB{$year}{$sequenceNumber}{$senderCode}_{$recipientId}{$fileExtension}";
        return $fileName;
    }

    function getLimit($limit = '') {
        if (isset($_POST['limit'])):
            $limit = " LIMIT " . $_POST['limit'];
        elseif (isset($_POST['limite'])):
            $limit = " LIMIT " . $_POST['limite'];
        elseif (is_numeric(onlyNumber($limit))):
            $limit = " LIMIT " . onlyNumber($limit);
        elseif (is_array($limit)):
            $offset = ($limit[0] - 1) * $limit[1];
            $limit = " LIMIT $offset," . $limit[1];
        else:
            $limit = '';
        endif;

        return $limit;
    }

    function periodo_format($fecha) {
        if (strlen($fecha) == 7)
            return $fecha . '-00';
        else
            return substr($fecha, 0, -3) . '-00';
    }

    function stringOnlyNumber($txt, $mayuscula = null) {
        return onlyNumber($txt, $mayuscula);
    }

    function onlyNumber($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        $txt = str_replace('-', '', $txt);
        return preg_replace('([^0-9])', '', $txt);
    }

    function stringOnlyMoney($txt, $mayuscula = null) {
        return onlyMoney($txt, $mayuscula);

    }

    function onlyMoney($txt, $mayuscula = null) {
        $txt = stringToMayusula($txt, $mayuscula);
        return preg_replace('([^0-9.])', '', $txt);
    }

    function stringOnlyNameAWS($txt, $mayuscula = null) {
        $txt = eliminartildes($txt);
        $txt = preg_replace('([^A-Za-z ])', '', $txt);
        $txt = preg_replace("/\s+/", " ", trim($txt));
        $txt = rtrim($txt);
        $txt = str_replace(' ', '_', $txt);
        $txt = preg_replace('([^A-Za-z_])', '', $txt);
        $txt = stringToMayusula($txt, $mayuscula);
        return $txt;
    }

    function addField($txt) {
        if (is_numeric($txt)):
            /* prettyPrint("sprintf('%.0f', $txt)");*/
            if (hasText(stringOnlyTrim(strtoupper($txt)), 'E+')):
                $txt = substr($txt, 0, -6) . substr($txt, -4);
                $txt = sprintf('%.0f', $txt);
            endif;
        endif;
        return addslashes(ltrim(rtrim(stringOnlyReduceToOneSpace($txt))));
    }

    function addField2($txt) {
        $txt = addslashes(ltrim(rtrim(stringOnlyReduceToOneSpace($txt))));
        if (is_numeric($txt)):
            $txt = nformat($txt);
        endif;
        return $txt;
    }

    function stringToMayusula($txt, $mayuscula = null) {
        if ($mayuscula != null):
            if ($mayuscula == true):
                $txt = strtoupper($txt);
            else:
                $txt = strtolower($txt);
            endif;
        endif;
        return $txt;
    }

    function getUrl($tienda, $url) {
        switch ($tienda) {
            case 'spotify':
                return "href='https://open.spotify.com/artist/$url'";
            case 'applemusic':
                return "href='https://music.apple.com/us/artist/chimbala/$url'";
            case 'audiomack':
                return "href='https://audiomack.com/$url'";
            default:
                return "";
        }
    }

    function arraytolower($array, $trim = false, $include_leys = false) {

        if ($include_leys) {
            foreach ($array as $key => $value) {
                if (is_array($value))
                    $array2[strtolower($key)] = arraytolower(($trim ? stringOnlyTrim($value) : $value), $include_leys);
                else
                    $array2[strtolower($key)] = strtolower(($trim ? stringOnlyTrim($value) : $value));
            }
            $array = $array2;
        } else {
            foreach ($array as $key => $value) {
                if (is_array($value))
                    $array[$key] = arraytolower(($trim ? stringOnlyTrim($value) : $value), $include_leys);
                else
                    $array[$key] = strtolower(($trim ? stringOnlyTrim($value) : $value));
            }
        }

        return $array;
    }

    function selectColumTable($files) {
        #$files = convertStringToArray($files, "\t");
        foreach ($files as $i => $m):
            $files[$i] = str_replace([' ', '/'], ['_', '_'], strtolower(stringOnlyReduceToOneSpace($m)));
        endforeach;
        #prettyPrint($files);
        return $files;
    }

    function cmdResultConvertStringToArray($txt, $delimit = ',', $delimitchild = ':') {
        $datos = explode($delimit, $txt);
        $object = new stdClass();
        foreach ($datos as $m):
            $src = explode($m, $txt);
            $key = stringOnlyTextComprime($src[0]); #"'".lcfirst(trm(ucwords($src[0])))."'" ;
            $value = "$src[1]";
            $object->{$key} = $value;
        endforeach;
        return $object;
    }

    function htmlToText($txt) {
        $txt = str_ireplace(["<br />", "<br>", "<br/>", "&nbsp;"], "\r\n", $txt);
        $txt = str_ireplace(["<p>", "</p>"], "", $txt);
        $txt = str_ireplace("&copy;", "Â©", $txt);
        return $txt;
    }

    function periodo($periodo) {
        if (strlen($periodo) > 7):
            $periodo = convertStringToArray($periodo, '|');
            $periodo = array_shift($periodo);
            return substr($periodo, 0, -3) . '-00';
        else:
            return $periodo . '-00';
        endif;
    }

    function onlyURL($url) {

        $specialCharacters = array(
            ' ' => '',
            '#' => '',
            '(' => '',
            ')' => '',
            '&' => '',
            '@' => '',
            '!' => '',
            '$' => '',
            '%' => '',
            '^' => '',
            '*' => '',
            '+' => '',
            '=' => '',
            '[' => '',
            ']' => '',
            '{' => '',
            '}' => '',
            '|' => '',
            '\\' => '',
            '<' => '',
            '>' => '',
            '?' => '',
            '"' => '',
            '\'' => '',
            ',' => '',
            ';' => '',
            ':' => '',
            '/' => '',
            '.' => ''
        );
        $cleanUrl = str_replace(array_keys($specialCharacters), array_values($specialCharacters), $url);
        return $cleanUrl;
    }

    function convertStringToArray($txt, $delimit) {
        if (stringOnlyTrim($txt) == '')
            return [];
        $data = explode($delimit, $txt);
        return $data;
    }

    function reverseDate($performance_start_date) {
        $performance_start_date = convertStringToArray($performance_start_date, '-');
        $anio = array_pop($performance_start_date);
        $performance_start_date = array_merge([$anio], $performance_start_date);
        $performance_start_date = convertArrayToString($performance_start_date, '-');

        return $performance_start_date;
    }

    function formatDateZ($timeCreated) {
        $conectado = convertStringToArray($timeCreated, 'T');
        $conectado[1] = str_replace('Z', '', $conectado[1]);
        $conectado[1] = convertStringToArray($conectado[1], '.');
        $conectado[1] = array_shift($conectado[1]);
        return convertArrayToString($conectado, ' ');
    }

    function getParam($video) {
        $vars = [];
        foreach ($video as $key => $m):
            $m = addslashes($m);
            $vars[] = "$key='$m'";
        endforeach;
        return convertArrayToString($vars, ',');
    }

    function array_find($array, $value2, $operador = '') {
        $value = new stdClass();
        $value->value = $value2;
        $value->operador = $operador;
        $result = array_filter($array, function ($item) use ($value) {
            if ($value->operador !== ''):
                if ($value->operador == '!='):
                    if (stringOnlyTrim($item) != $value->value):
                        return true;
                    else:
                        return false;
                    endif;
                endif;
            endif;

            if (stripos($item, $value->value) !== false) {
                return true;
            }
            return false;
        });
        return $result;
    }

    # function array_search_empty($datos, $columna, $novacio = true) {
    #     return array_filter($datos, function ($obj) {
    #         if ($novacio)
    #             return !empty($obj->$columna);
    #         else
    #             return empty($obj->$columna);
    #     });
    # }
    function array_search_empty($datos, $columna, $vacio = false) {
        return array_filter($datos, function ($obj) use ($columna, $vacio) {
            if ($vacio) {
                return empty($obj->$columna);
            } else {
                return !empty($obj->$columna);
            }
        });
    }

    function array_find_colum($array, $column, $value) {
        $filtered = array_filter($array, function ($item) use ($column, $value) {
            #return isset($item[$column]) && $item[$column] === $value;
            if (isset($item[$column]) && $item[$column] === $value)
                return true;
            else
                return false;
        });

        if (count($filtered) > 0) {
            #return reset($filtered); // Devuelve el primer elemento del array filtrado
            return $filtered; // Devuelve el primer elemento del array filtrado
        }

        return [];
    }

    function array_find_obj($array, $colum, $value, $hass = false, $tiene = true) {
        $index = isset($array['index']) ? true : false;
        $array = $array['index'] ?? $array;

        $values = new stdClass();
        $values->colum = $colum;
        $values->value = $value;
        $values->tiene = $tiene;
        $values->hass = $hass;

        $result = array_filter($array, function ($item) use ($values) {
            $item = (array)$item;

            if (stringOnlyTrim($values->colum) == '')
                return false;

            if (!isset($item[$values->colum]))
                return false;

            if ($values->hass):
                if (hasText($item[$values->colum], $values->value) == $values->tiene):
                    return true;
                else:
                    return false;
                endif;
            endif;

            if (rtrim(ltrim(stringOnlyReduceToOneSpace($item[$values->colum]))) == rtrim(ltrim(stringOnlyReduceToOneSpace($values->value)))):
                return true;
            else:
                return false;
            endif;
        });
        $result = is_null($result) ? [] : $result;
        if ($index)
            return $result;
        else
            return array_values($result);
    }

    function getNameColum($nombre) {
        $nombre = strtolower(onlyReduceToOneSpace($nombre));
        $nombre = stringOnlyAlfaNumericTxt(eliminartildes($nombre));
        $nombre = str_replace(' ', '_', $nombre);
        return $nombre;
    }

    function ejecutar($cmd, $return = false) {

        $descriptorspec = array(
            1 => array("pipe", "w")
        );

        $pipes = array();

        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        if (is_resource($process)) {
            $itemline = '';
            while ($s = fgets($pipes[1])) {
                $items = array_map('trim', preg_split('/\R/', $s));;
                foreach ($items as $item) {
                    imprimir("$item" . '*****************');
                    if ($item != ''):
                        $item = stringOnlyReduceToOneSpace($item);
                        #$item = convertStringToArray($item, ' ');
                        /*$item = $item[(count($item)-1)];
                        $item = str_replace('`', '', $item);
                        $item = str_replace('\'', '', $item);*/
                        $itemline .= $item;
                        imprimir("$item" . '*****************');
                    endif;
                }
                if ($return)
                    return $itemline;
            }
        }
    }

    function getCronjobs($tipo = 'cronjobs') {
        $cmd = "ps -o pid,sess,cmd afx | grep -A20 \"$tipo\" 2>&1";
        $descriptorspec = array(
            1 => array("pipe", "w")
        );
        $pipes = array();
        #proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        $src = '';
        $tareas = [];
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                $src .= $s;
                if (hasText($s, "libs/$tipo"))
                    $tareas[] = "$s";
            }
        }

        $task = [];
        foreach ($tareas as $m):
            if (hasText($m, 'bin/sh')):
                $m = convertStringToArray($m, '/');
                $m = array_pop($m);
                $task[] = stringOnlyTrim(rtrim("$m"));
            endif;

            #$task[] = stringOnlyTrim("$m");
        endforeach;
        return $task;
    }

    function verifyruntask($task) {
        $task = stringToArray($task, '/');
        $task = array_pop($task);


        $tareas = getCronjobs();
        $tareas2 = getCronjobs('tareas');
        if (is_array($tareas2)):
            if (count($tareas2) > 0):
                $tareas = array_merge($tareas, $tareas2);
            endif;
        endif;
        /*console($tareas);
		die();*/
        $ntareas = 0;
        foreach ($tareas as $m2):
            if ($m2 == $task):
                $ntareas++;
            endif;
        endforeach;
        if ($ntareas > 1):
            $tareas = json_encode($tareas);
            #$sql = "INSERT INTO cronjob SET name ='$task ($ntareas) tarea en curso ---------- $tareas'";
            #$m->set($sql);
            die('TAREA EN CURSO');
        endif;
    }

    function createDate($fecha, $formato = 'Y-m-d') {
        $date = date_create($fecha);
        return date_format($date, $formato);
    }

    function getFields($campos = []) {

        $fields = [];
        foreach ($campos as $field => $m):
            $m = addslashes($m);
            $fields[] = "$field='$m'";
        endforeach;
        $fields = convertArrayToString($fields, ',');
        return $fields;
    }

    function convertArrayToString($txt, $delimit) {
        return implode($delimit, $txt);
    }

    function arrayToString($txt, $delimit) {
        #if (count($txt) == 0)
        return implode($delimit, $txt);
        #else
        #	return '';
    }

    function stringToArray($txt, $delimit) {
        if (stringOnlyTrim($txt) == '')
            return [];
        $data = explode($delimit, $txt);
        return $data;
    }

    function ArrayRemoveLastItem($data) {
        array_pop($data);
        return $data;
    }

    function getName($key, $remove_extencion = false) {
        $nombreaudio = convertStringToArray($key, '/');
        $nombreaudio = array_pop($nombreaudio);
        if ($remove_extencion):
            $nombreaudio = stringToArray($nombreaudio, '.');
            $nombreaudio = array_shift($nombreaudio);
        endif;
        return $nombreaudio;
    }

    function getSize($enlace) {
        $headers = get_headers($enlace, 1);
        $contentLength = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
        return $contentLength;
    }

    function getExtencion($archivo, $mayuscula = false) {
        $infoArchivo = pathinfo($archivo);
        if ($mayuscula)
            $infoArchivo['extension'] = strtoupper($infoArchivo['extension'] ?? '');

        return $infoArchivo['extension'] ?? '';
    }

    function getContentType($archivo) {
        $extension = strtolower(getExtencion($archivo));

        $contentTypes = [
            'ai' => 'application/postscript',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'au' => 'audio/basic',
            'avi' => 'video/x-msvideo',
            'bin' => 'application/octet-stream',
            'bmp' => 'image/bmp',
            'class' => 'application/octet-stream',
            'cpt' => 'application/mac-compactpro',
            'css' => 'text/css',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'djv' => 'image/vnd.djvu',
            'djvu' => 'image/vnd.djvu',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dvi' => 'application/x-dvi',
            'dxr' => 'application/x-director',
            'eps' => 'application/postscript',
            'etx' => 'text/x-setext',
            'exe' => 'application/octet-stream',
            'ez' => 'application/andrew-inset',
            'flv' => 'video/x-flv',
            'gif' => 'image/gif',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'hdf' => 'application/x-hdf',
            'hqx' => 'application/mac-binhex40',
            'html' => 'text/html',
            'htm' => 'text/html',
            'ico' => 'image/x-icon',
            'ief' => 'image/ief',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/x-javascript',
            'json' => 'application/json',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'm3u' => 'audio/x-mpegurl',
            'm4a' => 'audio/mp4a-latm',
            'm4p' => 'audio/mp4a-latm',
            'm4u' => 'video/vnd.mpegurl',
            'm4v' => 'video/x-m4v',
            'mac' => 'image/x-macpaint',
            'man' => 'application/x-troff-man',
            'me' => 'application/x-troff-me',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mif' => 'application/vnd.mif',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'mpa' => 'audio/mpeg',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpg4' => 'video/mp4',
            'mpga' => 'audio/mpeg',
            'oda' => 'application/oda',
            'pbm' => 'image/x-portable-bitmap',
            'pdf' => 'application/pdf',
            'pgm' => 'image/x-portable-graymap',
            'pgn' => 'application/x-chess-pgn',
            'pgp' => 'application/pgp',
            'pm' => 'application/x-perl',
            'png' => 'image/png',
            'pnm' => 'image/x-portable-anymap',
            'ppm' => 'image/x-portable-pixmap',
            'pps' => 'application/vnd.ms-powerpoint',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps' => 'application/postscript',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-pn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'rar' => 'application/x-rar-compressed',
            'ras' => 'image/x-cmu-raster',
            'rgb' => 'image/x-rgb',
            'rm' => 'audio/x-pn-realaudio',
            'roff' => 'application/x-troff',
            'rpm' => 'application/x-rpm',
            'rtf' => 'text/rtf',
            'rtx' => 'text/richtext',
            'sgm' => 'text/sgml',
            'sgml' => 'text/sgml',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'sit' => 'application/x-stuffit',
            'snd' => 'audio/basic',
            'src' => 'application/x-wais-source',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            'svg' => 'image/svg+xml',
            'swf' => 'application/x-shockwave-flash',
            't' => 'application/x-troff',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tr' => 'application/x-troff',
            'tsv' => 'text/tab-separated-values',
            'txt' => 'text/plain',
            'wav' => 'audio/x-wav',
            'webm' => 'video/webm',
            'wma' => 'audio/x-ms-wma',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wri' => 'application/x-mswrite',
            'wrl' => 'model/vrml',
            'wrz' => 'x-world/x-vrml',
            'xbm' => 'image/x-xbitmap',
            'xhtml' => 'application/xhtml+xml',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
            'xpm' => 'image/x-xpixmap',
            'xsl' => 'application/xml',
            'xslt' => 'application/xslt+xml',
            'xul' => 'application/vnd.mozilla.xul+xml',
            'xwd' => 'image/x-xwindowdump',
            'xyz' => 'chemical/x-xyz',
            'zip' => 'application/zip',
            '7z' => 'application/x-7z-compressed'
        ];

        return $contentTypes[$extension] ?? 'application/octet-stream';
    }

    function array_str($txt) {
        $txt = (is_array($txt)) ? implode(',', $txt) : $txt;
        return $txt;
    }

    function string_array($txt) {
        $txt = ($txt != '') ? explode(',', $txt) : $txt;
        return $txt;
    }

    function transform_array($txt) {
        $txt = ($txt != '') ? explode(',', $txt) : $txt;
        if (!is_array($txt))
            $txt = [];

        return $txt;
    }

    function array_get_first($txt) {
        return array_shift($txt);
    }

    function array_get_latest($txt) {
        return array_pop($txt);
    }

    function search_str($txt, $n) {
        if (strpos($txt, $n))
            return true;
        else
            return false;
    }

    function numshort() {
        return rand(1, 10);
    }

    function getRole($idroll) {
        #$rol = ['4'=>'C','5'=>'A','6'=>'CA'];
        $rol = ['C' => '4', 'A' => '5', 'CA' => '6'];
        return array_search($idroll, $rol);
    }

    function abc($letra) {

        $alphabet = [
            '1' => 'A',
            '2' => 'B',
            '3' => 'C',
            '4' => 'D',
            '5' => 'E',
            '6' => 'F',
            '7' => 'G',
            '8' => 'H',
            '9' => 'I',
            '10' => 'J',
            '11' => 'K',
            '12' => 'L',
            '13' => 'M',
            '14' => 'N',
            '15' => 'O',
            '16' => 'P',
            '17' => 'Q',
            '18' => 'R',
            '19' => 'S',
            '20' => 'T',
            '21' => 'U',
            '22' => 'V',
            '23' => 'W',
            '24' => 'X',
            '25' => 'Y',
            '26' => 'Z'
        ];

        return array_search($letra, $alphabet);
    }

    function abcIndice($indice) {
        $alphabet = [
            'A' => '1',
            'B' => '2',
            'C' => '3',
            'D' => '4',
            'E' => '5',
            'F' => '6',
            'G' => '7',
            'H' => '8',
            'I' => '9',
            'J' => '10',
            'K' => '11',
            'L' => '12',
            'M' => '13',
            'N' => '14',
            'O' => '15',
            'P' => '16',
            'Q' => '17',
            'R' => '18',
            'S' => '19',
            'T' => '20',
            'U' => '21',
            'V' => '22',
            'W' => '23',
            'X' => '24',
            'Y' => '25',
            ' Z' => '26'
        ];
        return array_search($indice, $alphabet);
    }

    function getLastCellIndex($letra) {
        $num = 0;
        $position = '';
        if (strlen($letra) > 1) {
            $position = (abc($letra[0]));
            $num = ($position * 26) + abc($letra[1]);
        } else {
            $num = abc($letra[0]);
        }
        return $num;
    }

    function MbToBytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');
        $res = round(pow(1024, $base - floor($base)), $precision);
        $res = ($res * 1000000);
        return $res;
    }

    function getNextCellIndex($letra) {
        $num = getLastCellIndex($letra);
        return ($num + 1);
    }

    function breaktext($txt, $cant) {
        if (strlen($txt) > $cant)
            return substr($txt, 0, $cant) . ' ...';
        else
            return substr($txt, 0, $cant);
    }

    function roundtxt($txt, $cant) {
        $puntitos = '';
        if (strlen($txt) > $cant):
            $puntitos = ' ...';
        endif;
        return substr($txt, 0, $cant) . $puntitos;
    }

    function mapeo($item) {
        return (object)$item;
    }

    function array_duplicates($arr) {
        $unique = array_unique($arr);
        return array_diff_assoc($arr, $unique);
    }

    function get_q($q) {
        $rol = [
            '03' => 'q1',
            '06' => 'q2',
            '09' => 'q3',
            '12' => 'q4'
        ];
        return array_search($q, $rol);
    }

    function get_row($documento, $eq, $separador, $array = false, $row = 0) {
        $d = $row;
        $nameperiodo = '';
        if (($handle = fopen("$documento", "r")) !== FALSE):
            $i = 0;
            while (($data = @fgetcsv($handle, 0, "$separador")) !== FALSE):
                if ($separador == ','):
                    foreach ($data as $i2 => $a):
                        $data[$i2] = str_replace(',', '|', $a);
                    endforeach;
                endif;
                if ($array):
                    $nameperiodo = $data;
                #$i = 0;
                else:
                    $nameperiodo = $data[$eq];
                    #$i = 1;
                endif;


                if ($d == $i)
                    break;
                $i++;
            endwhile;
            fclose($handle);
        endif;
        return $nameperiodo;
    }

    /*function get_row($documento, $eq, $separador, $array = false, $num = 0) {
        $d = 0;
        $nameperiodo = '';
        if (($handle = fopen("$documento", "r")) !== FALSE):
            while (($data = fgetcsv($handle, 0, "$separador")) !== FALSE):

                if ($array):
                    $nameperiodo = $data;
                    $i = 0;
                else:
                    $nameperiodo = $data[$eq];
                    $i = 1;
                endif;


                if ($d == $i)
                    break;
                $d++;
            endwhile;
            fclose($handle);
        endif;
        return $nameperiodo;
    }*/

    function argumentos2($argumentos = []) {
        $argumentos = $argumentos ? $argumentos : $_POST;
        $datos = "";
        foreach ($argumentos as $key => $m):
            if ($key != 'load' and
                $key != 'in' and
                $key != 'ingestion' and
                $key != 'style' and
                $key != 'ruta' and
                $key != 'id_dom' and
                $key != 'autocomplete' and
                $key != 'class' and
                $key != 'load' and
                $key != 'method' and
                $key != 'tagName' and
                $key != 'estatic'):

                $data = hasText($m, 'data-') ? '' : 'data-';
                $datos .= "$data$key='" . inruta($m) . "' ";
            endif;
        endforeach;
        return $datos;
    }

    function argumentos($argumentos = []) {
        $data = "";
        foreach ($argumentos as $key => $m):
            if ($key == 'load' or $key == 'in' or $key == 'ingestion' or $key == 'estatic'):
                $data .= "$key='$m' ";
            elseif ($key == 'tooltip' or $key == 'confirm'):
                $data .= "data-$key='$m' ";
            else:
                $data .= "data-$key='" . inruta($m) . "' ";
            endif;
        endforeach;
        return $data;

    }

    function createurl_ID($texto) {
        $texto = eliminartildes($texto);
        $texto = stringOnlyReduceToOneSpace($texto);
        // Eliminar caracteres no alfanumÃ©ricos
        $texto = preg_replace('/[^a-zA-Z0-9 ]/', '', $texto);

        // Reducir espacios mÃºltiples a un solo guion
        $texto = preg_replace('/\s+/', '-', $texto);

        // Reducir dos guiones a uno
        $texto = str_replace('--', '-', $texto);

        return $texto;
    }

    function obtenerIniciales($texto, $mayusculas = false) {
        $texto = stringOnlyAlfaNumericTxt($texto);
        $texto = onlyReduceToOneSpace($texto);

        $palabras = explode(' ', $texto);

        $iniciales = array_map(function ($palabra) use ($mayusculas) {
            $inicial = strtoupper(substr($palabra, 0, 1));
            return $mayusculas ? $inicial : strtolower($inicial);
        }, $palabras);

        $inicialesTexto = implode('', $iniciales);

        return $inicialesTexto;
    }

    function getServicios($ev = '', $txt = 0, $separator = ',') {
        $servicio = [];
        $servicio[] = $ev[0] == 7 ? ($txt == 0 ? 'Distribucion' : 1) : '';
        $servicio[] = $ev[1] == 7 ? ($txt == 0 ? 'Youtube' : 2) : '';
        $servicio[] = $ev[2] == 7 ? ($txt == 0 ? 'Publishing' : 3) : '';
        $servicio[] = $ev[3] == 7 ? ($txt == 0 ? 'Marketing' : 4) : '';
        $servicio[] = $ev[4] == 7 ? ($txt == 0 ? 'Conexos' : 5) : '';
        $servicio[] = $ev[5] == 7 ? ($txt == 0 ? 'Playlist' : 6) : '';
        $servicio = array_filter($servicio);
        return convertArrayToString($servicio, "$separator");
    }

    function textBeofre($cadena, $text) {
        if (!hasText($cadena, $text))
            return $cadena;

        $partes = explode("$text", $cadena);
        return $partes[0];
    }

    function get_vars($data) {
        $datas = [];
        foreach ($data as $key => $m):
            if ($key == 'ruta' or $key == 'style' or $key == 'class' or $key == 'in' or $key == 'tagName' or $key == 'id_dom')
                continue;

            $datas[] = "$key='$m'";
        endforeach;
        return arrayToString($datas, ' ');
    }

    function is_like($texto) {
        $texto = onlyTrim($texto);
        $p1 = substr($texto, 0, 1);
        $p2 = substr($texto, -1);
        $p3 = "$p1$p2";

        if ($p3 == '%%'):
            return true;
        else:
            return false;
        endif;
    }

    function getOjectCsv($archivo) {
        $data = new stdClass();

        if (($handle = fopen($archivo, 'r')) !== false) {
            $delimiter = "\t";
            $headers = fgetcsv($handle, 5000, $delimiter);
            if (!$headers) {
                $delimiter = ',';
                $headers = fgetcsv($handle, 5000, $delimiter);
            }

            $headers = array_map(function ($header) {
                $header = stringOnlyReduceToOneSpace($header);
                return str_replace(' ', '_', $header);
            }, $headers);

            $rowData = fgetcsv($handle, 5000, $delimiter);
            if ($rowData !== false) {
                $data = (object)array_combine($headers, $rowData);
            }

            fclose($handle);
            return $data;
        }
        return $data;
    }

    function csvToObject($header, $line) {
        $data = explode(',', $line);
        $obj = array();
        foreach ($header as $key => $value) {
            $obj[$value] = isset($data[$key]) ? $data[$key] : '';
        }
        return $obj;
    }


    function console($array) {
        echo '<pre>';
        var_dump($array);
        echo '</pre>';
    }

    function array_remove_by_value($valorBuscado, $miArray) {
        $order = is_Array($valorBuscado) ? array_shift($valorBuscado) : $valorBuscado;
        $order = is_null($order) ? '' : $order;

        $clave = array_search($order, $miArray);
        if ($clave !== false) {
            unset($miArray[$clave]);
        }
        return $miArray;
    }

    function array_get_order($miArray) {
        $order = array_find($miArray, 'ORDER', '');
        $order = is_Array($order) ? array_shift($order) : $miArray;
        $order = is_null($order) ? '' : $order;
        return ltrim(rtrim($order));
    }

    /*function parametros($variables = [], $sinwhere = false) {
		$variables = array_find($variables, '', '!=');
		console($variables);

		/*$order = array_get_order('ORDER', $variables);
		$variables = array_remove_by_value($order, $variables);


		 console($order);
		 console($variables);*/
    function parametros($variables = [], $sinwhere = false) {
        $variables = array_find($variables, '', '!=');
        $order = array_get_order($variables);
        $variables = array_remove_by_value($order, $variables);
        $variables = arrayToString($variables, ' AND ');

        $sql = '';
        if ($sinwhere == false):
            $sql = $variables != '' ? " WHERE $variables $order" : '';
        else:
            /*if (hasText("$variables $order", 'ORDER', true)):*/
            $sql = " AND $variables $order";
            /*else:
				return $variables != '' ? " AND $variables " : '';
			endif;*/
        endif;
        if (onlyTrim($sql) == 'AND' or onlyTrim($sql) == 'WHERE')
            $sql = '';

        return $sql;
    }

    function array_construct($array, $object = true) {
        $obj = new stdClass();
        foreach ($array as $m):
            if (isset($m->Key)):
                if (hasText(strtolower($m->Key), 'date')):
                    $m->Value = $m->Value ?? '';
                    $m->Value = str_replace([' am', ' pm'], ['', ''], strtolower($m->Value));
                    $m->Value = removemicrotime($m->Value);
                    $m->Value = formatDate($m->Value);;
                endif;
                $obj->{strtolower($m->Key)} = $m->Value ?? '';
            endif;
        endforeach;
        return $obj;
    }

    function formatDate($fecha) {

        if (stringOnlyTrim($fecha) != ''):
            /*console($fecha);*/
            $fecha = str_replace(',', '', $fecha);
            $fecha = stringOnlyReduceToOneSpace($fecha);
            $fecha = date_eval($fecha);

            $fecha = convertStringToArray($fecha, ' ');

            $horas = array_pop($fecha);
            $horas = convertStringToArray($horas, ':');
            foreach ($horas as $i => $m):
                $horas[$i] = zerofill($m, 2);
            endforeach;


            $horas = convertArrayToString($horas, ':');

            if (count($fecha) == 1):
                $fecha = str_replace('/', '-', $fecha[0]);
            else:
                $fecha = convertArrayToString($fecha, '-');
            endif;

            $fecha = convertStringToArray($fecha, '-');
            $d1 = array_pop($fecha);
            $fecha = array_reverse($fecha);

            $fecha[] = $d1;
            $fecha = array_reverse($fecha);
            foreach ($fecha as $i => $m):
                $fecha[$i] = zerofill($m, 2);
            endforeach;

            $fecha = convertArrayToString($fecha, '-');
            return "$fecha $horas";
        endif;
        return '0000-00-00 00:00:00';
    }

    function date_eval($fecha) {
        $fecha = strtolower($fecha);
        $mes = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $mesremplace = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $fecha = str_replace($mes, $mesremplace, $fecha);
        $fecha = stringOnlyReduceToOneSpace($fecha);
        return $fecha;
    }


    function array_obj_key($object, $key) {
        foreach ($object as $name => $m):
            #console("$name ==== $key");
            if ($name === $key)
                return true;
        endforeach;
        return false;
    }

    function removemicrotime($fecha) {
        $punto = strpos($fecha, ".");

        if ($punto != false)
            return substr($fecha, 0, $punto);
        else
            return $fecha;
    }

    function getFtpFiles($ftp_domain, $ftp_user, $ftp_clave, $ruta = '/') {
        $ftp_connection = ftp_ssl_connect($ftp_domain);
        if (!$ftp_connection) {
            die("No se pudo conectar al servidor FTP");
        }

        $login_result = ftp_login($ftp_connection, $ftp_user, $ftp_clave);
        if (!$login_result) {
            die("Error de inicio de sesiÃ³n en el servidor FTP");
        }

        ftp_pasv($ftp_connection, true);

        $archivos = ftp_nlist($ftp_connection, "$ruta");
        ftp_close($ftp_connection);
        return $archivos;
    }

    function hasText($text, $tiene, $minuscula = false) {
        if ($minuscula):
            $text = strtolower($text);
            $tiene = strtolower($tiene);
        endif;
        return is_numeric(strpos($text, "$tiene")) ? true : false;
    }

    /*function esImagen($nombreArchivo) {
		$extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
		return ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'gif') && @getimagesize($nombreArchivo);
	}*/

    function esImagen($url) {
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        return ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'gif');
    }

    function isURL($cadena) {
        return filter_var($cadena, FILTER_VALIDATE_URL) !== false;
    }

    function convert_array($txt, $n) {
        if (search_str($txt, $n)) {
            $txt = string_array($txt);
        } else {
            $a = $txt;
            $txt = [];
            $txt[] = $a;
        }
        return $txt;
    }

    function icons_social() {
        $icons = [];
        $directorios = glob("public/socialicon/*");
        foreach ($directorios as $m) {
            $dato = [];
            if (is_file($m)) {
                $m = explode('/', $m);
                $m = array_get_latest($m);
                $dato[] = $m;
                $m = explode('.', $m);
                $dato[] = $m[0];
                $icons[] = $dato;
            }
        }
        return $icons;
    }


    function selected($valor1, $valor2) {
        if ($valor1 == $valor2) {
            echo "selected";
        }
    }


    function searchruta($ruta, $file) {
        $ruta = glob("$ruta/*");
        foreach ($ruta as $m) {
            if (is_file($m)) {
                $m = explode('/', $m);
                $m = array_get_latest($m);
                $m = explode('.', $m);
                $m = array_get_first($m);
                if ($m == $file) {
                    return true;
                }
            }
        }
        return false;
    }

    function nombre_log() {
        return "(" . $_SESSION['nickname'] . ") " . $_SESSION['nombres'] . " " . $_SESSION['apellidos'];
    }

    function minimizar_numero($input) {
        $suffixes = array('', 'k', 'm', 'g', 't');
        $suffixIndex = 0;

        while (abs($input) >= 1000 && $suffixIndex < sizeof($suffixes)) {
            $suffixIndex++;
            $input /= 1000;
        }

        return ($input > 0
                // precision of 3 decimal places
                ? floor($input * 10) / 10
                : ceil($input * 10) / 10
            )
            . $suffixes[$suffixIndex];
    }

    function getMes($mes) {
        switch ($mes) {
            case 'January':
                return "Enero";
            case 'February':
                return "Febrero";
            case 'March':
                return "Marzo";
            case 'April':
                return "Abril";
            case 'May':
                return "Mayo";
            case 'June':
                return "Junio";
            case 'July':
                return "Julio";
            case 'August':
                return "Agosto";
            case 'September':
                return "Septiembre";
            case 'October':
                return "Octubre";
            case 'November':
                return "Noviembre";
            case 'December':
                return "Diciembre";
        }
    }

    function porcentaje($valor, $total, $decima = 1) {
        $porcentaje = ($valor) ? $valor * 100 / $total : 0;
        $porcentaje = number_format($porcentaje, $decima, '.', '');
        return $porcentaje;
    }


    function hace($time) {
        // calculando el tiempo entre registro del lanzamiento de un disco
        // seguns minutos horas dias semanas meses ano decada
        $periodos = array("Segundo", "Minuto", "Hora", "DÃ­a", "Semana", "Mes", "AÃ±o", "DÃ©cada");
        $duraciones = array("60", "60", "24", "7", "4.35", "12", "10");
        $now = time();
        $diferencia = $now - $time;

        for ($j = 0; $diferencia >= $duraciones[$j] && $j < count($duraciones) - 1; $j++) {
            $diferencia /= $duraciones[$j];
        }
        $diferencia = round($diferencia);

        if ($diferencia != 1) {
            if ($j != 5) {
                $periodos[$j] .= "s";
            } else {
                $periodos[$j] .= "es";
            }
        }
        return "$diferencia $periodos[$j]";
    }

    /*VALIDAR CORREO*/
    function validar_correo($correo) {
        $result = (false !== filter_var($correo, FILTER_VALIDATE_EMAIL));
        if ($result) {
            $domain = explode("@", $correo)[1];
            $result = checkdnsrr($domain, 'MX');
        }
        return $result;
    }

    /*LIMPIAR CARCATERES*/
    function limpiar($cadena) {
        $textoLimpio = preg_replace("[^A-Za-z0-9]", "", $cadena);
        return $textoLimpio;
    }


    /*RENDERIZAR IMAGEN A 300PX*/
    function sizeimagen($file, $titulo) {
        $width = 300;
        $imagen = imagecreatefromjpeg($file);
        $alto = imagesy($imagen);
        $ancho = imagesx($imagen);
        if ($ancho > $alto) {
            $ancho = $width;
            $alto = $width * imagesy($imagen) / imagesy($imagen);
        } else {
            $alto = $width;
            $ancho = $width * imagesx($imagen) / imagesy($imagen);
        }
        // Creamos la miniatura
        $thumb = imagecreatetruecolor($ancho, $alto);
        // La redimensionamos
        imagecopyresampled($thumb, $imagen, 0, 0, 0, 0, $ancho, $alto, imagesx($imagen), imagesy($imagen));
        // La mostramos como jpg
        imagejpeg($thumb, $file);
    }

    /*FIN RENDERIZAR IMAGEN A 300PX*/


    /*INICIO DE ELIMINAR TILDES*/
    function cleanTxt($texto) {
        $txt = preg_replace('([^A-Za-z0-9 ])', '', $texto);
        return $txt;
    }

    function countTxt($texto) {
        $txt = strlen($texto);
        return $txt;
    }

    function formaturl($cadena) {
        //Ahora reemplazamos las letras

        $cadena = str_replace(
            array('-', '_', '.', '+', '!', '*', ' ', '(', ')', ',', '{', '}', '|', '\\', '^', '~', '[', ']', '`', '<', '>', '#', '%', '"', ';', '/', '?', ':', '@', '&', '=', '.'),
            array('-', '_', '.', '+', '!', '*', ' ', '(', ')', ',', '{', '}', '|', '\\', '^', '~', '[', ']', '`', '<', '>', '#', '%', '"', ';', '/', '?', ':', '@', '&', '=', '.'),
            $cadena
        );


        return $cadena;
    }

    function eliminartildes($cadena) {
        //Ahora reemplazamos las letras
        $cadena = str_replace(
            array('Ã¡', 'Ã ', 'Ã¤', 'Ã¢', 'Âª', 'Ã', 'Ã€', 'Ã‚', 'Ã„'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('Ã©', 'Ã¨', 'Ã«', 'Ãª', 'Ã‰', 'Ãˆ', 'ÃŠ', 'Ã‹'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );

        $cadena = str_replace(
            array('Ã­', 'Ã¬', 'Ã¯', 'Ã®', 'Ã', 'ÃŒ', 'Ã', 'ÃŽ'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );

        $cadena = str_replace(
            array('oÌ', 'Ã³', 'Ã²', 'Ã¶', 'Ã´', 'Ã“', 'Ã’', 'Ã–', 'Ã”'),
            array('o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );

        $cadena = str_replace(
            array('Ãº', 'Ãº', 'Ã¹', 'Ã¼', 'Ã»', 'Ãš', 'Ã™', 'Ã›', 'Ãœ', 'Ãº'),
            array('u', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'u'),
            $cadena
        );

        $cadena = str_replace(
            array('Ã±', 'Ã‘', 'Ã§', 'Ã‡'),
            array('n', 'N', 'c', 'C'),
            $cadena
        );

        return $cadena;
    }


    function printt($datos) {
        die(print_r($datos));
    }

    function promedioColorImagen($rutaImagen) {
        // obtenemos el tipo mime de la imagen (desde PHP 5.3)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMime = finfo_file($finfo, $rutaImagen);
        // abrimos la imagen
        if ($fileMime == "image/jpeg" || $fileMime == "image/jpeg")
            $imgId = imagecreatefromjpeg($rutaImagen);
        elseif ($fileMime == "image/gif")
            $imgId = imagecreatefromgif($rutaImagen);
        elseif ($fileMime == "image/png")
            $imgId = imagecreatefrompng($rutaImagen);
        else
            return array(0, 0, 0);
        $red = 0;
        $green = 0;
        $blue = 0;
        $total = 0;
        // Recorremos todos los valores horizontales
        for ($x = 0; $x < imagesx($imgId); $x++) {
            // Recorremos todos los valores verticales
            for ($y = 0; $y < imagesy($imgId); $y++) {
                // Obtenemos los valores red, green, blue de cada pixel de la imagen
                // (http://php.net/manual/en/function.imagecolorat.php)

                $rgb = imagecolorat($imgId, $x, $y);
                // devuelve el indice de cada color
                $red += ($rgb >> 16) & 0xFF;
                $green += ($rgb >> 8) & 0xFF;
                $blue += $rgb & 0xFF;
                $total++;
            }
        }
        $redPromedio = round($red / $total);
        $greenPromedio = round($green / $total);
        $bluePromedio = round($blue / $total);
        // devolvemos un array con el promedio de los colores en rojo, verde y azul
        return array($redPromedio, $greenPromedio, $bluePromedio);
    }

    /*FIN DE PROMEDIO COLOR*/


    /*FIN DE PROMEDIO COLOR*/


    /*INICIO RGBA EX*/
    function rgbahex($R, $G, $B) {

        $R = dechex($R);
        if (strlen($R) < 2)
            $R = '0' . $R;

        $G = dechex($G);
        if (strlen($G) < 2)
            $G = '0' . $G;

        $B = dechex($B);
        if (strlen($B) < 2)
            $B = '0' . $B;

        return '#' . $R . $G . $B;
    }

    function xml_entities($string) {
        return strtr(
            $string,
            array(
                "&" => "&amp;",
            )
        );
    }


    /* FIN DE GENERAR UPC*/


    /*INICIO  DE VERIFICAR UPC*/
    function verificarupc($upc) {
        // GENERANDO VALIDADOR DE CODIGO UPC //
        $c = $upc;
        $suma1 = $c[0] + $c[2] + $c[4] + $c[6] + $c[8] + $c[10];
        $suma1 = $suma1 * 3;
        $suma2 = $c[1] + $c[3] + $c[5] + $c[7] + $c[9];
        $total = $suma1 + $suma2;
        $resultado = '' . $total . '';
        if ($resultado[1] > 0) {
            $valor = $resultado[1] - 10;
            $resultado = $total - $resultado[1] + 10;
        } else {
            $valor = 0;
        }
        return true;
    }

    /*FIN  DE VERIFICAR UPC*/


    function stripePeriodo($num, $per) {
        if ($num == 1 and $per == 'year')
            return "$num aÃ±o";
        if ($num == 1 and $per == 'month')
            return "$num mese";
        if ($num > 1 and $per == 'month')
            return "$num meses";
    }

    function zerofill($numero, $ancho, $orientacion = STR_PAD_LEFT) {
        $padded = str_pad((string)$numero, $ancho, "0", $orientacion);
        return $padded;
    }

    function generarupc($precodigo) {

        $codigo = $precodigo;
        $precodigo = str_split($precodigo);
        $impar = 0;
        $par = 0;

        foreach ($precodigo as $index => $m):
            if ($index % 2 == 0)
                $impar += intval($m);
            if ($index % 2 == 1)
                $par += intval($m);
        endforeach;

        $total = ($impar * 3) + $par;
        $pre = intval(substr($total, -1));
        $pre = $pre > 0 ? (10 - $pre) : 0;
        return $codigo . $pre;
    }

    /*FIN DE ZERO FILL*/


    /*INICIO DE OBETENER EXTENCION DE ARCHIVO*/
    function getFileExtension($file_name) {
        return substr(strrchr($file_name, '.'), 1);
    }


    function descargar($archivo = '') {

        $nombreArchivo = basename($archivo);

        if (file_exists($archivo)) {
            header('Content-Description: Archivo Descargado');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($archivo));

            readfile($archivo);
            exit;
        } else {
            echo "El archivo no existe.";
        }
    }

    function comando_front($ruta, $variables = '') {

        $cmd = "/usr/bin/php /var/www/html/public_html/$ruta $variables 2>&1";
        if (hasText($ruta, '/usr/bin/php')):
            $cmd = "$ruta $variables 2>&1";
        endif;

        if (hasText($ruta, 'node')):
            $cmd = "/home/ubuntu/.nvm/versions/node/v17.2.0/bin/$ruta $variables  2>&1";
        endif;

        /*console($cmd);
		die();*/
        $descriptorspec = array(
            1 => array("pipe", "w")
        );
        $pipes = array();
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());

        $src = '';
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                $src .= $s;
            }
        }

        return $src;
    }

    function exec_cmd($script) {
        unset($_GET['url']);

        $arg = [];
        foreach ($_GET as $key => $value) {
            $arg[] = "$key*$value";
            #imprimir("$key|$value");
        }
        $vars = convertArrayToString($arg, ',');
        $vars = str_replace(',', ' ', $vars);


        $cmd = "/usr/bin/php  libs/cronjobs/$script $vars";
        #imprimir($cmd);
        $descriptorspec = array(
            1 => array("pipe", "w")
        );
        $pipes = array();
        #proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                echo "$s";
            }
        }
    }

    function exec_cmd_backgraund($script, $backgraund = true, $printconsole = false) {
        unset($_GET['url']);
        unset($_GET['encode']);

        $arg = [];
        foreach ($_GET as $key => $value) {
            $arg[] = "$key*$value";
            #imprimir("$key|$value");
        }
        $vars = convertArrayToString($arg, ',');
        $vars = str_replace(',', ' ', $vars);

        if ($backgraund):
            $cmd = "/usr/bin/php  libs/cronjobs/$script $vars";
            if (hasText($script, 'tareas/'))
                $cmd = "/usr/bin/php  libs/$script $vars";

            exec($cmd . ' > /dev/null $execicion');
        else:
            $cmd = "/usr/bin/php  libs/cronjobs/$script $vars 2>&1";
            if (hasText($script, 'tareas/'))
                $cmd = "/usr/bin/php  libs/$script $vars 2>&1";

            $descriptorspec = array(
                1 => array("pipe", "w")
            );
            $pipes = array();
            #proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
            $process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
            $src = '';
            if (is_resource($process)) {
                while ($s = fgets($pipes[1])) {
                    $src .= $s;
                    if ($printconsole):
                        echo "$s";
                    endif;
                }
                return $src;
            }
        endif;
    }

    function getVars($vars) {
        $arg = [];
        foreach ($vars as $key => $var) {
            if (substr($key, 0, 5) == 'data-')
                $arg[] = substr($key, 5) . '=' . $var;
        }
        if (count($arg) > 0) {
            $arg = '?' . convertArrayToString($arg, '&');
        } else {
            $arg = '';
        }
        return $arg;
    }

    function getVarsObj($vars, $delimit = '*') {
        $object = new stdClass();
        foreach ($vars as $key => $var) {
            if ($key == 0)
                continue;
            $var = convertStringToArray($var, $delimit);
            @$object->{$var[0]} = $var[1];
        }
        return $object;
    }

    function cronjob_usuario() {
        return shell_exec("/usr/bin/php  libs/cronjobs/prueba.php");
    }

    function montoEnviado($enviado, $monto, $estado) {
        if ($estado > 5)
            return $monto;
        if ($estado = 3)
            return $monto;
        else
            return $enviado;
    }

    function ocultar_cuenta($string, $visible = 4, $prefijo = '*') {
        $asteriscos = null;
        $ancho = strlen($string) - $visible;
        for ($i = 0; $i < $ancho; $i++) {
            $asteriscos .= $prefijo;
        }
        $resultado = $asteriscos . substr($string, $ancho);
        return $resultado;
    }


    function sendForm($url, $datos = []) {
        if (is_object($datos)):
            $datos = (array)$datos;
        elseif (isset($_POST) and count($datos) == 0):
            $datos = $_POST;
        endif;
        unset($datos['mensaje']);

        console($url);
        $formData = $datos;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Agregar esta lÃ­nea
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return false;
        }
        $info = curl_getinfo($ch);
        curl_close($ch);
        if (onlyTrim($response) == '')
            return false;
        $result = new stdClass();
        $result->data = $response;
        return $result;
    }

    function getPostCurl($ruta) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$ruta");
        $config['useragent'] = "Dark Secret Ninja/1.0";

        curl_setopt($ch, CURLOPT_USERAGENT, $config['useragent']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $server_output = curl_exec($ch);
        /*prettyPrint($server_output);
		die();*/
        curl_close($ch);
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->strictErrorChecking = false;
        @$doc->loadHTML($server_output);
        /*prettyPrint($server_output);*/
        libxml_use_internal_errors(false);
        $xml = simplexml_import_dom($doc);
        return $xml;
    }

    /**Mostrar color del tipo de smartlinks */
    function convertMilliseconds($milliseconds) {
        $seconds = floor($milliseconds / 1000);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;

        $out = "";
        if ($hours > 0) {
            $out .= $hours . "h ";
        }
        if ($minutes > 0) {
            $out .= $minutes . "m ";
        }
        if ($seconds > 0) {
            $out .= $seconds . "s";
        }

        return $out;
    }

    function eliminarColumnaRecursiva($array, $columnaAEliminar) {
        foreach ($array as $key => &$value) {
            if (is_array($value) or is_object($value)) {
                $value = (array)$value;
                $value = eliminarColumnaRecursiva($value, $columnaAEliminar);
            }
            if ($key === $columnaAEliminar) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    function getAt($fechaOriginal) {
        if (substr($fechaOriginal, -1) == 'Z')
            $fechaOriginal = substr($fechaOriginal, 0, -1);

        $ultimosDiez = substr($fechaOriginal, 0, 10);
        $partes = explode('.', $fechaOriginal);
        $sinPunto = array_shift($partes);
        $ultimosDiezSinPunto = substr($sinPunto, -8);
        return "$ultimosDiez $ultimosDiezSinPunto";
    }


    function recursiveFilter($data) {
        $data = ignoreColumnas($data);
        $data = ignoreColumnasKeyValue($data);
        #return $data;
        return columnaDate($data);
    }

    function ignoreColumnas($array, $columnas = ['internal_gapi_mappings', 'modelData', 'processed']) {
        $result = [];
        foreach ($array as $key => $value) {
            $key2 = onlyTrim(str_replace('*', '', $key));
            if (!in_array($key2, $columnas)) {
                if (is_array($value) or is_object($value)) {
                    $value = (array)$value;
                    $result[$key] = ignoreColumnas($value, $columnas);
                } else {
                    $key2 = onlyTrim(str_replace('*', '', $key));
                    if (!in_array($key2, $columnas)) {
                        $result[$key] = $value;
                    }
                }
            }
        }

        return $result;
    }

    function ignoreColumnasKey($data, $valor = '*') {
        $_POST['data-post-value'] = $valor;
        return is_array($data) ?
            array_map('ignoreColumnasKey', array_filter($data, function ($value, $key) {
                $parame = $_POST['data-post-value'] ?? '';
                return strpos($key, $parame) === false;
            }, ARRAY_FILTER_USE_BOTH)) :
            (is_object($data) ? (object)array_map('ignoreColumnasKey', (array)$data) : $data);
    }

    function ignoreColumnasKeyValue($data) {
        return is_array($data) ?
            array_map('ignoreColumnasKeyValue', array_filter($data, function ($value, $key) {
                return !(is_string($value) and strpos($value, 'Google\\') !== false and substr($key, -4) === 'Type');
            }, ARRAY_FILTER_USE_BOTH)) :
            (is_object($data) ? (object)array_map('ignoreColumnasKeyValue', (array)$data) : $data);
    }

    function columnaDate($array) {
        foreach ($array as $key => &$value) {
            if (is_array($value) or is_object($value)) {
                $value = (array)$value;
                $value = columnaDate($value);
            }
            if (is_string($value) && evaluarDate($value)) {
                $array[$key] = getAt($value);
            }
        }
        return $array;
    }

    function evaluarDate($texto) {
        $conteoGuiones = substr_count($texto, '-');
        $conteodospuntos = substr_count($texto, ':');
        $conteoPunto = substr_count($texto, '.');
        if ($conteoGuiones == 2 and $conteodospuntos == 2 and $conteoPunto <= 1):
            return true;
        else:
            return false;
        endif;
    }

    function flattenArray($array, $prefix = '_', $minuscula = true) {
        $result = array();

        foreach ($array as $key => $value) {
            $newKey = $prefix . (empty($prefix) ? '' : '_') . $key;

            if ($minuscula) {
                $newKey = strtolower($newKey);
            }
            $newKey = preg_replace('/^__/', '', $newKey);

            if (is_array($value) || is_object($value)) {
                $result = array_merge($result, flattenArray((array)$value, $newKey, $minuscula));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**Fin color del tipo de smartlinks */


    function obtenerNombreCancion($nombreCompleto) {

        $patron = '/^(.*?)(?:\s*(\([^)]*\)))?(?:\s*(?:\[feat\.|feat|ft\.)\s.*?)?$/i';

        preg_match($patron, $nombreCompleto, $coincidencias);

        $nombreCancion = isset($coincidencias[1]) ? $coincidencias[1] : '';

        $remixInfo = isset($coincidencias[2]) && strpos($coincidencias[2], "feat") == false ? $coincidencias[2] : '';

        $nombreCancion = trim($nombreCancion);
        $remixInfo = trim($remixInfo);

        if (!empty($remixInfo)) {
            $nombreCancion .= ' ' . $remixInfo;
        }

        return $nombreCancion;
    }


    function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function getUserBrowser() {
        return $_SERVER['HTTP_USER_AGENT'];
    }



