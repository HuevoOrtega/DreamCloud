<?php
require_once dirname ( __FILE__ ) . "/BaseDeDatos.php";

class UsuarioDB extends BaseDeDatos{
	
	const LEER_NOMBRE_DE_USUARIO = "SELECT nombreDeUsuario AS nombreDeUsuario FROM Usuario WHERE nombreDeUsuario = '%s';";
	const LEER_EMAIL = "SELECT email AS email FROM Usuario WHERE email = '%s';";
	const LEER_USUARIO_POR_NOMBRE = "SELECT email AS email FROM Usuario WHERE nombreDeUsuario = '%s';";
	
	const AGREGAR_USUARIO = "INSERT INTO Usuario (nombreDeUsuario, nombre, apellido, fechaDeNacimiento, email, contrasena) 
			VALUES ('%s', '%s', '%s', '%s', '%s', SHA2(MD5('%s'),512))";
	
	const REVISAR_CLAVES = "SELECT * FROM Usuario WHERE (nombreDeUsuario = '%s' OR email = '%s') AND (contrasena = SHA2(MD5('%s'),512));";
	const CREAR_SESION= "INSERT INTO Sesion_Usuario (token, fecha, email)
			VALUES ('%s', NOW(), '%s');";
	
	const REVISAR_TOKEN = "SELECT token AS token FROM Sesion_Usuario WHERE token = '%s';";
	
	const ACTUALIZAR_TOKEN = "UPDATE Sesion_Usuario SET fecha = NOW() WHERE token = '%s'";
	
	const LEER_CUENTA_TOKEN = "SELECT nombre AS nombre, apellido AS apellido, nombreDeUsuario AS nombreDeUsuario, descripcion AS descripcion, Usuario.id AS id,
			Usuario.telefono, Usuario.celular, Usuario.fechaDeNacimiento, Usuario.email, Usuario.avatar
			FROM Sesion_Usuario 
			LEFT JOIN Usuario 
			ON Sesion_Usuario.email = Usuario.email
			WHERE token = '%s'";
	const LEER_CUENTA_ID = "SELECT nombre AS nombre, apellido AS apellido, nombreDeUsuario AS nombreDeUsuario, descripcion AS descripcion, Usuario.id AS id,
			Usuario.telefono, Usuario.celular, Usuario.fechaDeNacimiento, Usuario.email, Usuario.avatar
			FROM Usuario
			WHERE id = '%s'";
	
	const LEER_CALIFICACION = "SELECT AVG(calificacion) AS calificacionUsuario 
			FROM Usuario 
			LEFT JOIN Usuario_tiene_Proyecto
			ON Usuario_tiene_Proyecto.idUsuario = Usuario.id
			LEFT JOIN Proyecto
			ON Proyecto.id = Usuario_tiene_Proyecto.idProyecto
			LEFT JOIN Trabajo
			ON Trabajo.idProyecto = Proyecto.id
			LEFT JOIN Resena
			ON Resena.idTrabajo = Trabajo.id
			WHERE Usuario.id = '%s'";
	
	const LEER_NEWSFEED = "(SELECT * FROM (SELECT Proyecto.id AS proyecto, Proyecto.titulo, Proyecto.sinopsis, 
			Genero.nombreESP AS genero, SubCategoria.nombreESP AS subcategoria, Categoria.nombreESP AS categoria, 
			aprobado, revisando, Trabajo.id AS idDream FROM 
			Usuario AS u1
			LEFT JOIN Usuario_sigue_Usuario
			ON u1.id = Usuario_sigue_Usuario.idUsuarioSeguidor
			LEFT JOIN Usuario AS u2
			ON Usuario_sigue_Usuario.idUsuarioSeguido = u2.id
			LEFT JOIN Usuario_tiene_Proyecto
			ON u2.id = Usuario_tiene_Proyecto.idUsuario
			LEFT JOIN Proyecto
			ON Usuario_tiene_Proyecto.idProyecto = Proyecto.id
			LEFT JOIN Genero
			ON Proyecto.idGenero = Genero.id
			LEFT JOIN SubCategoria
			ON Proyecto.idSubCategoria = SubCategoria.id
			LEFT JOIN Categoria
			ON SubCategoria.idCategoria = Categoria.id
			LEFT JOIN Trabajo
			ON Proyecto.id = Trabajo.idProyecto
			WHERE u1.id = '%s' AND Proyecto.id IS NOT NULL
			ORDER BY Trabajo.fecha DESC) AS help
			GROUP BY proyecto)
			UNION
			(SELECT * FROM (SELECT Proyecto.id AS proyecto, Proyecto.titulo, Proyecto.sinopsis, 
			Genero.nombreESP AS genero, SubCategoria.nombreESP AS subcategoria, Categoria.nombreESP AS categoria, 
			aprobado, revisando, Trabajo.id AS idDream FROM 
			Usuario 
			LEFT JOIN Usuario_sigue_Proyecto
			ON Usuario.id = Usuario_sigue_Proyecto.idUsuario
			LEFT JOIN Proyecto
			ON Usuario_sigue_Proyecto.idProyecto = Proyecto.id
			LEFT JOIN Genero
			ON Proyecto.idGenero = Genero.id
			LEFT JOIN SubCategoria
			ON Proyecto.idSubCategoria = SubCategoria.id
			LEFT JOIN Categoria
			ON SubCategoria.idCategoria = Categoria.id
			LEFT JOIN Trabajo
			ON Proyecto.id = Trabajo.idProyecto
			WHERE Usuario.id = '%s' AND Proyecto.id IS NOT NULL
			ORDER BY Trabajo.fecha DESC) AS help
			GROUP BY proyecto)";
	
	const CAMBIAR_DATOS_USUARIO = "UPDATE Usuario SET nombre = '%s', apellido = '%s',email = '%s', telefono = '%s', celular = '%s', 
			descripcion = '%s',
			fechaDeNacimiento = '%s' 
			WHERE id = '%s'";
	
	const CERRAR_SESION = "DELETE FROM Sesion_Usuario WHERE token = '%s'";
	const BUSCAR_USUARIOS = "SELECT id, nombreDeUsuario From Usuario WHERE nombreDeUsuario LIKE '%s'";
			
	const USUARIOS_SE_SIGUEN = "SELECT * FROM Usuario_sigue_Usuario 
			WHERE idUsuarioSeguido = %s 
			AND idUsuarioSeguidor = %s";
	const SEGUIR = "INSERT INTO Usuario_sigue_Usuario (idUsuarioSeguido, idUsuarioSeguidor)
			VALUES (%s, %s)";
	const DEJAR_DE_SEGUIR = "DELETE FROM Usuario_sigue_Usuario WHERE idUsuarioSeguido = %s AND idUsuarioSeguidor = %s";
	const CONTACTAR = "INSERT INTO Contacto (idUsuarioAContactar, idUsuarioContactando, idEmpresaContactando, mensaje, fecha)
			VALUES (%s, %s, %s, '%s', NOW())";
	
	const CAMBIAR_CONTRASEÑA = "UPDATE Usuario SET contrasena =  SHA2(MD5('%s'),512) WHERE id = %s AND contrasena =  SHA2(MD5('%s'),512)";
	
	const GUARDAR_AVATAR = "UPDATE Usuario SET avatar = '%s' WHERE id = %s";
	
	const GUARDAR_RECUPERAR_CONTRASEÑA = "INSERT INTO RecuperarContrasena (clave, email, fecha)
			VALUES ('%s', '%s', NOW())";
	const LEER_CAMBIAR_CONTRASEÑA = "SELECT email FROM RecuperarContrasena WHERE clave = '%s'";
	const REESTABLECER_CONTRASEÑA= "UPDATE Usuario SET contrasena = SHA2(MD5('%s'),512) WHERE email = '%s'";
	const BORRAR_REESTABLECER_CONTRASEÑA = "DELETE FROM RecuperarContrasena WHERE clave = '%s' AND email = '%s'";
	
	
	function existeNombreDeUsuario($nombreUsuario)
	{
		$query = sprintf(self::LEER_NOMBRE_DE_USUARIO, $nombreUsuario);
		$resultado = $this->ejecutarQuery($query);
		return $this->resultadoTieneValores($resultado);
	}
	
	function existeUsuarioEmail($email)
	{
		$query = sprintf(self::LEER_EMAIL, $email);
		$resultado = $this->ejecutarQuery($query);		
		return $this->resultadoTieneValores($resultado);
	}
	
	function agregarUsuario($nombreUsuario, $nombre, $apellido, $fechaNacimiento, $email, $contraseña)
	{
		$query = sprintf(self::AGREGAR_USUARIO, $nombreUsuario, $nombre, $apellido, $fechaNacimiento, $email, $contraseña);
		$this->ejecutarQuery($query);
	}
	
	function crearSesion($email)
	{
		$token = md5 (uniqid(mt_rand(), true));
		$query = sprintf(self::CREAR_SESION, $token, $email);
		$this->ejecutarQuery($query);
		return $token;
	}
	
	function clavesCoinciden($emailONombre, $contraseña)
	{
		sleep(1);
		$query = sprintf(self::REVISAR_CLAVES, $emailONombre, $emailONombre, $contraseña);
		$resultado = $this->ejecutarQuery($query);
		return $this->resultadoTieneValores($resultado);
	}
	
	function leerUsuarioPorNombre($usuario)
	{
		$query = sprintf(self::LEER_USUARIO_POR_NOMBRE, $usuario);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	
	function existeToken($token)
	{
		$query = sprintf(self::REVISAR_TOKEN, $token);
		$resultado = $this->ejecutarQuery($query);
		return $this->resultadoTieneValores($resultado);
	}
	
	function actualizaToken($token)
	{
		$query = sprintf(self::ACTUALIZAR_TOKEN, $token);
		$this->ejecutarQuery($query);
	}
	
	function leerCuentaToken($token)
	{
		$query = sprintf(self::LEER_CUENTA_TOKEN, $token);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	
	function leerCuentaId($id)
	{
		$query = sprintf(self::LEER_CUENTA_ID, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	
	function leerCalificacion($id)
	{
		$query = sprintf(self::LEER_CALIFICACION, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	
	
	function leerNewsFeed($id)
	{
		$query = sprintf(self::LEER_NEWSFEED, $id, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function cambiarDatosUsuario($id, $nombre, $apellido, $email, $telefono, $celular, $descripcion, $fechaNacimiento)
	{
		$query = sprintf(self::CAMBIAR_DATOS_USUARIO, $nombre, $apellido, $email, $telefono, $celular, $descripcion, $fechaNacimiento, $id);
		$this->ejecutarQuery($query);
	}
	function cerrarSesion($token)
	{
		$query = sprintf(self::CERRAR_SESION, $token);
		$this->ejecutarQuery($query);
	}
	function buscarUsuarios($nombreUsuario)
	{
		$query = "SELECT id AS id, nombreDeUsuario AS nombreDeUsuario, 'usuario' AS tipo From Usuario WHERE nombreDeUsuario LIKE '%$nombreUsuario%'
			UNION
			SELECT id AS id, nombreDeUsuario AS nombreDeUsuario, 'empresa' AS tipo From Empresa WHERE nombreDeUsuario LIKE '%$nombreUsuario%'";
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	function usuariosSeSiguen($id, $idSeguidor)
	{
		$query = sprintf(self::USUARIOS_SE_SIGUEN, $id, $idSeguidor);
		$resultado = $this->ejecutarQuery($query);
		return $this->resultadoTieneValores($resultado);
	}
	function seguir($id, $usuario)
	{
		$query = sprintf(self::SEGUIR, $usuario, $id);
		$this->ejecutarQuery($query);
	}
	
	function dejarDeSeguir($id, $usuario)
	{
		$query = sprintf(self::DEJAR_DE_SEGUIR, $usuario, $id);
		$this->ejecutarQuery($query);
	}
	
	function contactar($idUsuario, $idEmpresa, $usuario, $mensaje)
	{
		$query = sprintf(self::CONTACTAR, $usuario, $idUsuario, $idEmpresa, $mensaje);
		$this->ejecutarQuery($query);
	}
	
	function cambiarContraseña($id, $contraseña, $contraseñaNueva)
	{
		$query = sprintf(self::CAMBIAR_CONTRASEÑA, $contraseñaNueva, $id, $contraseña);
		$this->ejecutarQuery($query);
		return $this->mysqli->affected_rows;
	}
	
	function guardarDireccionImagen($id, $nombreImagen)
	{
		$query = sprintf(self::GUARDAR_AVATAR, $nombreImagen, $id);
		$this->ejecutarQuery($query);
	}
	
	function recuperarContraseña($email, $clave)
	{
		$query = sprintf(self::GUARDAR_RECUPERAR_CONTRASEÑA, $clave, $email);
		$this->ejecutarQuery($query);
	}
	
	function leerEmailRecuperarContraseña($clave)
	{
		$query = sprintf(self::LEER_CAMBIAR_CONTRASEÑA, $clave);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_row()[0];
	}
	function reestablecerContraseña($contraseña, $email)
	{
		$query = sprintf(self::REESTABLECER_CONTRASEÑA, $contraseña, $email);
		$resultado = $this->ejecutarQuery($query);
	}
	function borrarDeReestablecer($clave, $email)
	{
		$query = sprintf(self::BORRAR_REESTABLECER_CONTRASEÑA, $clave, $email);
		$resultado = $this->ejecutarQuery($query);
	}
}
?>