<?php
require_once("conexion.php");

class clsNotaCredito{

	function insertarDetalleNotaCredito($idnc,$detalle){
		$sql = "INSERT INTO nota_credito_detalle(id,idnc, item, idproducto, cantidad, valor_unitario, precio_unitario, igv, porcentaje_igv, valor_total, importe_total)
			VALUES (NULL, :idnc, :item, :idproducto, :cantidad, :valor_unitario, :precio_unitario, :igv, :porcentaje_igv, :valor_total, :importe_total)";
	
			global $cnx;
			$pre = $cnx->prepare($sql);

			foreach($detalle as $k=>$v){
				$parametros = array(
					':idnc'		=>$idnc,
					':item'			=>$v['item'],
					':idproducto'	=>$v['codigo'],
					':cantidad'		=>$v['cantidad'],
					':valor_unitario'=>$v['valor_unitario'],
					':precio_unitario'=>$v['precio_unitario'],
					':igv'			=>$v['igv'],
					':porcentaje_igv'=>$v['porcentaje_igv'],
					':valor_total'	=> $v['valor_total'],
					':importe_total'=> $v['importe_total']
					);
				$pre->execute($parametros);
			}
	}

	function insertarNotaCredito($idemisor, $venta){
		$sql = "INSERT INTO nota_credito(id, idemisor, tipocomp, idserie, serie, correlativo, fecha_emision, codmoneda, op_gravadas, op_exoneradas, op_inafectas, igv, total, codcliente, tipocomp_ref, serie_ref, correlativo_ref, codmotivo)
				VALUES (NULL, :idemisor, :tipocomp, :idserie, :serie, :correlativo, :fecha_emision, :codmoneda, :op_gravadas, :op_exoneradas, :op_inafectas, :igv, :total, :codcliente, :tipocomp_ref, :serie_ref, :correlativo_ref, :codmotivo)";
		$parametros = array(
					':idemisor'=>$idemisor,
					':tipocomp'=>$venta['tipodoc'],
					':idserie' =>$venta['idserie'],
					':serie'   =>$venta['serie'],
					':correlativo' =>$venta['correlativo'],
					':fecha_emision'=>$venta['fecha_emision'],
					':codmoneda'  => $venta['moneda'],
					':op_gravadas'=>$venta['total_opgravadas'],
					':op_exoneradas'=>$venta['total_opexoneradas'],
					':op_inafectas' =>$venta['total_opinafectas'],
					':igv'			=>$venta['igv'],
					':total'		=>$venta['total'],
					':codcliente'	=>$venta['codcliente'],
					':tipocomp_ref'=>$venta['tipodoc_ref'],
					':serie_ref'   =>$venta['serie_ref'],
					':correlativo_ref' =>$venta['correlativo_ref'],
					':codmotivo' =>$venta['codmotivo']
				);

			global $cnx;
			$pre = $cnx->prepare($sql);
			$pre->execute($parametros);
			return $pre;
	}

	function actualizarDatosFE($idventa, $estado, $codigoerror, $mensajesunat){
		$sql = "UPDATE venta SET feestado=:feestado, fecodigoerror=:fecodigoerror, femensajesunat=:femensajesunat WHERE id=:idventa";
		global $cnx;
		$parametros = array(
						':feestado'=>$feestado, 
						':fecodigoerror'=>$fecodigoerror, 
						':femensajesunat'=>$femensajesunat, 
						':idventa'=>$idventa
					);
		$pre = $cnx->prepare($sql);
		$pre->execute($parametros);
		return $pre;
	}

	function listarNotaCredito(){
		$sql = "SELECT * FROM nota_credito";
		global $cnx;
		return $cnx->query($sql);		
	}

	function obtenerUltimoComprobanteId(){
		$sql = "SELECT * FROM nota_credito ORDER BY id DESC LIMIT 1";
		global $cnx;
		return $cnx->query($sql);		
	}

	function obtenerComprobanteId($id){
		$sql = "SELECT * FROM nota_credito WHERE id=?";
		global $cnx;
		$pre = $cnx->prepare($sql);
		$pre->execute(array($id));
		return $pre;		
		
	}


}

?>