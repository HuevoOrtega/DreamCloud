<?php
require_once dirname(__FILE__)."/../../../modelo/SafeString.php";
require_once dirname(__FILE__)."/../../../modelo/Usuario.php";

header('Content-Type: text/html; charset=utf8');
if (!isset($_POST['token']) || !isset($_POST['usuario']) || !isset($_POST['seguir'])) {
	die(json_encode(array("status"=>"error","clave"=>"parametros","explicacion"=>"faltan parametros")));
}

try {
	$token = SafeString::safe($_POST['token']);
	$usuario = SafeString::safe($_POST['usuario']);
	$seguir = SafeString::safe($_POST['seguir']);
	$info = (new Usuario())->leerCuenta($token, null, 0);
	
	(new Usuario())->seguir($info['id'], $usuario, $seguir);
	echo json_encode(array("status"=>"ok"));
} catch (tokenInvalido $e) {
	echo json_encode(array("status"=>"error","clave"=>"token","explicacion"=>"Token invalido"));
} catch (errorConBaseDeDatos $e) {
	echo json_encode(array("status"=>"error","clave"=>"db","explicacion"=>$e->getMessage()));
} catch (Exception $e) {
	echo json_encode(array("status"=>"error","clave"=>"desconocido","explicacion"=>$e->getMessage()));
}
?>