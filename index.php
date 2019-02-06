<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'proyecto');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Authorization, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

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


// Crear Contactos
$app->post('/contacto', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	var_dump();

	if(!isset($data['nombres'])){
		$data['nombre']=null;
	}
	if(!isset($data['apellidos'])){
		$data['apellidos']=null;
	}
	if(!isset($data['telefono'])){
		$data['telefono']=null;
	}
	if(!isset($data['ciudad'])){
		$data['ciudad']=null;
	}
	if(!isset($data['direccion'])){
		$data['direccion']=null;
	}
	if(!isset($data['country'])){
		$data['country']=null;
	}
	if(!isset($data['id_usuario'])){
		$data['id_usuario']=null;
	}
	$telefono = intval($data['telefono']);
	$id_usuario = intval($data['id_usuario']);
	$query = "INSERT INTO contactos VALUES(NULL,".
			 "'{$data['nombres']}',".
			 "'{$data['apellidos']}',".
			 "'{$data['ciudad']}',".
			 "'{$data['direccion']}',".
			 "{$data['telefono']},".
			 "'{$data['country']}',".
			 "$id_usuario".
			 ");";		 
	$insert = $db->query($query);
		if($insert){
			$result = array(
				'status' => 'success',
				'code'	 => 200,
				'message' => 'contacto creado correctamente'
			);
		} else {
			$result = array(
				'status' => 'error',
				'code'	 => 404,
				'message' => 'el contatcto NO se ha creado'
			);
		}
		echo json_encode($result);
});

//traer contactos
$app->post('/contactos', function() use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	if(!isset($data['id_usuario'])){
		$data['id_usuario']=1;
	}
	$sql = "SELECT nombres,apellidos,ciudad,direccion,telefono,pais FROM contactos WHERE id_usuario = {$data['id_usuario']}";
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
	
/* login */
$app->post('/login', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);



	if (!isset($data['user']) && !isset($data['pass']) ) {
		$error = "El usuario o la contraseña deben contener caracteres";
	}else if(!isset($data['user'])){
		$error = "El usuario debe contener minimo 5 caracteres";
	}else if(!isset($data['pass']) ){
		$error = "la contraseña debe contener minimo de 5 caracteres";
	}else{
		$pass = strlen($data['pass']);
		if ($pass < 5) {
			$error = "La contraseña tiene ".$pass." caracteres debe contener como minimo 5";
		}else {

		$error = "El usuario o la contraseña son incorrectos";
		$sql = "SELECT u.id_usu, u.usuario, u.id_rol, e.documento, s.id_sede
		FROM usuario u
		INNER JOIN empleado e 
		ON e.documento = u.documento_empleado
		INNER JOIN sede s 
		ON s.id_sede = e.id_sede 
		WHERE usuario = '{$data['user']}' AND contrasena = '{$data['pass']}'";
		$login = $db->query($sql);
		$usser = $login->fetch_assoc();
		}
	}
		if(isset($usser)){
			$result = array(
				'status' 	=> 'success',
				'code'		=> 200,
				'data' 	=> $usser
			);
		} else {
			$result = array(
				'status' 	=> 'error',
				'code'		=> 404,
				'message' 	=> $error
			);
		}
	echo json_encode($result);
});



/* -------------------------------------------  entrada usuarios ----------------------- */

//total de horas trabajadas
$app->get('/horas-totales/:documento', function($documento) use($app, $db) {
	$query = "SELECT SUM(TIME_TO_SEC(dif)) as segundos FROM movimiento WHERE documento_empleado = ".$documento;
	$file = $db->query($query);
	$time = $file->fetch_assoc();
	$segundosini = intval($time['segundos']);
	$tiempoDia = 31800;
	$tiempoMes = ($tiempoDia * 22);
	$tiempoSemana = 86400 * 30;
	$total = $tiempoSemana-$tiempoMes;
	$horas = strval(floor($segundosini/3600));
	$minutos = strval(floor(($segundosini-($horas*3600))/60));
	$segundos = strval($segundosini-($horas*3600)-($minutos*60));
	if ($segundos<= 9) {
		$segundos = ('0'.$segundos);
	}
	$tiempo = $horas.$minutos.$segundos;
	echo $tiempo;
});

//Guardar entrada de usuario 
$app->post('/usuario-entrada', function() use($app, $db){
	
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	$consulta = "SELECT id_mov,entrada,salida,id_sede from movimiento WHERE documento_empleado = {$data['documento']} AND id_sede = {$data['id_sede']} AND DATE(entrada) = CURRENT_DATE()  ORDER BY id_mov DESC limit 1";

	$consultaid = $db->query($consulta);
	$fila = $consultaid->fetch_assoc();

	$id = $fila["id_mov"];
	$entrada = $fila["entrada"];
	$salida = $fila["salida"];
	$sede = $fila["id_sede"];

	if (isset($salida) && isset($entrada) && isset($sede) or !isset($salida) && !isset($entrada) && !isset($sede) ) {
		
		$query = "INSERT INTO movimiento (id_mov, entrada, id_sede, documento_empleado) VALUES (NULL, CURRENT_TIMESTAMP(),{$data['id_sede']}, {$data['documento']})";
		$insert = $db->query($query);

		if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'empleado ingreso correctamente');
		}else {
			$result = array(
			'status' => 'error',
			'code'	 => 404,
			'message' => 'empleado NO ingreso'
		);
	}} else {
		$queryU = "UPDATE movimiento SET salida = CURRENT_TIMESTAMP() WHERE id_mov = $id";
		$insertU = $db->query($queryU);	
			$fechas = "SELECT TIMEDIFF(salida,entrada)+0 as dif from movimiento WHERE documento_empleado = {$data['documento']} 
			ORDER BY id_mov DESC limit 1";		
			$min = $db->query($fechas);
			$date = $min->fetch_assoc();
			$dif = floatval($date['dif']);
			$fechass = "UPDATE movimiento set dif = $dif WHERE id_mov = $id";
			$diff = $db->query($fechass);
		// }

		if($diff){
				$result = array(
					'status' => 'success',
					'code'	 => 200,
					'message' => 'usuario salio correctamente');
			}else {
				$result = array(
					'status' => 'error',
					'code'	 => 404,
					'message' => 'usuario NO salio'
				);
			}
		}
	echo json_encode($result);
});

/* listar entradas de usuario */
$app->post('/entrada-usuario', function() use($db, $app){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	$sql = "SELECT m.entrada, m.salida, m.dif, m.documento_empleado, e.nombres, e.apellidos, s.nom_sede
	FROM movimiento m 
	INNER JOIN empleado e 
	ON m.documento_empleado = e.documento 
	INNER JOIN sede s
	ON m.id_sede = s.id_sede
	WHERE DATE(m.entrada) = CURRENT_DATE() AND m.id_sede = {$data['id_sede']} ";
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

/* <------------------------------------------> */


/* traer roles */

$app->get('/roles', function() use($db, $app){
	$sql = 'SELECT * FROM rol ORDER BY id_rol DESC;';
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

/* taer empleados todos*/
$app->get('/empleados', function() use($db, $app) {
	$sql = "SELECT e.documento,e.nombres,e.apellidos,e.telefono,e.correo,s.nom_sede
	FROM empleado e 
	INNER JOIN sede s 
	ON e.id_sede = s.id_sede";
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

/* taer sedes */
$app->get('/sedes', function() use($db, $app) {
	$sql = 'SELECT id_sede,nom_sede  FROM sede';
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


/* traer usuarios */
$app->post('/usuarios', function() use($db, $app) {
	$json = $app->request->post('json');
	$data = json_decode($json, true);	
	$sql = 'SELECT u.usuario, r.nom_rol, e.nombres, e.apellidos, e.documento  
	FROM usuario u 
	INNER JOIN rol r
	ON u.id_rol = r.id_rol
	INNER JOIN empleado e
	ON u.documento_empleado = e.documento';
	if(isset($data['documento'])){
		$sql .= " WHERE e.documento = {$data['documento']} ";
	} 	
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

/* crear empleados */
$app->post('/crear-empleado', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	$sql = "INSERT INTO empleado VALUES ({$data['documento']},'{$data['nombres']}','{$data['apellidos']}',{$data['telefono']},'{$data['correo']}',{$data['sede']})";
	$insert = $db->query($sql);	
	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'empleado creado correctamente'
		);
	} else {
		$result = array(
			'status' => 'error',
			'code'	 => 404,
			'message' => 'el empleado NO! se ha creado'
		);
	}
	echo json_encode($result);
});

/*crear usuarios*/

$app->post('/crear-usuario', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);
	if(!isset($data['user'])){
		$data['user']=null;
	}
	if(!isset($data['pass'])){
		$data['pass']=null;
	}
	if(!isset($data['rol'])){
		$data['rol']=null;
	}
	if(!isset($data['documento'])){
		$data['documento']=null;
	}

	$query = "INSERT INTO usuario VALUES(NULL,".
			 "'{$data['user']}',".
			 "'{$data['pass']}',".
			 "'{$data['rol']}',".
			 "'{$data['documento']}'".
			 ");"; 		 
	$insert = $db->query($query);	
	if($insert){
		$result = array(
			'status' => 'success',
			'code'	 => 200,
			'message' => 'usuario creado correctamente'
		);
	} else {
		$result = array(
			'status' => 'error',
			'code'	 => 404,
			'message' => 'el usuario NO se ha creado'
		);
	}
	echo json_encode($result);
});


/* busquedas para repote admin */
$app->post('/filtro-empleado', function() use($app, $db){
	$json = $app->request->post('json');
	$data = json_decode($json, true);

	if (isset($data['DateOne']) && !isset($data['DateTwo'])) {
		$dateOne = date_create($data['DateOne'])->format('Y-m-d');	
	}else if(isset($data['DateTwo']) && !isset($data['DateOne'])) {
		$dateTwo = date_create($data['DateTwo'])->format('Y-m-d');	
	}else if(isset($data['DateOne']) && isset($data['DateTwo'])) {
		$dateOne = date_create($data['DateOne'])->format('Y-m-d');
		$dateTwo = date_create($data['DateTwo'])->format('Y-m-d');
	}

	$query = "SELECT e.documento, e.nombres, e.apellidos, s.nom_sede, m.entrada, m.salida, m.dif
			  FROM movimiento m 
			  INNER JOIN sede s 
			  ON m.id_sede = s.id_sede 
			  INNER JOIN empleado e 
			  ON e.documento = m.documento_empleado";

 			  if(isset($data['documento']) && !isset($data['DateOne']) && !isset($data['sede']) ){//solo documento
			 	    $query .= " WHERE e.documento = {$data['documento']} ";
			  }else if(isset($data['documento']) && isset($data['DateOne']) && !isset($data['DateTwo'])) {//documento y fecha1
			  	    $query .= " WHERE e.documento = {$data['documento']} AND DATE(m.entrada) = '$dateOne'";
			  }else if(isset($data['documento']) && isset($data['DateOne']) && isset($data['sede']) && !isset($data['DateTwo'])) {//documento sede y fecha1
				    $query .= " WHERE e.documento = {$data['documento']} AND m.id_sede = {$data['sede']} AND DATE(m.entrada) = '$dateOne'";
			  }else if(isset($data['documento']) && isset($data['DateOne']) && isset($data['DateTwo']) && !isset($data['sede'])) {//documento y fecha1 y fecha2
				    $query .= " WHERE e.documento = {$data['documento']} AND DATE(m.entrada) BETWEEN '$dateOne' AND '$dateTwo'";  
			  }else if(isset($data['documento']) && isset($data['DateOne']) && isset($data['DateTwo']) && isset($data['sede'])) {//dcoumento fecha1 fecha2 sede	
				    $query .= " WHERE e.documento = {$data['documento']} AND m.id_sede = {$data['sede']} AND DATE(m.entrada) BETWEEN '$dateOne' AND '$dateTwo'";
			  }else if(isset($data['sede']) && !isset($data['documento']) && !isset($data['DateOne'])) {//solo sede
					$query .= " WHERE m.id_sede = {$data['sede']}";  
			  }else if(isset($data['documento']) && isset($data['sede']) && !isset($data['DateOne'])) {//documento y sede
					$query .= " WHERE e.documento = {$data['documento']} AND m.id_sede = {$data['sede']}";
			  }else if(isset($data['DateOne']) && isset($data['sede']) && !isset($data['documento']) && !isset($data['DateTwo'])) {//sede y fecha
					 $query .= " WHERE m.id_sede = {$data['sede']} AND DATE(m.entrada) = '$dateOne'";
			  }else if(isset($data['DateOne']) && isset($data['sede']) && isset($data['DateTwo']) && !isset($data['documento'])) {//sede y fecha1 fecha2 
				$query .= " WHERE e.documento = {$data['documento']} AND m.id_sede = {$data['sede']}";
			  }else if(isset($data['DateOne']) && isset($data['DateTwo']) && !isset($data['documento']) && !isset($data['sede'])) {//fecha1 fecha2 
				$query .= " WHERE DATE(m.entrada) BETWEEN '$dateOne' AND '$dateTwo'";
		 	  }else { //fecha actual
				    $query .= " WHERE DATE(m.entrada) = CURRENT_DATE()";
			  }	
			  $sql = $db->query($query);
			  $productos = array();
			  while ($producto = $sql->fetch_assoc()) {
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