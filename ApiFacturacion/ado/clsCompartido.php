<?php
require_once("conexion.php");

class clsCompartido{

	function listarMonedas(){
		$sql = "SELECT * FROM moneda";
		global $cnx;
		return $cnx->query($sql);
		
	}

	function listarComprobantes(){
		$sql = "SELECT * FROM tipo_comprobante";
		global $cnx;
		return $cnx->query($sql);
		
	}

	function listarTipoDocumento(){
		$sql = "SELECT * FROM tipo_documento";
		global $cnx;
		return $cnx->query($sql);
		
	}
		
	function listarProducto($filtro){
		$sql = "SELECT * FROM producto WHERE nombre LIKE :filtro";
		global $cnx;
		$parametros = array(':filtro'=>'%'.$filtro.'%');
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;	
	}

	function obtenerProducto($codigo){
		$sql = "SELECT * FROM producto WHERE codigo=:codigo";
		global $cnx;
		$parametros = array(':codigo'=>$codigo);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;	
	}

	function listarSerie($tipocomp){
		$sql = "SELECT * FROM serie WHERE tipocomp=:tipocomp";
		global $cnx;
		$parametros = array(':tipocomp'=>$tipocomp);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function obtenerSerie($idserie){
		$sql = "SELECT * FROM serie WHERE id=:idserie";
		global $cnx;
		$parametros = array(':idserie'=>$idserie);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function obtenerSerieResumen($tipocomp){
		$sql = "SELECT * FROM serie WHERE tipocomp=:tipocomp";
		global $cnx;
		$parametros = array(':tipocomp'=>$tipocomp);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function insertarSerie($serie,$tipocomp){
		$sql = "INSERT INTO serie(id,tipocomp,serie,correlativo) VALUES(NULL,:tipocomp,:serie,0)";
		global $cnx;
		$parametros = array(':serie'=>$serie, ':tipocomp'=>$tipocomp);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function actualizarSerieResumen($tipocomp, $serie){
		$sql = "UPDATE serie SET serie=:serie, correlativo=0 WHERE tipocomp=:tipocomp";
		global $cnx;
		$parametros = array(':serie'=>$serie, ':tipocomp'=>$tipocomp);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function actualizarSerie($idserie, $correlativo){
		$sql = "UPDATE serie SET correlativo=:correlativo WHERE id=:idserie";
		global $cnx;
		$parametros = array(':idserie'=>$idserie, ':correlativo'=>$correlativo);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function obtenerRegistroAfectacion($codigoafectacion){
		$sql = "SELECT * FROM tipo_afectacion WHERE codigo=:codigoafectacion";
		global $cnx;
		$parametros = array(':codigoafectacion'=>$codigoafectacion);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;

	}

	

	function obtenerComprobante($codigo){
		$sql = "SELECT * FROM tipo_comprobante WHERE codigo=?";
		global $cnx;
		$parametros = array($codigo);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}


	function listarUnidad(){
		$sql = "SELECT * FROM unidad";
		global $cnx;
		return $cnx->query($sql);		
	}

	function listarTablaParametrica($tipo){
		$sql = "SELECT * FROM tabla_parametrica WHERE tipo=:tipo";
		global $cnx;
		$parametros = array(':tipo'=>$tipo);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function getRegistroTablaParametrica($tipo,$codigo){
		$sql = "SELECT * FROM tabla_parametrica WHERE tipo=:tipo AND codigo=:codigo";
		global $cnx;
		$parametros = array(':tipo'=>$tipo, ':codigo'=>$codigo);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

}

?>