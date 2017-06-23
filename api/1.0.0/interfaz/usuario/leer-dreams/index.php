<?php
require_once dirname(__FILE__)."/../../../modelo/SafeString.php";
require_once dirname(__FILE__)."/../../../modelo/Proyecto.php";

header('Content-Type: text/html; charset=utf8');

try {
	if (isset($_POST['token'])) {
		$token = SafeString::safe($_POST['token']);
		$proyectos = (new Proyecto())->buscarProyectos($token);
		echo json_encode(array("status"=>"ok","proyectos"=>$proyectos));
	} else {
		echo json_encode(array("status"=>"error","clave"=>"token","explicacion"=>"No existe token"));
	}
} catch (tokenInvalido $e) {
	echo json_encode(array("status"=>"error","clave"=>"token","explicacion"=>"Token invalido"));
} catch (errorConBaseDeDatos $e) {
	echo json_encode(array("status"=>"error","clave"=>"db","explicacion"=>$e->getMessage()));
} catch (Exception $e) {
	echo json_encode(array("status"=>"error","clave"=>"desconocido","explicacion"=>$e->getMessage()));
}
?>