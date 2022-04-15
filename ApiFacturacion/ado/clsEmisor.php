<?php
require_once("conexion.php");

class clsEmisor{

	function consultarListaEmisores(){
		$sql = "SELECT * FROM emisor ";
		global $cnx;
		return $cnx->query($sql);
		
	}

	function obtenerEmisor($idemisor){
		$sql = "SELECT * FROM emisor WHERE id=:idemisor ";
		global $cnx;
		$parametros = array(':idemisor'=>$idemisor);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

}


?>