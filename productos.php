<?php
class Productos{
    public $id;
    public $producto;
    public $edad;
    public $marca;

    public function __construct($id = null, $producto, $edad, $marca){

        $this->id = $id;
        $this->producto = $producto;
        $this->edad= $edad;
        $this->marca = $marca;

    }

    public static function fromArray($data){
        
        return new self(
            $data['id'] ?? null,
            $data['producto'] ?? null,
            $data['edad'] ?? null,
            $data['marca'] ?? null
        );
    }

    public function toArray(){
        return get_object_vars($this);
    }
}

?>