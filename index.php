<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app, $db){
	echo "Hola mundo desde Slim PHP";
});

$app->get("/probando", function() use($app){
	echo "OTRO TEXTO CUALQUIERA";
});

// LISTAR TODOS LOS PRODUCTOS
$app->get('/productos', function() use($db, $app){
	$sql = 'SELECT * FROM productos ORDER BY id DESC;';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

// DEVOLVER UN SOLO PRODUCTO
$app->get('/producto/:id', function($id) use($db, $app){
	$sql = 'SELECT * FROM productos WHERE id = '.$id;
	$query = $db->query($sql);

	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'Producto no disponible'
	);

	if($query->num_rows == 1){
		$producto = $query->fetch_assoc();

		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'data' 	=> $producto
		);
	}

	echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get('/delete-producto/:id', function($id) use($db, $app){
	$sql = 'DELETE FROM productos WHERE id = '.$id;
	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El producto se ha eliminado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El producto no se ha eliminado!!'
		);
	}

	echo json_encode($result);
});

// ACTUALIZAR UN PRODUCTO
$app->post('/update-producto/:id', function($id) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	$sql = "UPDATE productos SET ".
		   "nombre = '{$data["nombre"]}', ".
		   "descripcion = '{$data["descripcion"]}', ";

	if(isset($data['imagen'])){
 		$sql .= "imagen = '{$data["imagen"]}', ";
	}

	$sql .=	"precio = '{$data["precio"]}' WHERE id = {$id}";


	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El producto se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El producto no se ha actualizado!!'
		);
	}

	echo json_encode($result);

});

// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function() use($db, $app){
	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'El archivo no ha podido subirse'
	);
	
	if(isset($_FILES['uploads'])){
		$piramideUploader = new PiramideUploader();

		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
		$file = $piramideUploader->getInfoFile();
		$file_name = $file['complete_name'];

		if(isset($upload) && $upload["uploaded"] == false){
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> 'El archivo no ha podido subirse'
			);
		}else{
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'message' 	=> 'El archivo se ha subido',
				'filename'  => $file_name
			);
		}
	}

	echo json_encode($result);
});

// GUARDAR PRODUCTOS
$app->post('/productos', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(!isset($data['nombre'])){
		$data['nombre']=null;
	}

	if(!isset($data['descripcion'])){
		$data['descripcion']=null;
	}

	if(!isset($data['precio'])){
		$data['precio']=null;
	}

	if(!isset($data['imagen'])){
		$data['imagen']=null;
	}

	$query = "INSERT INTO productos VALUES(NULL,".
			 "'{$data['nombre']}',".
			 "'{$data['descripcion']}',".
			 "'{$data['precio']}',".
			 "'{$data['imagen']}'".
			 ");";

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'Producto NO se ha creado'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'Producto creado correctamente'
		);
	}

	echo json_encode($result);
});

//Guardar entrada 
$app->post('/productos-entrada', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(!isset($data['create_at'])){
		$data['create_at']='';
	}

	if(!isset($data['id_producto'])){
		$data['id_producto']=NULL;
	}

	$query = "INSERT INTO entradaproductos VALUES (NULL, CURRENT_TIMESTAMP(), {$data['id_producto']})";

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'Producto NO se ha creado'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'Producto creado correctamente'
		);
	}

	echo json_encode($result);
});

/* listar entradas */
$app->get('/entrada-list', function() use($db, $app){
	$sql = 'SELECT entradaproductos.id, entradaproductos.create_at, entradaproductos.id_producto, productos.nombre, productos.precio, productos.id, productos.imagen FROM entradaproductos INNER JOIN productos ON entradaproductos.id_producto = productos.id ORDER BY entradaproductos.id_producto DESC;';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

/* listar 1 solo producto */
$app->get('/entrada/:id', function($id) use($db, $app){
	$sql = 'SELECT * FROM entradaproductos WHERE id = '.$id;
	$query = $db->query($sql);

	$result = array(
		'status' 	=> 'error',
		'code'		=> 404,
		'message' 	=> 'Producto no disponible'
	);

	if($query->num_rows == 1){
		$producto = $query->fetch_assoc();

		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'data' 	=> $producto
		);
	}

	echo json_encode($result);
});

/* crear usuarios */

$app->post('/usuario', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(!isset($data['usuario'])){
		$data['usuario']=NULL;
	}
	if(!isset($data['nombres'])){
		$data['nombres']=NULL;
	}
	if(!isset($data['apellidos'])){
		$data['apellidos']=NULL;
	}
	if(!isset($data['contrasena'])){
		$data['contrasena']=NULL;
	}
	if(!isset($data['rol_id'])){
		$data['rol_id']=NULL;
	}
	if(!isset($data['direccion'])){
		$data['direccion']=NULL;
	}
	if(!isset($data['documento'])){
		$data['documento']=NULL;
	}
	if(!isset($data['telefono'])){
		$data['telefono']=NULL;
	}
	if(!isset($data['foto'])){
		$data['foto']=NULL;
	}




	$query = "INSERT INTO usuario VALUES('{$data['usuario']}',".
	"'{$data['nombres']}',".
	"'{$data['apellidos']}',".
	"'{$data['contrasena']}',".
	"'{$data['rol_id']}',".
	"'{$data['direccion']}',".
	"'{$data['documento']}',".
	"'{$data['telefono']}',".
	"'{$data['foto']}'".
	");";

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'usuario NO se ha creado'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'usuario creado correctamente'
		);
	}

	echo json_encode($result);
});

/* traer todos los usuarios */
$app->get('/usuarios', function() use($db, $app){
 	$sql = 'SELECT usuario.usuario, usuario.nombres, usuario.apellidos, usuario.rol_id, usuario.direccion, usuario.telefono, usuario.documento, rol.id, rol.nombre FROM usuario INNER JOIN rol ON usuario.rol_id = rol.id ORDER BY id DESC';
	$query = $db->query($sql); 

	$query = $db->query($sql);
	$productos = array();

	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

/* eliminar usuarios */
$app->get('/delete-usuario/:documento', function($documento) use($db, $app){
	$sql = 'DELETE FROM usuario WHERE documento = '.$documento;
	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El usuario se ha eliminado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> 'El usuario no se ha eliminado!!'
		);
	}

	echo json_encode($result);
});

/* actualizar un usuario */
$app->post('/update-usuario/:documento', function($documento) use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if(isset($data['contrasena'])){
	$sql =	"UPDATE usuario SET ".
	    "usuario = '{$data["usuario"]}',".
	    "nombres = '{$data["nombres"]}',".
		"apellidos = '{$data["apellidos"]}',".
		"contrasena = '{$data["contrasena"]}',".
		"rol_id = '{$data["rol_id"]}',".
		"direccion = '{$data["direccion"]}', ".
		"documento = '{$data["documento"]}', ".
		"telefono = '{$data["telefono"]}' WHERE documento = {$documento}";
	} else {
	$sql =	"UPDATE usuario SET ".
	    "usuario = '{$data["usuario"]}',".
	    "nombres = '{$data["nombres"]}',".
		"apellidos = '{$data["apellidos"]}',".
		"rol_id = '{$data["rol_id"]}',".
		"direccion = '{$data["direccion"]}', ".
		"documento = '{$data["documento"]}', ".
		"telefono = '{$data["telefono"]}'  WHERE documento = {$documento}";
	}

	$query = $db->query($sql);

	if($query){
		$result = array(
			'status' 	=> 'success',
			'code'		=> 200,
			'message' 	=> 'El usuario se ha actualizado correctamente!!'
		);
	}else{
		$result = array(
			'status' 	=> 'error',
			'code'		=> 404,
			'message' 	=> $sql
		);
	}

	echo json_encode($result);

});

//Guardar entrada de usuario 
$app->post('/usuario-entrada', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);


	if(!isset($data['documento_usuario'])){
		$data['documento_usuario']=NULL;
	}

	 $contador = "SELECT * FROM entradausuario WHERE documento_usuario = {$data['documento_usuario']}";
	 $consulta = "SELECT * from entradausuario  WHERE documento_usuario = {$data['documento_usuario']} ORDER BY id DESC limit 1";

	 $consultaid = $db->query($consulta);
	 $contarr = $db->query($contador);
	 $fila = $consultaid->fetch_assoc();
	 $id = $fila["id"];
	 $salida = $fila["salida"];
	 $num = $contarr->num_rows;

	
		if (isset($salida)) {
			$query = "INSERT INTO entradausuario (id, entrada, documento_usuario) VALUES (NULL, CURRENT_TIMESTAMP(), {$data['documento_usuario']})";
		}else{
			$query = "UPDATE entradausuario SET salida = CURRENT_TIMESTAMP() WHERE id = $id";
		}
	 


	var_dump($query);

	$insert = $db->query($query);

	$result = array(
		'status' => 'error',
		'code'	 => 404,
		'message' => 'usuario NO ingreso'
	);

	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'usuario ingreso correctamente'
		);
	}

	echo json_encode($result);
});

/* listar entradas de usuario */
$app->get('/entrada-usuario', function() use($db, $app){
	$sql = 'SELECT entradausuario.id, entradausuario.entrada, entradausuario.salida, entradausuario.documento_usuario, usuario.usuario, usuario.nombres, usuario.apellidos, usuario.documento FROM entradausuario INNER JOIN usuario ON entradausuario.documento_usuario = usuario.documento';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

/* traer roles */
$app->get('/roles', function() use($db, $app){
	$sql = 'SELECT * FROM rol ORDER BY id DESC;';
	$query = $db->query($sql);

	$productos = array();
	while ($producto = $query->fetch_assoc()) {
		$productos[] = $producto;
	}

	$result = array(
			'status' => 'success',
			'code'	 => 200,
			'data' => $productos
		);

	echo json_encode($result);
});

$app->run();