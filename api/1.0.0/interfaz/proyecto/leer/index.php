<?php
require_once dirname(__FILE__)."/../../../modelo/SafeString.php";
require_once dirname(__FILE__)."/../../../modelo/Proyecto.php";
require_once dirname(__FILE__)."/../../../modelo/Usuario.php";
require_once dirname(__FILE__)."/../../../modelo/Socio.php";
require_once dirname(__FILE__)."/../../../modelo/Administrador.php";

header('Content-Type: text/html; charset=utf8');
if (!isset($_POST['id']) || !isset($_POST['token']) || !isset($_POST['usuario'])) {
	die(json_encode(array("status"=>"error","clave"=>"parametros","explicacion"=>"faltan parametros")));
}

try {
	$id = SafeString::safe($_POST['id']);
	$token = SafeString::safe($_POST['token']);
	if (($_POST['usuario'] == 0)) {
		(new Usuario())->leerCuenta($token, null);
	} elseif (($_POST['usuario'] == 1)) {
		(new Socio())->leerCuenta($token, null);
	} elseif (($_POST['usuario'] == 2)) {
		(new Administrador())->iniciarSesionConToken($token);
	}
	$trabajo = (new Proyecto())->buscarTrabajo($id);
	echo json_encode(array("status"=>"ok","dream"=>$trabajo));
} catch (tokenInvalido $e) {
	echo json_encode(array("status"=>"error","clave"=>"token","explicacion"=>"Token invalido"));
} catch (errorConBaseDeDatos $e) {
	echo json_encode(array("status"=>"error","clave"=>"db","explicacion"=>$e->getMessage()));
} catch (Exception $e) {
	echo json_encode(array("status"=>"error","clave"=>"desconocido","explicacion"=>$e->getMessage()));
}
?>