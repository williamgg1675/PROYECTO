<?php
require_once("conexion.php");

class clsCliente{

	function insertarCliente($cliente){

		$sql = "INSERT INTO cliente(id, tipodoc, nrodoc, razon_social, direccion)
				VALUES (NULL, :tipodoc, :nrodoc, :razon_social, :direccion)";

		$parametros = array(
						':tipodoc'		=>$cliente['tipodoc'],
						':nrodoc' 		=>$cliente['ruc'],
						':razon_social'	=>$cliente['razon_social'],
						':direccion'	=>$cliente['direccion']
						);
		global $cnx;
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function consultarCliente($nrodoc){
		$sql = "SELECT * FROM cliente WHERE nrodoc=:nrodoc";

		$parametros = array(':nrodoc'=>$nrodoc);

		global $cnx;
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;	
	}

	function consultarClientePorCodigo($codigo){
		$sql = "SELECT * FROM cliente WHERE id=:codigo";

		$parametros = array(':codigo'=>$codigo);

		global $cnx;
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;	
	}

}

?>