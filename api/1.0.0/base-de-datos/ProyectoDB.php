<?php
require_once dirname ( __FILE__ ) . "/BaseDeDatos.php";

class ProyectoDB extends BaseDeDatos {
	
	const LEER_PROYECTOS = "SELECT * FROM (SELECT Proyecto.id AS proyecto, Proyecto.titulo, Proyecto.sinopsis, 
			Genero.nombreESP AS genero, SubCategoria.nombreESP AS subcategoria, Categoria.nombreESP AS categoria, 
			aprobado, revisando, Trabajo.id AS idDream FROM 
			Usuario 
			LEFT JOIN Usuario_tiene_Proyecto
			ON Usuario.id = Usuario_tiene_Proyecto.idUsuario
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
			WHERE Usuario.id = '%s' AND Proyecto.id IS NOT NULL
			ORDER BY Trabajo.fecha DESC) AS help
			GROUP BY proyecto";
	const LEER_PROYECTOS_CONVOCATORIA = "SELECT * FROM (SELECT Proyecto.id AS proyecto, Proyecto.titulo, Proyecto.sinopsis, Categoria.nombreESP AS categoria,
			Trabajo.aprobado, Trabajo.revisando, Trabajo.id AS idDream, 
			Usuario.nombre, Usuario.apellido, Usuario.id AS idUsuario, Usuario.email, Usuario.nombreDeUsuario FROM Usuario 
			LEFT JOIN Usuario_tiene_Proyecto
			ON Usuario.id = Usuario_tiene_Proyecto.idUsuario
			LEFT JOIN Proyecto
			ON Usuario_tiene_Proyecto.idProyecto = Proyecto.id
			LEFT JOIN SubCategoria
			ON Proyecto.idSubCategoria = SubCategoria.id
			LEFT JOIN Categoria
			ON SubCategoria.idCategoria = Categoria.id
			LEFT JOIN Trabajo
			ON Proyecto.id = Trabajo.idProyecto
			LEFT JOIN Convocatoria
			ON Proyecto.idConvocatoria = Convocatoria.id 
			WHERE Convocatoria.id = %s
			AND Trabajo.aprobado = 1
			ORDER BY Trabajo.fecha DESC) help
			GROUP BY proyecto";
	const LEER_CALIFICACION_PROYECTO = "SELECT AVG(calificacion) AS calificacion FROM Proyecto 
			LEFT JOIN Trabajo
			ON Proyecto.id = Trabajo.idProyecto
			LEFT JOIN Resena
			ON Trabajo.id = Resena.idTrabajo
			WHERE Proyecto.id = '%s'
			GROUP BY Trabajo.id
			ORDER BY Trabajo.fecha DESC
			";
	
	const LEER_CATEGORIAS = "SELECT id, nombreESP FROM Categoria";
	const LEER_SUBCATEGORIAS = "SELECT SubCategoria.id AS id, SubCategoria.nombreESP AS nombreESP 
			FROM Categoria LEFT JOIN SubCategoria 
			ON Categoria.id = SubCategoria.idCategoria
			WHERE Categoria.id = '%s'";
	const LEER_GENEROS= "SELECT Genero.id AS id, Genero.nombreESP AS nombreESP 
			FROM Categoria LEFT JOIN Categoria_tiene_Genero 
			ON Categoria.id = Categoria_tiene_Genero.idCategoria
			LEFT JOIN Genero
			ON Genero.id = Categoria_tiene_Genero.idGenero
			WHERE Categoria.id = '%s'";
	const NUEVO_PROYECTO = "INSERT INTO Proyecto (idGenero, idSubCategoria, titulo, sinopsis, proposito, fecha, idConvocatoria)
			VALUES ('%s', '%s', '%s', '%s', '%s', NOW(), %s)";
	const NUEVO_TRABAJO= "INSERT INTO Trabajo (idProyecto, direccion, direccionCertificado, fecha, idEstado)
			VALUES ('%s', '%s', '%s', NOW(), '%s')";
	const GUARDAR_REFERENCIA= "INSERT INTO Usuario_tiene_Proyecto (idUsuario, idProyecto)
			VALUES ('%s', '%s')";
	const CAMBIAR_PROPOSITO = "UPDATE Proyecto SET proposito = '%s' WHERE id = '%s'";
	const LEER_TRABAJO = "SELECT Usuario.id AS idUsuario, Usuario.nombreDeUsuario, Usuario.email, Usuario.nombre, Usuario.apellido, Usuario.avatar, 
			Proyecto.id AS idProyecto, Proyecto.titulo, Proyecto.sinopsis, 
			Trabajo.direccion, Trabajo.direccionCertificado, Trabajo.fecha, Trabajo.aprobado, Trabajo.revisando, Trabajo.id AS idTrabajo,
			AVG(Resena.calificacion) AS calificacion
			FROM Usuario
			LEFT JOIN Usuario_tiene_Proyecto
			ON Usuario.id = Usuario_tiene_Proyecto.idUsuario
			LEFT JOIN Proyecto
			ON Usuario_tiene_Proyecto.idProyecto = Proyecto.id
			LEFT JOIN Trabajo
			ON Proyecto.id = Trabajo.idProyecto
			LEFT JOIN Resena
			ON Trabajo.id = Resena.idTrabajo
			WHERE Trabajo.id = %s";
	
	const LEER_RESENAS = "Select Resena.id, Usuario.nombreDeUsuario, Resena.calificacion, Resena.comentario, Resena.titulo 
			FROM Resena LEFT JOIN Usuario
			ON Resena.idUsuario = Usuario.id 
			WHERE Resena.idTrabajo = %s";
	
	CONST GUARDAR_RESENA = "INSERT INTO Resena (idUsuario, idTrabajo, titulo, comentario, calificacion) 
			VALUES (%s, %s, '%s', '%s', '%s')";
	const GUARDAR_SUBCOMENTARIO = "INSERT INTO Comentario (idUsuario, comentario, idResena) 
			VALUES (%s, '%s', %s)";
	const LEER_SUBCOMENTARIOS = "Select Comentario.id, Usuario.nombreDeUsuario, Comentario.comentario
			FROM Comentario LEFT JOIN Usuario
			ON Comentario.idUsuario = Usuario.id 
			LEFT JOIN Resena
			ON Resena.id = Comentario.idResena
			WHERE Resena.idTrabajo = %s";
	const USUARIO_SIGUE_PROYECTO = "SELECT * FROM Usuario 
			LEFT JOIN Usuario_sigue_Proyecto
			ON Usuario_sigue_Proyecto.idUsuario = Usuario.id
			LEFT JOIN Proyecto
			ON Proyecto.id = Usuario_sigue_Proyecto.idProyecto
			LEFT JOIN Trabajo 
			ON Trabajo.idProyecto = Proyecto.id
			WHERE Usuario.id = %s AND Trabajo.id = %s";
	const LEER_PROYECTO_POR_TRABAJO = "SELECT Proyecto.id FROM Proyecto LEFT JOIN Trabajo ON Proyecto.id = Trabajo.idProyecto WHERE Trabajo.id = %s";
	const SEGUIR_PROYECTO = "INSERT INTO Usuario_sigue_Proyecto (idUsuario, idProyecto) 
			VALUES (%s, %s)";
	const DEJAR_SEGUIR_PROYECTO = "DELETE FROM Usuario_sigue_Proyecto WHERE idusuario = %s AND idProyecto = %s";
	
	function buscarProyectos($id)
	{
		$query = sprintf(self::LEER_PROYECTOS, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function buscarProyectosConvocatoria($id)
	{
		$query = sprintf(self::LEER_PROYECTOS_CONVOCATORIA, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function leerCalificacionProyecto($id)
	{
		$query = sprintf(self::LEER_CALIFICACION_PROYECTO, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc()['calificacion'];
	}
	
	function buscarCategorias()
	{
		$query = sprintf(self::LEER_CATEGORIAS);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function buscarSubcategorias($idCategoria)
	{
		$query = sprintf(self::LEER_SUBCATEGORIAS, $idCategoria);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function buscarGeneros($idCategoria)
	{
		$query = sprintf(self::LEER_GENEROS, $idCategoria);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function guardarProyectoNuevo($idGenero, $idSubcategoria, $titulo, $sinopsis, $proposito, $idConvocatoria)
	{
		$query = sprintf(self::NUEVO_PROYECTO, $idGenero, $idSubcategoria, $titulo, $sinopsis, $proposito, $idConvocatoria);
		$this->ejecutarQuery($query);
		return $this->mysqli->insert_id;
	}
	
	function guardarTrabajoNuevo($idProyecto, $ubicacionDestinoTrabajo, $ubicacionDestinoCertificado, $idEstado)
	{
		$query = sprintf(self::NUEVO_TRABAJO, $idProyecto, $ubicacionDestinoTrabajo, $ubicacionDestinoCertificado, $idEstado);
		$this->ejecutarQuery($query);
	}
	
	function guardarReferenciaUsuarioProyecto($id, $idProyecto)
	{
		$query = sprintf(self::GUARDAR_REFERENCIA, $id, $idProyecto);
		$this->ejecutarQuery($query);
	}
	
	function cambiarProposito($idProyecto, $proposito)
	{
		$query = sprintf(self::CAMBIAR_PROPOSITO, $proposito, $idProyecto);
		$this->ejecutarQuery($query);
	}
	function buscarTrabajo($idDream)
	{
		$query = sprintf(self::LEER_TRABAJO, $idDream);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	
	function filtrarProyectos($categoria, $subcategoria, $genero, $extra, $texto)
	{
		$queryArray = array(
				"select" => "SELECT * FROM (SELECT Proyecto.id AS proyecto, Proyecto.titulo, Proyecto.sinopsis,
								aprobado, revisando, Trabajo.id AS idDream, 
								Usuario.nombre, Usuario.apellido, Usuario.id AS idUsuario, Usuario.email, Usuario.nombreDeUsuario, Usuario.avatar AS avatar
								FROM Usuario 
								LEFT JOIN Usuario_tiene_Proyecto
								ON Usuario.id = Usuario_tiene_Proyecto.idUsuario
								LEFT JOIN Proyecto
								ON Usuario_tiene_Proyecto.idProyecto = Proyecto.id
								LEFT JOIN SubCategoria
								ON Proyecto.idSubCategoria = SubCategoria.id
								LEFT JOIN Categoria
								ON SubCategoria.idCategoria = Categoria.id
								LEFT JOIN Trabajo
								ON Proyecto.id = Trabajo.idProyecto",
				"where" => "WHERE Categoria.id = $categoria AND aprobado = 1",
				"subcategoria" => "AND Proyecto.idSubCategoria = '$subcategoria'",
				"genero" => "AND Proyecto.idGenero = '$genero'",
				"texto" => "AND Proyecto.titulo LIKE '%$texto%' OR Proyecto.sinopsis LIKE '%$texto%'",
				"order" => "ORDER BY Trabajo.fecha DESC) help",
				"group" => "GROUP BY proyecto"
		);
		if ($subcategoria == -1) {
			unset($queryArray["subcategoria"]);
		}
		if ($genero == -1) {
			unset($queryArray["genero"]);
		}
		if ($texto == '') {
			unset($queryArray["texto"]);
		}
		$query = implode(' ',$queryArray);
	
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function leerResenas($idDream)
	{
		$query = sprintf(self::LEER_RESENAS, $idDream);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	
	function guardarResena($id, $idDream, $titulo, $comentario, $calificacion)
	{
		$query = sprintf(self::GUARDAR_RESENA, $id, $idDream, $titulo, $comentario, $calificacion);
		$this->ejecutarQuery($query);
	}
	
	function guardarSubcomentario($idUsuario, $subcomentario, $idResena)
	{
		$query = sprintf(self::GUARDAR_SUBCOMENTARIO, $idUsuario, $subcomentario, $idResena);
		$this->ejecutarQuery($query);
	}
	function leerSubcomentarios($id)
	{
		$query = sprintf(self::LEER_SUBCOMENTARIOS, $id);
		$resultado = $this->ejecutarQuery($query);
		return $resultado;
	}
	function usuarioSigueProyecto($id, $idDream)
	{
		$query = sprintf(self::USUARIO_SIGUE_PROYECTO, $id, $idDream);
		$resultado = $this->ejecutarQuery($query);
		return $this->resultadoTieneValores($resultado);
	}
	function buscarProyectoDeTrabajo($idDream)
	{
		$query = sprintf(self::LEER_PROYECTO_POR_TRABAJO, $idDream);
		$resultado = $this->ejecutarQuery($query);
		return $resultado->fetch_assoc();
	}
	function seguir($id, $idProyecto)
	{
		$query = sprintf(self::SEGUIR_PROYECTO, $id, $idProyecto);
		$resultado = $this->ejecutarQuery($query);
	}
	function dejarDeSeguir($id, $idProyecto)
	{
		$query = sprintf(self::DEJAR_SEGUIR_PROYECTO, $id, $idProyecto);
		$resultado = $this->ejecutarQuery($query);
	}
}
?>