<?php

try {
    
    $manejador = 'mysql';
    $servidor = 'localhost';
    $puerto = '3306';
    $base = 'facturacion17';
    $usuario = 'root';
    $pass = '1675QW';
    $cadena = "$manejador:host=$servidor;dbname=$base";
    $cnx = new PDO($cadena, $usuario, $pass, array(PDO::ATTR_PERSISTENT => 'true', PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8' "));

    //CRUD: INSERT, READ, UPDATE, DELETE : REGISTRAR, SELECCIONAR, ACTUALIZAR, ELIMINAR: REGISTROS - MANTEMIENTO

    // //ejemplo de insert - tabla UNIDAD
    // $sql = 'INSERT INTO unidad(codigo, descripcion) VALUES(:codigo, :descripcion)';
    // $parametros = array(
    //     ':codigo'       =>  'KG',
    //     ':descripcion'  =>  'KILOS'
    // );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);
    // echo 'Unidad registrada correctamente.';

    //ejemplo de update - tabla UNIDAD
    // $sql = 'UPDATE unidad 
    //             SET descripcion = :descripcion
    //         WHERE codigo = :codigo';

    // $parametros = array(
    //     ':codigo'       =>  'KG',
    //     ':descripcion'  =>  'KILOGRAMOS'
    // );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);
    // echo 'Unidad actualizada correctamente.';

    //Ejemplo de SELECT - Tabla UNIDAD
    // $sql = 'SELECT * FROM unidad';
    // $res = $cnx->query($sql);
    // while($fila = $res->fetch(PDO::FETCH_NAMED))
    // {
    //     echo $fila['codigo'] . ' - ' . $fila['descripcion'] . '</br>';
    // }

    //Ejemplo de DELETE - Tabla UNIDAD
    // $sql = 'DELETE FROM unidad WHERE codigo = :codigo';
    // $parametros = array( ':codigo' => 'KG' );
    // $pre = $cnx->prepare($sql);
    // $pre->execute($parametros);

    // echo 'Unidad eliminada correctamente';

} catch (Exception $ex) {
    //throw $th;
}

?>