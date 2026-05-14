<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include("inc/funciones.inc.php");
include("secure/ips.php");

$metodo_permitido = "POST";
$archivo = "../logs/log.log";
$dominio_autorizado = "localhost";
$ip = ip_in_ranges($_SERVER['REMOTE_ADDR'], $rango);
$txt_usuario_autorizado = "admin";
$txt_password_autorizado = "admin";

//Se verifica que la dirección de origen sea autorizada
if(array_key_exists("HTTP_REFERER",$_SERVER)){
	//Si tiene un referer, será necesario verificar el origen
	if(strpos($_SERVER['HTTP_REFERER'],$dominio_autorizado) !== false){
		//El referer sí posee el dominio autorizado
		if($ip === true){
			if($_SERVER['REQUEST_METHOD'] == $metodo_permitido){
				//Todos los controles han sido superados, ahora se deberá verificar el contenido de los campos enviados
				$valor_campo_usuario = ( (array_key_exists("txt_user",$_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_user"])),ENT_QUOTES) : "");
				$valor_campo_password = ( (array_key_exists("txt_pass",$_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_pass"])),ENT_QUOTES) : "");
				if(($valor_campo_usuario!="" || strlen($valor_campo_usuario) > 0) and ($valor_campo_password!="" || strlen($valor_campo_password) > 0)){
					//las variables sí tienen valores, entonces se puede continuar
					$usuario = preg_match('/^[a-zA-Z0-9]{1,10}+$/', $valor_campo_usuario);//Se verifica con un patrón si cumple las condiciones aceptables
					$password = preg_match('/^[a-zA-Z0-9]{1,10}+$/', $valor_campo_password);//Se verifica con un patrón si cumple las condiciones aceptables
					if(($usuario !== false && $usuario !== 0) && ($password !== false && $password !== 0)){
						//Verificar que el usuario y la contraseña tengan los valores esperados
						if($valor_campo_usuario === $txt_usuario_autorizado && $valor_campo_password === $txt_password_autorizado){
							//El usuario y la contraseña sí poseen los valores esperados
							echo("INICIÓ SESIÓN CORRECTAMENTE!");
							crear_editar_log($archivo,"El cliente inició sesión correctamente",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
						}else{
							crear_editar_log($archivo,"Credenciales incorrectas enviadas hacia //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
							header("HTTP/1.1 301 Moved Permanently");
							header("Location: ../?status=7");
							//El usuario y la contraseña no poseen los valores esperados.
						}
					}else{
						//Los valoes ingresados en el formulario poseen caracteres no soportados o la longitud de caracteres es mayor a la permitida
						crear_editar_log($archivo,"Envío de datos de formulario hacia //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] con caracteres no soportados",2,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
						header("HTTP/1.1 301 Moved Permanently");
						header("Location: ../?status=6");
					}
				}else{
					//Al menos uno de los campos no posee datos
					crear_editar_log($archivo,"Envío de algunos campos de formulario vacíos hacia //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
					header("HTTP/1.1 301 Moved Permanently");
					header("Location: ../?status=5");
				}
			}else{
				//El método enviado no está autorizado
				crear_editar_log($archivo,"Ejecución de un método HTTP no soportado",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ../?status=4");
			}
		}else{
			//La dirección Ip no está autorizada
			crear_editar_log($archivo,"Dirección IP no autorizada para ejecutar //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ../?status=3");
		}
	}else{
		//El referer no está autorizado
		crear_editar_log($archivo,"Ha intentado suplantar un referer que no está autorizado",2,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../?status=2");
	}
}else{
	//No posee referer
	crear_editar_log($archivo,"Ha intentado ejecutar //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI] desde la URL sin pasar por el form",2,$_SERVER['REMOTE_ADDR'],((array_key_exists("HTTP_REFERER",$_SERVER)) ? $_SERVER["HTTP_REFERER"] : "NULL REFERER"),$_SERVER["HTTP_USER_AGENT"]);
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ../?status=1");
}

?>