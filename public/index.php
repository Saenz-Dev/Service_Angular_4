<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

//Ajustamos la ruta base (BASE PATH)
$app->setBasePath('/curso-angular4-backend');

//Middleware para detectar errores 
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);
// $db = new mysqli('localhost', 'root', '', 'curso_angular4');

// Configuración de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
	die();
}

$app->get("/pruebas", function (Request $request, Response $response) {
	$result = [
		'status' => 'success',
		'code' => 200,
		'message' => 'Llegó la respuesta'
	];
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});


//Guardar productos 

$app->post("/productos", function (Request $request, Response $response, array $args) {
	$body = $request->getParsedBody(); //Aquí lo decodifica de una vez, es como hacer json_decode()
	if (!isset($body['nombre'])) {
		$body['nombre'] = null;
	}
	if (!isset($body['description'])) {
		$body['description'] = null;
	}
	if (!isset($body['precio'])) {
		$body['precio'] = null;
	}
	if (!isset($body['imagen'])) {
		$body['imagen'] = null;
	}
	$pdo = new PDO('mysql:dbname=curso_angular4;host=localhost;port=3013', 'root', ''); //Ajustamos el PDO que es la conexión a la base de datos
	$sql = "INSERT INTO productos (nombre, description, precio, imagen) VALUES (:nombre, :description, :precio, :imagen)"; //Creamos la consulta a la base de datos
	//En este caso con :valor para evitar que haya perdidas de datos con Inyección SQL

	$statement = $pdo->prepare($sql); //Prepara la sentencia SQL 
	$statement->execute([
		':nombre' => $body['nombre'],
		':description' => $body['description'],
		':precio' => $body['precio'],
		':imagen' => $body['imagen']
	]);
	$result = ['status' => 'error', 'code' => 400, 'message' => 'El producto no ha sido creado.'];
	if ($statement->rowCount() > 0) {
		$result = ['status' => 'success', 'code' => 200, 'message' => 'Producto creado exitosamente.'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});


//Listar todos los productos

$app->get('/productos', function (Request $request, Response $response, array $args) {

	$pdo = new PDO('mysql:dbname=curso_angular4;host=localhost;port=3013', 'root', ''); //Ajustamos el PDO que es la conexión a la base de datos
	$sql = 'SELECT * FROM productos ORDER BY id DESC';

	$statement = $pdo->prepare($sql);
	$statement->execute();

	$data = $statement->fetchAll(PDO::FETCH_ASSOC);
	$result = ['status' => 'error', 'code' => 400, 'message' => 'No se ha podido realizar la lista de productos.', 'data' => $data];
	if ($statement->rowCount() > 0) {
		$result = ['status' => 'success', 'code' => 200, 'message' => 'Lista de productos hecha exitosamente.', 'data' => $data];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});

//Devolver un solo producto

$app->get('/producto/{id}', function (Request $request, Response $response, array $args) {
	$pdo = new PDO('mysql:dbname=curso_angular4;host=localhost;port=3013', 'root', '');
	$sql = 'SELECT * FROM productos WHERE id = :id';
	echo $args['id'];
	$statement = $pdo->prepare($sql);
	$statement->execute([
		':id' => $args['id']
	]);
	$data = $statement->fetchAll(PDO::FETCH_ASSOC);

	$result = ['status' => 'error', 'code' => 400, 'message' => 'No se ha encontrado el producto.', 'data' => $data];
	if ($statement->rowCount() > 0) {
		$result = ['status' => 'success', 'code' => 200, 'message' => 'Producto encontrado con exito.', 'data' => $data];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});


//Eliminar un producto

$app->get('/delete-producto/{id}', function (Request $request, Response $response, array $args) {
	$pdo = new PDO('mysql:dbname=curso_angular4;host=localhost;port=3013', 'root', '');
	$sql = 'DELETE FROM productos WHERE id = :id';
	echo $args['id'];
	$statement = $pdo->prepare($sql);
	$statement->execute([
		':id' => $args['id']
	]);
	$data = $statement->fetchAll(PDO::FETCH_ASSOC);

	$result = ['status' => 'error', 'code' => 400, 'message' => 'No se ha eliminado el producto.'];
	if ($statement->rowCount() > 0) {
		$result = ['status' => 'success', 'code' => 200, 'message' => 'El producto se ha eliminado exitosamente.'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});

//Actualizar un producto

$app->post('/update-producto/{id}', function (Request $request, Response $response, array $args) {
	$body = $request->getParsedBody();
	if (!isset($body['nombre'])) {
		$body['nombre'] = null;
	}
	if (!isset($body['description'])) {
		$body['description'] = null;
	}
	if (!isset($body['precio'])) {
		$body['precio'] = null;
	}
	$pdo = new PDO('mysql:dbname=curso_angular4;host=localhost;port=3013', 'root', '');
	$sql = 'UPDATE productos SET nombre = :nombre, description = :description,';
	if (isset($body['imagen'])) {
		$sql .= 'imagen = :imagen, ';
	}
	$sql .= ' precio = :precio WHERE id = :id';

	$statement = $pdo->prepare($sql);
	$statement->execute([
		':nombre' => $body['nombre'],
		':description' => $body['description'],
		':precio' => $body['precio'],
		':imagen' => $body['imagen'],
		':id' => $args['id']
	]);

	$result = ['status' => 'error', 'code' => 400, 'message' => 'El producto no ha sido actualizado.'];
	if ($statement->rowCount() > 0) {
		$result = ['status' => 'success', 'code' => 200, 'message' => 'El producto se ha actualizado exitosamente.'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});

//Subir imagen a un producto

$app->post('/upload-file', function (Request $request, Response $response) {

	if (isset($_FILES['uploads'])) {

		$piramedeUploader = new PiramideUploader();

		$uploaded = $piramedeUploader->upload(
			'img',
			'uploads',
			'uploads',
			['image/jpeg', 'image/png', 'image/gif']
		);

		if (isset($uploaded) && $uploaded['uploaded'] == false) {
			$result = ['status' => 'error', 'code' => 400, 'message' => 'No se subió la imagen'];
		} else {
			$file = $piramedeUploader->getInfoFile();

			$result = [
				'status' => 'success',
				'code' => 200,
				'message' => 'Imagen subida correctamente',
				'file' => $file
			];
		}
	}

	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
});

// $app->post('/upload-file', function (Request $request, Response $response, array $args) {

// 	if (isset($_FILES['uploads'])) {
// 		$piramideUploader = new PiramideUploader();
// 		$upload = $piramideUploader->upload('uploads', 'uploads', 'uploads', ['image/jpeg', 'image/png', 'image/gif']);
// 		$file = $piramideUploader->getInfoFile();
// 		$fileName = $file['complete_name'];
// 		if (isset($upload) && $upload['uploaded'] == false) {
// 			$result = ['status' => 'error', 'code' => 404, 'message' => 'La imagen no fué subida.', 'file' => $file];
// 		} else {
// 			$result = ['status' => 'success', 'code' => 200, 'message' => 'La imagen ha sido subido exitosamente.', 'file' => $fileName];
// 		}
// 		// var_dump($_FILES);
// 		// die();
// 		$response->getBody()->write(json_encode($result));
// 	}
// 	return $response->withHeader('Content-Type', 'application/json');
// });
// $app->get("/probando", function() 	use($app){
// 	echo "OTRO TEXTO CUALQUIERA";
// });

// // LISTAR TODOS LOS PRODUCTOS
// $app->get('/productos', function() use($db, $app){
// 	$sql = 'SELECT * FROM productos ORDER BY id DESC;';
// 	$query = $db->query($sql);

// 	$productos = array();
// 	while ($producto = $query->fetch_assoc()) {
// 		$productos[] = $producto;
// 	}

// 	$result = array(
// 			'status' => 'success',
// 			'code'	 => 200,
// 			'data' => $productos
// 		);

// 	echo json_encode($result);
// });

// // DEVOLVER UN SOLO PRODUCTO
// $app->get('/producto/:id', function($id) use($db, $app){
// 	$sql = 'SELECT * FROM productos WHERE id = '.$id;
// 	$query = $db->query($sql);

// 	$result = array(
// 		'status' 	=> 'error',
// 		'code'		=> 404,
// 		'message' 	=> 'Producto no disponible'
// 	);

// 	if($query->num_rows == 1){
// 		$producto = $query->fetch_assoc();

// 		$result = array(
// 			'status' 	=> 'success',
// 			'code'		=> 200,
// 			'data' 	=> $producto
// 		);
// 	}

// 	echo json_encode($result);
// });

// // ELIMINAR UN PRODUCTO
// $app->get('/delete-producto/:id', function($id) use($db, $app){
// 	$sql = 'DELETE FROM productos WHERE id = '.$id;
// 	$query = $db->query($sql);

// 	if($query){
// 		$result = array(
// 			'status' 	=> 'success',
// 			'code'		=> 200,
// 			'message' 	=> 'El producto se ha eliminado correctamente!!'
// 		);
// 	}else{
// 		$result = array(
// 			'status' 	=> 'error',
// 			'code'		=> 404,
// 			'message' 	=> 'El producto no se ha eliminado!!'
// 		);
// 	}

// 	echo json_encode($result);
// });

// // ACTUALIZAR UN PRODUCTO
// $app->post('/update-producto/:id', function($id) use($db, $app){
// 	$json = $app->request->post('json');
// 	$data = json_decode($json, true);

// 	$sql = "UPDATE productos SET ".
// 		   "nombre = '{$data["nombre"]}', ".
// 		   "descripcion = '{$data["descripcion"]}', ";

// 	if(isset($data['imagen'])){
//  		$sql .= "imagen = '{$data["imagen"]}', ";
// 	}

// 	$sql .=	"precio = '{$data["precio"]}' WHERE id = {$id}";


// 	$query = $db->query($sql);

// 	if($query){
// 		$result = array(
// 			'status' 	=> 'success',
// 			'code'		=> 200,
// 			'message' 	=> 'El producto se ha actualizado correctamente!!'
// 		);
// 	}else{
// 		$result = array(
// 			'status' 	=> 'error',
// 			'code'		=> 404,
// 			'message' 	=> 'El producto no se ha actualizado!!'
// 		);
// 	}

// 	echo json_encode($result);

// });

// // SUBIR UNA IMAGEN A UN PRODUCTO
// $app->post('/upload-file', function() use($db, $app){
// 	$result = array(
// 		'status' 	=> 'error',
// 		'code'		=> 404,
// 		'message' 	=> 'El archivo no ha podido subirse'
// 	);

// 	if(isset($_FILES['uploads'])){
// 		$piramideUploader = new PiramideUploader();

// 		$upload = $piramideUploader->upload('image', "uploads", "uploads", array('image/jpeg', 'image/png', 'image/gif'));
// 		$file = $piramideUploader->getInfoFile();
// 		$file_name = $file['complete_name'];

// 		if(isset($upload) && $upload["uploaded"] == false){
// 			$result = array(
// 				'status' 	=> 'error',
// 				'code'		=> 404,
// 				'message' 	=> 'El archivo no ha podido subirse'
// 			);
// 		}else{
// 			$result = array(
// 				'status' 	=> 'success',
// 				'code'		=> 200,
// 				'message' 	=> 'El archivo se ha subido',
// 				'filename'  => $file_name
// 			);
// 		}
// 	}

// 	echo json_encode($result);
// });

// // GUARDAR PRODUCTOS
// $app->post('/productos', function() use($app, $db){
// 	$json = $app->request->post('json');
// 	$data = json_decode($json, true);

// 	if(!isset($data['nombre'])){
// 		$data['nombre']=null;
// 	}

// 	if(!isset($data['descripcion'])){
// 		$data['descripcion']=null;
// 	}

// 	if(!isset($data['precio'])){
// 		$data['precio']=null;
// 	}

// 	if(!isset($data['imagen'])){
// 		$data['imagen']=null;
// 	}

// 	$query = "INSERT INTO productos VALUES(NULL,".
// 			 "'{$data['nombre']}',".
// 			 "'{$data['descripcion']}',".
// 			 "'{$data['precio']}',".
// 			 "'{$data['imagen']}'".
// 			 ");";

// 	$insert = $db->query($query);

// 	$result = array(
// 		'status' => 'error',
// 		'code'	 => 404,
// 		'message' => 'Producto NO se ha creado'
// 	);

// 	if($insert){
// 		$result = array(
// 			'status' => 'success',
// 			'code'	 => 200,
// 			'message' => 'Producto creado correctamente'
// 		);
// 	}

// 	echo json_encode($result);
// });

$app->run();