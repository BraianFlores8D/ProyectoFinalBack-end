<?php  

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");



include 'db.php';
include 'productos.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;

    case 'POST':
        handlePost($conn);
        break;

    case 'PUT':
        handlePut($conn);
        break;

    case 'DELETE':
        handleDelete($conn);
        break;
    
    default:
        echo json_encode(['message'=>'Metodo No Permitido']);
        break;
}

//este metodo tiene que devolver 1 o todas las peliculas
function handleGet($conn){

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if($id>0){
        //entonces devuelvo una pelicula segun el id proporcionado.
        $stmt = $conn->prepare("SELECT * FROM productos WHERE ID = ?");
        $stmt -> execute([$id]);
        $producto = $stmt -> fetch(PDO::FETCH_ASSOC);

        if($producto){

            $productoObj = Productos::fromArray($producto);
            echo json_encode($productoObj -> toArray());
        }
        else{
            http_response_code(404);
            echo json_encode(['message'=>'No se encontro producto']);
        }

    }
    else{
        //devuelvo todas las peliculas.
        $stmt = $conn->query("SELECT * FROM productos");
        $productos = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        $productoObjs = array_map(fn($producto)=>Productos::fromArray($producto)->toArray(),$productos);
        echo json_encode(['productos'=>$productoObjs]);
    }
}

function handlePost($conn){  //Este metodo sirve para ingresar productos

    $data = json_decode(file_get_contents('php://input'), true);
    $requiredFields = ['producto','edad','marca'];

    foreach($requiredFields as $field){

        if(!isset($data[$field])){

            echo json_encode(['message'=>'Datos incompletos']);
            return;
        }

    }

    $producto=Productos::fromArray($data);
    try {
        $stmt = $conn->prepare("INSERT INTO productos (producto,edad,marca) VALUES (?,?,?)");

        $stmt->execute([
            $producto->producto,
            $producto->edad,
            $producto->marca
        ]);

        echo json_encode(['message'=>'Ingresada Correctamente']);

    } catch (PDOException $e) {
        
        echo json_encode(['message'=>'Error al ingresar producto', 'error'=>$e->getMessage()]);
    }

}

function handlePut($conn){  //Sirve para actualizar productos

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {

        $data = json_decode(file_get_contents('php://input'), true);
        $producto = Productos::fromArray($data);
        $producto-> id = $id;

        $fields = [];
        $params = [];

        if($producto->producto !== null){

            $fields[]= 'producto = ?';
            $params[]= $producto->producto;
        }

        if($producto->edad !== null){

            $fields[]= 'edad = ?';
            $params[]= $producto->edad;
        }

        if($producto->marca !== null){

            $fields[]= 'marca = ?';
            $params[]= $producto->marca;
        }

        if(!empty($fields)){
            $params[]=$id;
            $stmt=$conn-> prepare("UPDATE productos SET".implode(',', $fields) . "WHERE id = ?");
            $stmt->execute($params);
            echo json_encode(['message'=>'El producto se actualizo con Exito']);

        }else{
            echo json_encode(['message'=>'No hay campos para actualizar']);
        }



    } else {
        echo json_encode(['message'=>'ID no encontrado']);
    }
}


function handleDelete($conn){ //Sirve para borrar registros (productos)

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt -> execute([$id]);
        echo json_encode(['message'=>'Producto Eliminado con exito']);

    } else {
        echo json_encode(['message'=>'ID no encontrado']);
    }
}    


?>